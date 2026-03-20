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