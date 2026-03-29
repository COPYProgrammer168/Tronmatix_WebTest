// src/context/AuthContext.jsx
import { createContext, useContext, useState, useEffect, useCallback } from 'react'
import axios, { clearAuthStorage } from '../lib/axios'

const AuthContext = createContext(null)

axios.defaults.withCredentials = false
axios.defaults.headers.common['Accept']       = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

const USER_KEY = 'tronmatix_user'

// Check token expiry from JWT payload — 30s clock-skew buffer
function isTokenExpired(token) {
  try {
    const base64Url = token.split('.')[1]
    if (!base64Url) return true
    const json = JSON.parse(atob(base64Url.replace(/-/g, '+').replace(/_/g, '/')))
    return json.exp ? json.exp * 1000 < Date.now() - 30_000 : false
  } catch {
    return true
  }
}

// Strip server-only fields before persisting to localStorage
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
  else       localStorage.removeItem(USER_KEY)
}

// Normalize API response shapes: { data: user }, { user: user }, or just user
function extractUser(responseData) {
  if (!responseData) return null
  if (responseData.data && responseData.data.id) return responseData.data
  if (responseData.user && responseData.user.id) return responseData.user
  if (responseData.id) return responseData
  return null
}

export function AuthProvider({ children }) {
  const [user,    setUser]    = useState(() => loadCachedUser())
  const [token,   setToken]   = useState(() => localStorage.getItem('token'))
  const [loading, setLoading] = useState(false)
  const [ready,   setReady]   = useState(false)

  const applyToken = useCallback((t) => {
    if (t) {
      localStorage.setItem('token', t)
      axios.defaults.headers.common['Authorization'] = `Bearer ${t}`
    } else {
      localStorage.removeItem('token')
      delete axios.defaults.headers.common['Authorization']
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

  // Restore session on mount
  useEffect(() => {
    if (!token) { setReady(true); return }

    if (isTokenExpired(token)) {
      clearSession()
      setReady(true)
      return
    }

    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

    axios.get('/api/auth/me')
      .then(res => {
        const fresh = extractUser(res.data)
        if (fresh) applyUser(fresh)
      })
      .catch((err) => {
        if (err.response?.status === 401) clearSession()
        // Network errors: keep cached user — don't log out on bad connection
      })
      .finally(() => setReady(true))
  }, []) // eslint-disable-line react-hooks/exhaustive-deps

  const refreshUser = useCallback(async () => {
    try {
      const res = await axios.get('/api/auth/me')
      const fresh = extractUser(res.data)
      if (fresh) applyUser(fresh)
      return fresh
    } catch { return null }
  }, [applyUser])

  // ── LOGIN ───────────────────────────────────────────────────────────────────
  // Accepts username OR email. The backend does case-insensitive lookup on both.
  const login = useCallback(async (usernameOrEmail, password) => {
    setLoading(true)
    try {
      const isEmail = usernameOrEmail.includes('@')
      const payload = isEmail
        ? { username: usernameOrEmail, password }  // backend accepts email in the username field
        : { username: usernameOrEmail, password }

      const res = await axios.post('/api/auth/login', payload)

      const t = res.data?.token ?? res.data?.data?.token
      const u = extractUser(res.data)

      if (!t || !u) throw new Error('Unexpected login response')

      applyToken(t)
      applyUser(u)
      return { success: true }
    } catch (e) {
      const data = e.response?.data
      let msg = 'Login failed. Check your credentials and try again.'
      if (data?.errors) {
        msg = Object.values(data.errors).flat()[0] || msg
      } else if (data?.message) {
        msg = data.message
      }
      return { success: false, message: msg }
    } finally {
      setLoading(false)
    }
  }, [applyToken, applyUser])

  // ── REGISTER ─────────────────────────────────────────────────────────────────
  // Returns the registered email so the caller can prefill the login form.
  // No token is returned by the backend — user must log in explicitly.
  const register = useCallback(async (username, email, password, confirm) => {
    setLoading(true)
    try {
      await axios.post('/api/auth/register', {
        username,
        email,
        password,
        password_confirmation: confirm,
      })
      // Return the email so AuthModal can prefill the login form.
      // The user's Gmail address becomes the login credential automatically.
      return { success: true, email }
    } catch (e) {
      const data = e.response?.data
      let msg = 'Registration failed.'
      if (data?.errors) {
        msg = Object.values(data.errors).flat().join(' ')
      } else if (data?.message) {
        msg = data.message
      }
      return { success: false, message: msg }
    } finally {
      setLoading(false)
    }
  }, [])

  // ── FORGOT PASSWORD ───────────────────────────────────────────────────────────
  const forgotPassword = useCallback(async (email) => {
    setLoading(true)
    try {
      const res = await axios.post('/api/auth/forgot-password', { email })
      // Backend always returns the same message to prevent email enumeration
      const msg = res.data?.message || 'If that email is registered, a reset link has been sent.'
      return { success: true, message: msg }
    } catch (e) {
      const data = e.response?.data
      let msg = 'Failed to send reset email. Please try again.'
      if (data?.errors?.email) {
        msg = data.errors.email[0]
      } else if (data?.message) {
        msg = data.message
      }
      return { success: false, message: msg }
    } finally {
      setLoading(false)
    }
  }, [])

  // ── LOGOUT ───────────────────────────────────────────────────────────────────
  const logout = useCallback(async () => {
    try { await axios.post('/api/auth/logout') } catch { /* ignore */ }
    clearSession()
  }, [clearSession])

  return (
    <AuthContext.Provider value={{
      user, token, loading, ready,
      login, register, logout, refreshUser, forgotPassword,
    }}>
      {children}
    </AuthContext.Provider>
  )
}

export const useAuth = () => useContext(AuthContext)
