// src/context/AuthContext.jsx
import { createContext, useContext, useState, useEffect, useCallback, useRef } from 'react'
import api, { clearAuthStorage } from '../lib/axios'

const AuthContext = createContext(null)

const USER_KEY  = 'tronmatix_user'
const TOKEN_KEY = 'token'

// ── Helpers ───────────────────────────────────────────────────────────────────

// Sanctum tokens are opaque (not JWT) — no expiry embedded in them.
// The only reliable expiry check is a 401 from the server.
// We keep this guard only as a last-resort sanity check for actual JWTs.
function isTokenExpired(token) {
  try {
    const parts = token.split('.')
    if (parts.length !== 3) return false // opaque Sanctum token — not a JWT
    const json = JSON.parse(atob(parts[1].replace(/-/g, '+').replace(/_/g, '/')))
    return json.exp ? json.exp * 1000 < Date.now() - 30_000 : false
  } catch {
    return false // parse failed → assume valid, let server decide
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

// Normalize API response shapes: { data: user }, { user: user }, or flat user
function extractUser(responseData) {
  if (!responseData) return null
  if (responseData.data?.id) return responseData.data
  if (responseData.user?.id) return responseData.user
  if (responseData.id)       return responseData
  return null
}

// ── Provider ──────────────────────────────────────────────────────────────────

export function AuthProvider({ children }) {
  const [user,    setUser]    = useState(() => loadCachedUser())
  const [token,   setToken]   = useState(() => localStorage.getItem(TOKEN_KEY))
  const [loading, setLoading] = useState(false)
  const [ready,   setReady]   = useState(false)

  // Ref so interceptors & effects always read the latest value without stale closures
  const tokenRef = useRef(token)

  const applyToken = useCallback((t) => {
    tokenRef.current = t
    if (t) {
      localStorage.setItem(TOKEN_KEY, t)
      // Keep the axios instance header in sync — the request interceptor also
      // reads localStorage, so this is belt-and-suspenders for the initial mount.
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

  // ── Restore session on mount ───────────────────────────────────────────────
  // Strategy:
  //  1. Immediately show cached user (no flicker — already in useState init).
  //  2. Try to refresh from /api/auth/me in the background.
  //  3. On 401 → clear (token revoked server-side).
  //  4. On NETWORK ERROR → keep cached user (no internet ≠ logged out).
  useEffect(() => {
    const storedToken = localStorage.getItem(TOKEN_KEY)

    if (!storedToken) {
      setReady(true)
      return
    }

    // Expired JWT (rare with Sanctum opaque tokens, but guard anyway)
    if (isTokenExpired(storedToken)) {
      clearSession()
      setReady(true)
      return
    }

    // Ensure header is set before the /me request fires
    api.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`
    tokenRef.current = storedToken

    api.get('/api/auth/me')
      .then(res => {
        const fresh = extractUser(res.data)
        if (fresh) applyUser(fresh)
        // else: /me returned unexpected shape — keep cached user
      })
      .catch((err) => {
        if (err.response?.status === 401) {
          // Token revoked or expired server-side → force logout
          clearSession()
        }
        // FIX: Network/timeout errors → do NOT clear session.
        // User is still logged in — they just have no internet right now.
        // err.isNetworkError is set by the axios interceptor in axios.js
      })
      .finally(() => setReady(true))
  }, []) // eslint-disable-line react-hooks/exhaustive-deps

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
      // Backend accepts email in the `username` field (case-insensitive lookup)
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
    try { await api.post('/api/auth/logout') } catch { /* ignore — clear locally anyway */ }
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
