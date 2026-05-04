import axios from 'axios'

const isProd = import.meta.env.PROD

// Never throw at module level — a module-level throw crashes the entire app
// before React mounts, causing a blank white page.
// Fall back to relative URLs when VITE_API_URL is not set (same-origin deploys).
// Priority: VITE_API_URL → window.location.origin (auto-detects ngrok/localhost/prod)
const VITE_URL = (import.meta.env.VITE_API_URL || '').replace(/\/$/, '')
const baseURL = VITE_URL || (typeof window !== 'undefined' ? window.location.origin : '')

if (isProd && !import.meta.env.VITE_API_URL) {
  console.warn(
    '[axios] VITE_API_URL is not set. API calls will use relative URLs.\n' +
    'If your frontend and backend are on different origins, set VITE_API_URL in your deployment env vars.'
  )
}

// ── Auth storage keys ─────────────────────────────────────────────────────────
// Only THESE keys are wiped on session expiry. Never bluntly `localStorage.clear()`
// which would destroy unrelated data belonging to other features or third-party libs.
export const AUTH_STORAGE_KEYS = ['token', 'tronmatix_user']

export function clearAuthStorage() {
  AUTH_STORAGE_KEYS.forEach((key) => localStorage.removeItem(key))
}

// ── Axios instance ────────────────────────────────────────────────────────────
const instance = axios.create({
  baseURL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'ngrok-skip-browser-warning': 'true',
  },
  withCredentials: false,
  timeout: 15000,
})

// ── Request interceptor: attach Bearer token ──────────────────────────────────
instance.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) config.headers.Authorization = `Bearer ${token}`
    return config
  },
  (error) => Promise.reject(error)
)

// ── 401 guard: prevent cascading redirects if multiple requests fail at once ──
let isHandling401 = false

// ── Response interceptor ──────────────────────────────────────────────────────
instance.interceptors.response.use(
  (res) => res,
  (error) => {
    const status = error.response?.status

    if (status === 401 && !isHandling401) {
      isHandling401 = true

      // Remove only auth-related keys — leave everything else intact
      clearAuthStorage()

      const protectedPaths = ['/orders', '/profile', '/checkout', '/cart']
      const onProtected = protectedPaths.some((p) =>
        window.location.pathname.startsWith(p)
      )
      if (onProtected) {
        window.location.replace('/')
      }

      // Reset flag after redirect / settling so future logins work
      setTimeout(() => { isHandling401 = false }, 2000)
    }

    // Tag network (no-response) errors so callers can distinguish them from
    // server errors and avoid logging users out on a bad connection.
    if (!error.response) {
      error.isNetworkError = true
    }

    return Promise.reject(error)
  }
)

export default instance