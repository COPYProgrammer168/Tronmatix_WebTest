// src/lib/axios.js

import axios from 'axios'

const isProd = import.meta.env.PROD

// DEV:  '' → Vite proxy → http://127.0.0.1:8000
// PROD: must set VITE_API_URL in Render environment variables
//       e.g. https://tronmatix-beckend.onrender.com
const baseURL = isProd
  ? (import.meta.env.VITE_API_URL ?? '')
  : ''

// Fail loudly in prod if the env var is missing — empty baseURL means
// every /api/* call hits the static frontend domain and returns HTML, not JSON
if (isProd && !import.meta.env.VITE_API_URL) {
  console.error(
    '❌ VITE_API_URL is not set!\n' +
    'Go to Render → your frontend service → Environment → Add:\n' +
    '  Key:   VITE_API_URL\n' +
    '  Value: https://tronmatix-beckend.onrender.com\n' +
    'Then trigger a manual redeploy.'
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

// ── Request interceptor: attach Bearer token ──────────────────────────────────
instance.interceptors.request.use(
  (config) => {
    // FIX: AuthContext saves token as 'token' — must read same key here
    // Previously was 'auth_token' → token never attached → all API calls 401
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// ── Response interceptor ───────────────────────────────────────────────────────
instance.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status

    if (status === 401) {
      // Clear all token keys — legacy and current
      localStorage.removeItem('token')
      localStorage.removeItem('tronmatix_user')
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')

      // FIX: do NOT redirect with window.location.replace('/').
      // Redirecting caused an infinite reload loop on mobile:
      //   stale token → 401 → redirect to / → reload → 401 → redirect → ...
      // Public pages (home, products) don't need auth — just clear the token
      // and let React re-render in unauthenticated state naturally.
      // Only redirect if the current page actually requires auth.
      const publicPaths = ['/', '/category', '/products', '/contact']
      const isPublic = publicPaths.some(p => window.location.pathname.startsWith(p))
      if (!isPublic) {
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
