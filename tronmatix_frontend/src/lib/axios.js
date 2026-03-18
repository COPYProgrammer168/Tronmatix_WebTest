// src/lib/axios.js

import axios from 'axios'

// ── Environment detection ────────────────────────────────────────────────────
const isProd = import.meta.env.PROD

// ── Base URL strategy ────────────────────────────────────────────────────────
// DEV:  baseURL = ''  →  Vite proxy intercepts /api/* → http://127.0.0.1:8000
// PROD: baseURL = 'https://tronmatix-beckend.onrender.com'
//       every axios.get('/api/products') becomes:
//       https://tronmatix-beckend.onrender.com/api/products  ✅
const baseURL = isProd
  ? (import.meta.env.VITE_API_URL ?? '')
  : ''

if (isProd && !import.meta.env.VITE_API_URL) {
  console.error('❌ VITE_API_URL is not set — API calls will fail in production')
}

// ── Axios instance ───────────────────────────────────────────────────────────
const instance = axios.create({
  baseURL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  withCredentials: false, // Bearer token auth — no cookies needed
  timeout: 15000,
})

// ── Request interceptor: attach Bearer token ─────────────────────────────────
instance.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// ── Response interceptor: global error handling ───────────────────────────────
instance.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status

    // Auto logout on 401 Unauthorized
    if (status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      window.location.replace('/')
    }

    // Log errors in development only
    if (import.meta.env.DEV) {
      console.error(`API Error [${status}]:`, error.config?.url, error.message)
    }

    return Promise.reject(error)
  }
)

export default instance