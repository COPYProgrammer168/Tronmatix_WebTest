// src/lib/axios.js
import axios from 'axios'

// Detect environment
const isProd = import.meta.env.PROD

// Base URL strategy
const baseURL = isProd
  ? import.meta.env.VITE_API_URL // production API
  : '/api' // use Vite proxy in development

if (isProd && !baseURL) {
  console.error("❌ VITE_API_URL is missing in production environment")
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

// ── Request interceptor ─────────────────────────────
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

// ── Response interceptor ────────────────────────────
instance.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status

    // Handle Unauthorized
    if (status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')

      // Better: soft redirect (SPA-safe)
      window.location.replace('/')
    }

    // Optional: log errors for debugging
    if (import.meta.env.DEV) {
      console.error('API Error:', error)
    }

    return Promise.reject(error)
  }
)

export default instance