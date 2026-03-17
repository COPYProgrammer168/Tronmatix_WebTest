// src/lib/axios.js
import axios from 'axios'

const instance = axios.create({
  // ✅ NO baseURL — requests stay relative to localhost:5173
  // Vite proxy intercepts /api/* and /storage/* and forwards to Laravel
  // ❌ DO NOT set baseURL: 'http://localhost:8000' — that bypasses Vite proxy → CORS error
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: false,
  timeout: 15000, // 15s timeout
})

// ── Request interceptor: attach Bearer token if present ───────────────────
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

// ── Response interceptor: handle 401 globally ─────────────────────────────
instance.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expired / invalid — clear and redirect to home
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      window.location.href = '/'
    }
    return Promise.reject(error)
  }
)

export default instance