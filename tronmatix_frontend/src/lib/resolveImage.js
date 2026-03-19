const BACKEND_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'
 
export function resolveImage(path) {
  if (!path) return null
 
  // Already a full URL (S3, R2, CDN, or external) — use as-is
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path
  }
 
  // Local storage path — prefix with backend URL
  return BACKEND_URL + (path.startsWith('/') ? path : '/' + path)
}
 
export default resolveImage