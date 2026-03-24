// src/context/AuthContext.jsx
import { createContext, useContext, useState, useEffect, useCallback } from 'react'
import axios from '../lib/axios'

const AuthContext = createContext(null)

// ── Axios defaults ─────────────────────────────────────────────────────────────
axios.defaults.withCredentials = false
axios.defaults.headers.common['Accept']       = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

// ── localStorage helpers ───────────────────────────────────────────────────────
const USER_KEY = 'tronmatix_user'

function loadCachedUser() {
  try {
    const raw = localStorage.getItem(USER_KEY)
    return raw ? JSON.parse(raw) : null
  } catch { return null }
}

function saveUser(user) {
  if (user) localStorage.setItem(USER_KEY, JSON.stringify(user))
  else       localStorage.removeItem(USER_KEY)
}

// ── Normalize whatever the API returns into a plain user object ───────────────
// Handles: { data: user }, { user: user }, or just the user directly
function extractUser(responseData) {
  if (!responseData) return null
  // { data: { id, username, ... } }
  if (responseData.data && responseData.data.id) return responseData.data
  // { user: { id, username, ... } }
  if (responseData.user && responseData.user.id) return responseData.user
  // { id, username, ... } — already the user
  if (responseData.id) return responseData
  return null
}

// ── Provider ───────────────────────────────────────────────────────────────────
export function AuthProvider({ children }) {
  // Seed from cache immediately → Navbar shows username on first paint, no flash
  const [user,    setUser]    = useState(() => loadCachedUser())
  const [token,   setToken]   = useState(() => localStorage.getItem('token'))
  const [loading, setLoading] = useState(false)
  const [ready,   setReady]   = useState(false)

  // ── Helper: set token in state + axios headers + localStorage ───────────────
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

  // ── Helper: set user in state + localStorage ────────────────────────────────
  const applyUser = useCallback((u) => {
    // Merge with cached user to avoid losing fields the API omitted
    const merged = u ? { ...loadCachedUser(), ...u } : null
    setUser(merged)
    saveUser(merged)
  }, [])

  // ── Helper: clear everything ────────────────────────────────────────────────
  const clearSession = useCallback(() => {
    applyToken(null)
    applyUser(null)
  }, [applyToken, applyUser])

  // ── Restore session on mount ────────────────────────────────────────────────
  useEffect(() => {
    if (!token) {
      setReady(true)
      return
    }
    // Apply saved token to axios immediately
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

    axios.get('/api/auth/me')
      .then(res => {
        const fresh = extractUser(res.data)
        if (fresh) {
          applyUser(fresh)
        } else {
          // Token valid but empty response — keep cache
        }
      })
      .catch(() => {
        // 401 or network error
        // If 401 → clear session. If network error → keep cache so user
        // isn't logged out on a bad connection.
        // We check by seeing if there's any cached user; if not, clear.
        if (!loadCachedUser()) clearSession()
      })
      .finally(() => setReady(true))
  }, []) // eslint-disable-line react-hooks/exhaustive-deps

  // ── refreshUser — call after profile updates ────────────────────────────────
  const refreshUser = useCallback(async () => {
    try {
      const res = await axios.get('/api/auth/me')
      const fresh = extractUser(res.data)
      if (fresh) applyUser(fresh)
      return fresh
    } catch { return null }
  }, [applyUser])

  // ── LOGIN ───────────────────────────────────────────────────────────────────
  const login = useCallback(async (usernameOrEmail, password) => {
    setLoading(true)
    try {
      // Detect whether the user typed an email or a username
      const isEmail = usernameOrEmail.includes('@')
      const payload = isEmail
        ? { email: usernameOrEmail, password }
        : { username: usernameOrEmail, password }

      const res = await axios.post('/api/auth/login', payload)

      // Token may be at res.data.token or res.data.data.token
      const t = res.data?.token ?? res.data?.data?.token
      const u = extractUser(res.data)

      if (!t || !u) throw new Error('Unexpected login response')

      applyToken(t)
      applyUser(u)
      return { success: true }
    } catch (e) {
      const data = e.response?.data
      let msg = 'Login failed. Check your username and password.'
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
  const register = useCallback(async (username, email, password, confirm) => {
    setLoading(true)
    try {
      await axios.post('/api/auth/register', {
        username,
        email,
        password,
        password_confirmation: confirm,
      })
      // Do NOT apply token/user here — user must manually log in to confirm
      return { success: true }
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
      const msg =
        res.data?.message ||
        res.data?.status  ||
        'Password reset link sent! Please check your email.'
      return { success: true, message: msg }
    } catch (e) {
      const data = e.response?.data
      let msg = 'Failed to send reset email. Please try again.'
      if (data?.errors?.email) {
        msg = data.errors.email[0]
      } else if (data?.message) {
        msg = data.message
      } else if (data?.status) {
        const statusMap = {
          'passwords.throttled': 'Too many attempts. Please wait before requesting another link.',
          'passwords.user':      'No account found with that email address.',
        }
        msg = statusMap[data.status] || data.status
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
