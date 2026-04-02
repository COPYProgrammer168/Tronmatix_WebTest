// src/lib/resolveImage.js
//
// Resolves a DB image path to a usable URL in the frontend.
//
// DB values come in two shapes:
//   Local  : "/storage/products/abc123.webp"   → prefix with VITE_API_URL
//   Cloud  : "https://bucket.r2.dev/img.webp"  → use as-is
//   Legacy : "storage/products/img.jpg"         → normalize to /storage/... then prefix
//   External paste: "https://cdn.example.com/img.png" → use as-is

const BACKEND_URL = (import.meta.env.VITE_API_URL || '').replace(/\/$/, '')

/**
 * Resolve a DB image path to a full URL the browser can load.
 *
 * @param {string|null|undefined} path - Value from DB / API
 * @returns {string|null} - Full URL, or null if no image
 */
export function resolveImage(path) {
  if (!path || typeof path !== 'string') return null

  const trimmed = path.trim()
  if (!trimmed) return null

  // Already a full URL (S3, R2, CDN, external paste) — use as-is
  if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) {
    return trimmed
  }

  // Normalize: add leading slash if missing
  const normalized = trimmed.startsWith('/') ? trimmed : '/' + trimmed

  // Local storage path — prefix with backend URL
  return BACKEND_URL + normalized
}

/**
 * Get the first valid image from a product's images array,
 * falling back to the legacy single `image` field.
 *
 * @param {object} product - Product object from API
 * @returns {string|null}
 */
export function resolveProductImage(product) {
  if (!product) return null

  const images = Array.isArray(product.images) ? product.images : []

  for (const img of images) {
    const resolved = resolveImage(img)
    if (resolved) return resolved
  }

  // Legacy fallback
  return resolveImage(product.image) || null
}

export default resolveImage