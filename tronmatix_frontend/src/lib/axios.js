// src/lib/axios.js
import axios from 'axios'

const isProd = import.meta.env.PROD

const baseURL = isProd
  ? (import.meta.env.VITE_API_URL ?? '')   // e.g. "https://api.yoursite.com"
  : ''                                      // empty = same origin, Vite proxy handles /api

if (isProd && !import.meta.env.VITE_API_URL) {
  console.error('❌ VITE_API_URL is not set in production — API calls will fail')
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

// ── Request interceptor: attach Bearer token ────────────────────────────────
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

// ── Response interceptor: handle 401 globally ───────────────────────────────
instance.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status

    if (status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      window.location.replace('/')
    }

    if (import.meta.env.DEV) {
      console.error('API Error:', error.config?.url, error.message)
    }

    return Promise.reject(error)
  }
)

export default instance