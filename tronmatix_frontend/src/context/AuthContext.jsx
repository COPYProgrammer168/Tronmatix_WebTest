// src/context/AuthContext.jsx
import { createContext, useContext, useState, useEffect, useCallback, useRef } from 'react'
import api, { clearAuthStorage } from '../lib/axios'

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
    const raw = localStorage.getItem(USER_KEY)
    return raw ? JSON.parse(raw) : null
  } catch { return null }
}

function saveUser(user) {
  const safe = sanitizeUser(user)
  if (safe) localStorage.setItem(USER_KEY, JSON.stringify(safe))
  else      localStorage.removeItem(USER_KEY)
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

  const applyToken = useCallback((t) => {
    tokenRef.current = t
    if (t) {
      localStorage.setItem(TOKEN_KEY, t)
      api.defaults.headers.common['Authorization'] = `Bearer ${t}`
    } else {
      localStorage.removeItem(TOKEN_KEY)
      delete api.defaults.headers.common['Authorization']
    }
    setToken(t)
  }, [])

  const applyUser = useCallback((u) => {
    const merged = u ? sanitizeUser({ ...loadCachedUser(), ...u }) : null
    setUser(merged)
    saveUser(merged)
  }, [])

  const clearSession = useCallback(() => {
    applyToken(null)
    applyUser(null)
    clearAuthStorage()
  }, [applyToken, applyUser])

  // ── Restore session on mount ──────────────────────────────────────────────
  useEffect(() => {
    const storedToken = localStorage.getItem(TOKEN_KEY)

    if (!storedToken) { setReady(true); return }

    if (isTokenExpired(storedToken)) { clearSession(); setReady(true); return }

    api.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`
    tokenRef.current = storedToken

    api.get('/api/auth/me')
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

  const refreshUser = useCallback(async () => {
    try {
      const res   = await api.get('/api/auth/me')
      const fresh = extractUser(res.data)
      if (fresh) applyUser(fresh)
      return fresh
    } catch { return null }
  }, [applyUser])

  // ── LOGIN ─────────────────────────────────────────────────────────────────
  const login = useCallback(async (usernameOrEmail, password) => {
    setLoading(true)
    try {
      const res = await api.post('/api/auth/login', { username: usernameOrEmail, password })
      const t = res.data?.token ?? res.data?.data?.token
      const u = extractUser(res.data)
      if (!t || !u) throw new Error('Unexpected login response shape')
      applyToken(t)
      applyUser(u)
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
  const googleLogin = useCallback(async (accessToken) => {
    setLoading(true)
    try {
      const res = await api.post('/api/auth/google', { access_token: accessToken })
      const t = res.data?.token
      const u = res.data?.user
      if (!t || !u) throw new Error('Unexpected Google response shape')
      applyToken(t)
      applyUser(u)
      return { success: true }
    } catch (e) {
      const msg = e.response?.data?.message || 'Google sign-in failed. Please try again.'
      return { success: false, message: msg }
    } finally {
      setLoading(false)
    }
  }, [applyToken, applyUser])

  // ── TELEGRAM LOGIN (from AuthModal — user not yet logged in) ─────────────
  const telegramLogin = useCallback(async () => {
    setLoading(true)
    try {
      // Step 1 — get a connection token (works without auth for login flow)
      const tokenRes = await api.post('/api/auth/telegram-generate-token')
      if (!tokenRes.data?.success) throw new Error('Failed to generate link')

      const connectToken = tokenRes.data.token
      const botUsername  = import.meta.env.VITE_TELEGRAM_BOT_USERNAME || ''
      const tgUrl        = `https://t.me/${botUsername}?start=${connectToken}`

      // Step 2 — open Telegram
      const tgWindow = window.open(tgUrl, '_blank')
      if (!tgWindow) {
        // Popup was blocked — fallback: show a clickable link instead of redirecting
        setLoading(false)
        return { success: false, message: 'Popup blocked. Please allow popups and try again, or open Telegram manually.' }
      }

      setWaiting(true)
      setPolling(true)

      // Step 3 — poll /api/auth/telegram-status until token is claimed
      return new Promise((resolve) => {
        const startTime = Date.now()
        const poll = setInterval(async () => {
          if (Date.now() - startTime > 180_000) {
            clearInterval(poll)
            setLoading(false)
            resolve({ success: false, message: 'Connection timed out. Please try again.' })
            return
          }
          try {
            const res = await api.get('/api/auth/telegram-status', {
              params: { token: connectToken }
            })
            if (res.data?.success) {
              clearInterval(poll)
              applyToken(res.data.token)
              applyUser(res.data.user)
              setLoading(false)
              resolve({ success: true, isNewUser: res.data.is_new_user })
            }
          } catch { /* keep polling */ }
        }, 3000)
      })
    } catch (e) {
      setLoading(false)
      return { success: false, message: e.response?.data?.message || 'Telegram login failed.' }
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
      return { success: false, message: msg }
    } finally {
      setLoading(false)
    }
  }, [])

  // ── LOGOUT ────────────────────────────────────────────────────────────────
  const logout = useCallback(async () => {
    try { await api.post('/api/auth/logout') } catch { /* ignore */ }
    clearSession()
  }, [clearSession])

  return (
    <AuthContext.Provider value={{
      user, token, loading, ready,
      login, register, logout, refreshUser,
      forgotPassword, googleLogin, telegramLogin,
    }}>
      {children}
    </AuthContext.Provider>
  )
}

export const useAuth = () => useContext(AuthContext)