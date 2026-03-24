import axios from 'axios'

const isProd = import.meta.env.PROD

// never throw at module level — a module-level throw crashes the ENTIRE
// app before React mounts, causing a blank white page with "loading is not defined"
// in the minified bundle stack trace.
// Instead: warn in console and fall back to relative URLs (works when frontend
// and backend are on the same origin, e.g. a monorepo deploy).
const baseURL = isProd
  ? (import.meta.env.VITE_API_URL || '')
  : ''

if (isProd && !import.meta.env.VITE_API_URL) {
  console.warn(
    '[axios] VITE_API_URL is not set. API calls will use relative URLs.\n' +
    'If your frontend and backend are on different origins, set VITE_API_URL in your deployment env vars.'
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

instance.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

instance.interceptors.response.use(
  (res) => res,
  (error) => {
    const status = error.response?.status

    if (status === 401) {
      localStorage.clear()

      const protectedPaths = ['/orders', '/profile', '/checkout', '/cart']
      const onProtected = protectedPaths.some((p) =>
        window.location.pathname.startsWith(p)
      )

      if (onProtected) {
        window.location.replace('/')
      }
    }

    return Promise.reject(error)
  }
)

export default instance