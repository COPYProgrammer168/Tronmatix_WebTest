// src/lib/axios.js

import axios from 'axios'

const isProd = import.meta.env.PROD

// DEV:  '' → Vite proxy → http://127.0.0.1:8000
// PROD: 'https://tronmatix-beckend.onrender.com'
const baseURL = isProd
  ? (import.meta.env.VITE_API_URL ?? '')
  : ''

if (isProd && !import.meta.env.VITE_API_URL) {
  console.error('❌ VITE_API_URL is not set — API calls will fail in production')
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
      // FIX: clear both keys to be safe
      localStorage.removeItem('token')
      localStorage.removeItem('tronmatix_user')
      localStorage.removeItem('auth_token')  // legacy cleanup
      localStorage.removeItem('auth_user')   // legacy cleanup
      window.location.replace('/')
    }

    if (import.meta.env.DEV) {
      console.error(`API Error [${status}]:`, error.config?.url, error.message)
    }

    return Promise.reject(error)
  }
)

export default instance