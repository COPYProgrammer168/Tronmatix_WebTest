// src/lib/axios.js
import axios from 'axios'

const isProd = import.meta.env.PROD

const baseURL = isProd
  ? (import.meta.env.VITE_API_URL ?? '')
  : ''

if (isProd && !import.meta.env.VITE_API_URL) {
  console.error(
    '❌ VITE_API_URL is not set! Set it in Render → Environment → VITE_API_URL'
  )
}

const instance = axios.create({
  baseURL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  withCredentials: false,
  timeout: 15000,
})

<<<<<<< HEAD
// ── Request interceptor ───────────────────────────────────────────────────────
=======
// ── Request interceptor: attach Bearer token ─────────────────────────────────
>>>>>>> 68d08f38a8ddbc6562aef8beae5a7419c0f5bc7c
instance.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) config.headers.Authorization = `Bearer ${token}`
    return config
  },
  (error) => Promise.reject(error)
)

// ── Response interceptor ──────────────────────────────────────────────────────
instance.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status

    if (status === 401) {
      // Clear all stored auth keys
      localStorage.removeItem('token')
      localStorage.removeItem('tronmatix_user')
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')

<<<<<<< HEAD
      // FIX: only redirect on protected pages to prevent infinite reload loop.
      // Public pages (home, products) don't need auth — just clear the token
      // and let React re-render unauthenticated without triggering a reload.
      const protectedPaths = ['/orders', '/profile', '/checkout', '/cart']
      const onProtected = protectedPaths.some(
        (p) => window.location.pathname.startsWith(p)
      )
=======
      // Only redirect if on a protected page.
      // Public pages (home, products) don't need auth — just clear the token
      // and let React re-render unauthenticated. No reload loop.
      const protectedPaths = ['/orders', '/profile', '/checkout', '/cart']
      const onProtected = protectedPaths.some(p => window.location.pathname.startsWith(p))
>>>>>>> 68d08f38a8ddbc6562aef8beae5a7419c0f5bc7c
      if (onProtected) {
        window.location.replace('/')
      }
    }

    if (import.meta.env.DEV) {
      console.error(`API Error [${status}]:`, error.config?.url, error.message)
    }

    return Promise.reject(error)
  }
)

export default instance
