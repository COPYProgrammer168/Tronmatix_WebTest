import axios from 'axios'

const isProd = import.meta.env.PROD

const baseURL = isProd
  ? import.meta.env.VITE_API_URL
  : ''

if (isProd && !import.meta.env.VITE_API_URL) {
  throw new Error('VITE_API_URL is not set!')
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