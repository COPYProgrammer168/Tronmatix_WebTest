// src/context/AuthContext.jsx

import { createContext, useContext, useState, useEffect, useCallback } from 'react'
import axios from '../lib/axios'

const AuthContext = createContext(null)

// ── localStorage key constants ─────────────────────────────────────────────────
// FIX: Define keys as constants — used in both AuthContext and axios.js
// axios.js reads TOKEN_KEY to attach Bearer header
export const TOKEN_KEY = 'token'
export const USER_KEY  = 'tronmatix_user'

// ── Axios defaults ─────────────────────────────────────────────────────────────
axios.defaults.withCredentials = false
axios.defaults.headers.common['Accept']       = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

// ── localStorage helpers ───────────────────────────────────────────────────────
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

// ── Normalize API response into plain user object ──────────────────────────────
function extractUser(responseData) {
  if (!responseData) return null
  if (responseData.data?.id)  return responseData.data
  if (responseData.user?.id)  return responseData.user
  if (responseData.id)        return responseData
  return null
}

// ── Provider ───────────────────────────────────────────────────────────────────
export function AuthProvider({ children }) {
  const [user,    setUser]    = useState(() => loadCachedUser())
  const [token,   setToken]   = useState(() => localStorage.getItem(TOKEN_KEY))
  const [loading, setLoading] = useState(false)
  const [ready,   setReady]   = useState(false)

  // ── Apply token to state + axios + localStorage ────────────────────────────
  const applyToken = useCallback((t) => {
    if (t) {
      localStorage.setItem(TOKEN_KEY, t)
      axios.defaults.headers.common['Authorization'] = `Bearer ${t}`
    } else {
      localStorage.removeItem(TOKEN_KEY)
      delete axios.defaults.headers.common['Authorization']
    }
    setToken(t)
  }, [])

  // ── Apply user to state + localStorage ────────────────────────────────────
  const applyUser = useCallback((u) => {
    const merged = u ? { ...loadCachedUser(), ...u } : null
    setUser(merged)
    saveUser(merged)
  }, [])

  // ── Clear session ──────────────────────────────────────────────────────────
  const clearSession = useCallback(() => {
    applyToken(null)
    applyUser(null)
  }, [applyToken, applyUser])

  // ── Restore session on mount ───────────────────────────────────────────────
  useEffect(() => {
    if (!token) {
      setReady(true)
      return
    }

    // Re-apply token to axios on page refresh
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

    axios.get('/api/auth/me')
      .then(res => {
        const fresh = extractUser(res.data)
        if (fresh) applyUser(fresh)
      })
      .catch((err) => {
        // 401 = invalid token → clear session
        if (err.response?.status === 401) {
          clearSession()
        }
        // Network error → keep cache so user isn't logged out offline
      })
      .finally(() => setReady(true))
  }, []) // eslint-disable-line react-hooks/exhaustive-deps

  // ── Refresh user from API ──────────────────────────────────────────────────
  const refreshUser = useCallback(async () => {
    try {
      const res = await axios.get('/api/auth/me')
      const fresh = extractUser(res.data)
      if (fresh) applyUser(fresh)
      return fresh
    } catch { return null }
  }, [applyUser])

  // ── LOGIN ──────────────────────────────────────────────────────────────────
  const login = useCallback(async (username, password) => {
    setLoading(true)
    try {
      const res = await axios.post('/api/auth/login', { username, password })

      const t = res.data?.token ?? res.data?.data?.token
      const u = extractUser(res.data)

      if (!t || !u) throw new Error('Unexpected login response')

      applyToken(t)
      applyUser(u)
      return { success: true }
    } catch (e) {
      const data = e.response?.data
      let msg = 'Login failed. Check your username and password.'
      if (data?.errors)  msg = Object.values(data.errors).flat()[0] || msg
      else if (data?.message) msg = data.message
      return { success: false, message: msg }
    } finally {
      setLoading(false)
    }
  }, [applyToken, applyUser])

  // ── REGISTER ───────────────────────────────────────────────────────────────
  const register = useCallback(async (username, email, password, confirm) => {
    setLoading(true)
    try {
      const res = await axios.post('/api/auth/register', {
        username,
        email,
        password,
        password_confirmation: confirm,
      })

      const t = res.data?.token ?? res.data?.data?.token
      const u = extractUser(res.data)

      if (!t || !u) throw new Error('Unexpected register response')

      applyToken(t)
      applyUser(u)
      return { success: true }
    } catch (e) {
      const data = e.response?.data
      let msg = 'Registration failed.'
      if (data?.errors)       msg = Object.values(data.errors).flat().join(' ')
      else if (data?.message) msg = data.message
      return { success: false, message: msg }
    } finally {
      setLoading(false)
    }
  }, [applyToken, applyUser])

  // ── FORGOT PASSWORD ────────────────────────────────────────────────────────
  const forgotPassword = useCallback(async (email) => {
    setLoading(true)
    try {
      const res = await axios.post('/api/auth/forgot-password', { email })
      const msg = res.data?.message || res.data?.status || 'Password reset link sent!'
      return { success: true, message: msg }
    } catch (e) {
      const data = e.response?.data
      let msg = 'Failed to send reset email. Please try again.'
      if (data?.errors?.email)  msg = data.errors.email[0]
      else if (data?.message)   msg = data.message
      else if (data?.status) {
        const map = {
          'passwords.throttled': 'Too many attempts. Please wait.',
          'passwords.user': 'No account found with that email.',
        }
        msg = map[data.status] || data.status
      }
      return { success: false, message: msg }
    } finally {
      setLoading(false)
    }
  }, [])

  // ── LOGOUT ─────────────────────────────────────────────────────────────────
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
