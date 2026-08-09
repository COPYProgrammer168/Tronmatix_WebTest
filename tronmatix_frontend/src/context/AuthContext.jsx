// src/context/AuthContext.jsx
import { createContext, useContext, useState, useEffect, useCallback, useRef } from 'react'
import api, { clearAuthStorage } from '../lib/axios'
import { getInitData, ready, expand } from '../lib/telegramMiniApp'

const AuthContext = createContext(null)

const USER_KEY  = 'tronmatix_user'
const TOKEN_KEY = 'token'

function isTokenExpired(token) {
  try {
    const parts = token.split('.')
    if (parts.length !== 3) return false
    const json = JSON.parse(atob(parts[1].replace(/-/g, '+').replace(/_/g, '/')))
    return json.exp ? json.exp * 1000 < Date.now() - 30_000 : false
  } catch {
    return false
  }
}

function sanitizeUser(user) {
  if (!user) return null
  const { password, password_confirmation, remember_token, ...safe } = user
  return safe
}

function loadCachedUser() {
  try {
    const raw = localStorage.getItem(USER_KEY) || sessionStorage.getItem(USER_KEY)
    return raw ? JSON.parse(raw) : null
  } catch { return null }
}

function saveUser(user) {
  const safe = sanitizeUser(user)
  if (safe) localStorage.setItem(USER_KEY, JSON.stringify(safe))
  else      localStorage.removeItem(USER_KEY)
}

function saveUserSession(user) {
  const safe = sanitizeUser(user)
  if (safe) sessionStorage.setItem(USER_KEY, JSON.stringify(safe))
  else      sessionStorage.removeItem(USER_KEY)
}

function extractUser(responseData) {
  if (!responseData) return null
  if (responseData.data?.id) return responseData.data
  if (responseData.user?.id) return responseData.user
  if (responseData.id)       return responseData
  return null
}

export function AuthProvider({ children }) {
  const [user,    setUser]    = useState(() => loadCachedUser())
  const [token,   setToken]   = useState(() => localStorage.getItem(TOKEN_KEY))
  const [loading, setLoading] = useState(false)
  const [ready,   setReady]   = useState(false)

  const tokenRef = useRef(token)

  const applyToken = useCallback((t, remember = true) => {
    tokenRef.current = t
    if (t) {
      if (remember) localStorage.setItem(TOKEN_KEY, t)
      else         sessionStorage.setItem(TOKEN_KEY, t)
      api.defaults.headers.common['Authorization'] = `Bearer ${t}`
    } else {
      localStorage.removeItem(TOKEN_KEY)
      sessionStorage.removeItem(TOKEN_KEY)
      delete api.defaults.headers.common['Authorization']
    }
    setToken(t)
  }, [])

  const applyUser = useCallback((u, remember = true) => {
    const merged = u ? sanitizeUser({ ...loadCachedUser(), ...u }) : null
    setUser(merged)
    if (remember) saveUser(merged)
    else         saveUserSession(merged)
  }, [])

  const clearSession = useCallback(() => {
    applyToken(null)
    applyUser(null)
    clearAuthStorage()
  }, [applyToken, applyUser])

  // ── Restore session on mount ──────────────────────────────────────────────
  useEffect(() => {
    const storedToken = localStorage.getItem(TOKEN_KEY) || sessionStorage.getItem(TOKEN_KEY)

    if (!storedToken) { setReady(true); return }

    if (isTokenExpired(storedToken)) { clearSession(); setReady(true); return }

    api.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`
    tokenRef.current = storedToken

    api.get('/api/portal/me')
      .then(res => {
        const fresh = extractUser(res.data)
        if (fresh) applyUser(fresh)
      })
      .catch((err) => {
        if (err.response?.status === 401) clearSession()
      })
      .finally(() => setReady(true))
  }, [])

  // ── Listen for social login events dispatched by AuthModal ───────────────
  useEffect(() => {
    const handler = (e) => {
      const { token: t, user: u } = e.detail ?? {}
      if (!t || !u) return
      applyToken(t)
      applyUser(u)
    }
    window.addEventListener('auth:social-login', handler)
    return () => window.removeEventListener('auth:social-login', handler)
  }, [applyToken, applyUser])

  // ── Telegram Mini App auto-login ─────────────────────────────────────────
  // When the storefront runs inside a Telegram Mini App, Telegram injects a
  // signed `initData`. We verify it server-side and silently restore the users
  // session — so opening the mini app logs the already-connected account in
  // automatically (no manual login, no token deep-link). Runs once on mount.
  const miniAppDoneRef = useRef(false)

  useEffect(() => {
    if (miniAppDoneRef.current) return
    miniAppDoneRef.current = true

    const auto = async () => {
      const initData = await getInitData().catch(() => null)
      if (!initData) return // not inside a mini app (or SDK failed) — normal site

      ready()
      expand()

      try {
        const res = await api.post('/api/auth/telegram/mini-app', { initData })
        const t = res.data?.token
        const u = res.data?.user
        if (!t || !u) return

        applyToken(t)
        applyUser(u)
        // Let the rest of the app (navbar, profile) react to the restored user.
        window.dispatchEvent(new CustomEvent('auth:social-login', {
          detail: { token: t, user: u },
        }))
      } catch {
        // Invalid/expired initData or server error — leave the user anonymous;
        // they can still sign in normally.
      }
    }

    auto()
  }, [applyToken, applyUser])

  const refreshUser = useCallback(async () => {
    try {
      const res   = await api.get('/api/portal/me')
      const fresh = extractUser(res.data)
      if (fresh) applyUser(fresh)
      return fresh
    } catch { return null }
  }, [applyUser])

  // ── LOGIN ─────────────────────────────────────────────────────────────────
  const login = useCallback(async (usernameOrEmail, password, remember = true) => {
    setLoading(true)
    try {
      const res = await api.post('/api/auth/login', { username: usernameOrEmail, password })
      const t = res.data?.token ?? res.data?.data?.token
      const u = extractUser(res.data)
      if (!t || !u) throw new Error('Unexpected login response shape')
      applyToken(t, remember)
      applyUser(u, remember)
      return { success: true }
    } catch (e) {
      const data = e.response?.data
      let msg = 'Login failed. Check your credentials and try again.'
      if (data?.errors)  msg = Object.values(data.errors).flat()[0] || msg
      else if (data?.message) msg = data.message
      return { success: false, message: msg }
    } finally {
      setLoading(false)
    }
  }, [applyToken, applyUser])

  const staffLogin = useCallback(async (email, password, remember = true) => {
    setLoading(true)
    try {
      const res = await api.post('/api/staff/login', { email, password })
      const t = res.data?.token
      const u = res.data?.user
      if (!t || !u) throw new Error('Unexpected staff login response')
      applyToken(t, remember)
      applyUser(u, remember)
      return { success: true }
    } catch (e) {
      const data = e.response?.data
      let msg = 'Staff login failed.'
      if (data?.message) msg = data.message
      return { success: false, message: msg }
    } finally {
      setLoading(false)
    }
  }, [applyToken, applyUser])

  const devLogin = useCallback(async (email, password, dev_key, remember = true) => {
    setLoading(true)
    try {
      const res = await api.post('/api/dev/login', { email, password, dev_key })
      const t = res.data?.token
      const u = res.data?.user
      if (!t || !u) throw new Error('Unexpected dev login response')
      applyToken(t, remember)
      applyUser(u, remember)
      return { success: true }
    } catch (e) {
      const data = e.response?.data
      let msg = 'Dev login failed.'
      if (data?.message) msg = data.message
      return { success: false, message: msg }
    } finally {
      setLoading(false)
    }
  }, [applyToken, applyUser])

  // ── REGISTER ──────────────────────────────────────────────────────────────
  const register = useCallback(async (username, email, password, confirm) => {
    setLoading(true)
    try {
      await api.post('/api/auth/register', {
        username,
        email,
        password,
        password_confirmation: confirm,
      })
      return { success: true, email }
    } catch (e) {
      const data = e.response?.data
      let msg = 'Registration failed.'
      if (data?.errors)  msg = Object.values(data.errors).flat().join(' ')
      else if (data?.message) msg = data.message
      return { success: false, message: msg }
    } finally {
      setLoading(false)
    }
  }, [])

  // ── GOOGLE LOGIN ──────────────────────────────────────────────────────────
  const googleLogin = useCallback(async (accessToken, remember = true) => {
    setLoading(true)
    try {
      const res = await api.post('/api/auth/google', { access_token: accessToken })
      const t = res.data?.token
      const u = res.data?.user
      if (!t || !u) throw new Error('Unexpected Google response shape')
      applyToken(t, remember)
      applyUser(u, remember)
      return { success: true }
    } catch (e) {
      const msg = e.response?.data?.message || 'Google sign-in failed. Please try again.'
      return { success: false, message: msg }
    } finally {
      setLoading(false)
    }
  }, [applyToken, applyUser])

  // ── FORGOT PASSWORD ───────────────────────────────────────────────────────
  const forgotPassword = useCallback(async (email) => {
    setLoading(true)
    try {
      const res = await api.post('/api/auth/forgot-password', { email })
      const msg = res.data?.message || 'If that email is registered, a reset link has been sent.'
      return { success: true, message: msg }
    } catch (e) {
      const data = e.response?.data
      let msg = 'Failed to send reset email. Please try again.'
      if (data?.errors?.email) msg = data.errors.email[0]
      else if (data?.message)  msg = data.message
      return {
        success: false,
        message: msg,
        cooldownSeconds: data?.cooldown_seconds,
        banSeconds: data?.ban_seconds,
      }
    } finally {
      setLoading(false)
    }
  }, [])

  // ── RESET PASSWORD (Laravel native, token from email link) ───────────────
  const resetPassword = useCallback(async (token, email, password) => {
    setLoading(true)
    try {
      const res = await api.post('/api/auth/reset-password', {
        token,
        email,
        password,
        password_confirmation: password,
      })
      return { success: true, message: res.data?.message || 'Password reset successfully.' }
    } catch (e) {
      const data = e.response?.data
      let msg = 'Failed to reset password. Please try again.'
      if (data?.message) msg = data.message
      return { success: false, message: msg }
    } finally {
      setLoading(false)
    }
  }, [])

  // ── RESET PASSWORD VIA PHONE OTP (Firebase) ──────────────────────────────
  const resetByPhone = useCallback(async (idToken, password) => {
    setLoading(true)
    try {
      const res = await api.post('/api/auth/reset-by-phone', {
        id_token: idToken,
        password,
        password_confirmation: password,
      })
      return { success: true, message: res.data?.message || 'Password reset successfully. You can now log in.' }
    } catch (e) {
      return {
        success: false,
        message: e.response?.data?.message || 'Failed to reset password. Please try again.',
      }
    } finally {
      setLoading(false)
    }
  }, [])

    // ── HEARTBEAT ──────────────────────────────────────────────
  // Keep the session alive and mark user as online while dashboard is open
  const heartbeatIntervalRef = useRef(null)

  const startHeartbeat = useCallback(() => {
    if (heartbeatIntervalRef.current) return // already running
    heartbeatIntervalRef.current = setInterval(async () => {
      try {
        await api.post('/api/staff/heartbeat')
      } catch { /* ignore heartbeat failures */ }
    }, 30_000)
  }, [])

  const stopHeartbeat = useCallback(() => {
    if (heartbeatIntervalRef.current) {
      clearInterval(heartbeatIntervalRef.current)
      heartbeatIntervalRef.current = null
    }
  }, [])

  // ── LOGOUT ──────────────────────────────────────────────
  const logout = useCallback(async () => {
    try { await api.post('/api/auth/logout') } catch { /* ignore */ }
    stopHeartbeat()
    clearSession()
  }, [clearSession, stopHeartbeat])
  return (
    <AuthContext.Provider value={{
      user, token, loading, ready,
      login, staffLogin, devLogin, register, logout, refreshUser,
      startHeartbeat, stopHeartbeat,
      forgotPassword, resetPassword, resetByPhone, googleLogin,
    }}>
      {children}
    </AuthContext.Provider>
  )
}

export const useAuth = () => useContext(AuthContext)