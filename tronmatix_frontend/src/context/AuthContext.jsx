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
  // eslint-disable-next-line no-unused-vars
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

  // ── Post-OAuth profile completion state ──────────────────────────────────
  // When is_new_user=true, we show a "complete your profile" modal.
  const [needsProfileSetup, setNeedsProfileSetup] = useState(false)

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
    setNeedsProfileSetup(false)
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
        // Network error → keep cached user
      })
      .finally(() => setReady(true))
  }, []) // eslint-disable-line react-hooks/exhaustive-deps

  // ── Listen for social login events dispatched by AuthModal ───────────────
  // AuthModal dispatches 'auth:social-login' after Google/Telegram callback.
  useEffect(() => {
    const handler = (e) => {
      const { token: t, user: u, isNewUser } = e.detail ?? {}
      if (!t || !u) return
      applyToken(t)
      applyUser(u)
      // Show profile completion modal for brand-new Google/Telegram users
      if (isNewUser) setNeedsProfileSetup(true)
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
  // Called by AuthModal after GSI popup returns an access_token.
  const googleLogin = useCallback(async (accessToken) => {
    setLoading(true)
    try {
      const res = await api.post('/api/auth/google', { access_token: accessToken })
      const t = res.data?.token
      const u = res.data?.user
      if (!t || !u) throw new Error('Unexpected Google response shape')
      applyToken(t)
      applyUser(u)
      // Show profile setup if this is a brand new Google account
      if (res.data?.is_new_user) setNeedsProfileSetup(true)
      return { success: true, isNewUser: !!res.data?.is_new_user }
    } catch (e) {
      const msg = e.response?.data?.message || 'Google sign-in failed. Please try again.'
      return { success: false, message: msg }
    } finally {
      setLoading(false)
    }
  }, [applyToken, applyUser])

  // ── COMPLETE PROFILE (post-OAuth) ─────────────────────────────────────────
  // Saves username + phone for new Google/Telegram users.
  const completeProfile = useCallback(async (username, phone) => {
    setLoading(true)
    try {
      const res = await api.post('/api/user/profile/complete', { username, phone })
      const u = res.data?.data
      if (u) applyUser(u)
      setNeedsProfileSetup(false)
      return { success: true }
    } catch (e) {
      const data = e.response?.data
      let msg = 'Failed to save profile.'
      if (data?.errors)  msg = Object.values(data.errors).flat()[0] || msg
      else if (data?.message) msg = data.message
      return { success: false, message: msg }
    } finally {
      setLoading(false)
    }
  }, [applyUser])

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
      needsProfileSetup, setNeedsProfileSetup,
      login, register, logout, refreshUser,
      forgotPassword, googleLogin, completeProfile,
    }}>
      {children}
    </AuthContext.Provider>
  )
}

export const useAuth = () => useContext(AuthContext)
