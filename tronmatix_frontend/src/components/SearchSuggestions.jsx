import { useState, useEffect, useRef, useMemo } from 'react'
import { useNavigate } from 'react-router-dom'
import axios from '../lib/axios'
import { useTheme } from '../context/ThemeContext'

const DEBOUNCE_MS = 280

function resolveImage(path) {
  if (!path || typeof path !== 'string') return null
  const t = path.trim()
  if (!t) return null
  if (t.startsWith('http://') || t.startsWith('https://')) return t
  const base = (import.meta.env.VITE_API_URL || '').replace(/\/$/, '')
  return base + (t.startsWith('/') ? t : '/' + t)
}

function formatPrice(n) {
  if (n == null) return ''
  return Number(n).toLocaleString('en-US') + '$'
}

export default function SearchSuggestions({ query, onClose, onClear, inputRef }) {
  const [items, setItems] = useState([])
  const [loading, setLoading] = useState(false)
  const { dark } = useTheme()
  const navigate = useNavigate()
  const wrapRef = useRef(null)
  const timerRef = useRef(null)

  useEffect(() => {
    if (timerRef.current) clearTimeout(timerRef.current)

    const trimmed = query.trim()
    if (trimmed.length < 2) {
      setItems([])
      setLoading(false)
      return
    }

    setLoading(true)
    timerRef.current = setTimeout(async () => {
      try {
        const { data } = await axios.get('/api/products/suggestions', { params: { q: trimmed } })
        setItems(Array.isArray(data.data) ? data.data : [])
      } catch {
        setItems([])
      } finally {
        setLoading(false)
      }
    }, DEBOUNCE_MS)

    return () => { if (timerRef.current) clearTimeout(timerRef.current) }
  }, [query])

  // Close on outside click (but not when clicking the input itself)
  useEffect(() => {
    if (items.length === 0 && !loading) return
    const handler = (e) => {
      if (wrapRef.current && !wrapRef.current.contains(e.target)) {
        // Don't close if clicking back into the search input
        if (inputRef?.current && inputRef.current.contains(e.target)) return
        onClose?.()
      }
    }
    document.addEventListener('mousedown', handler, true)
    return () => document.removeEventListener('mousedown', handler, true)
  }, [items, loading, onClose, inputRef])

  // Close on Escape
  useEffect(() => {
    if (items.length === 0 && !loading) return
    const handler = (e) => {
      if (e.key === 'Escape') onClose?.()
    }
    document.addEventListener('keydown', handler)
    return () => document.removeEventListener('keydown', handler)
  }, [items, loading, onClose])

  const show = items.length > 0 || loading

  const panelBg = dark ? '#111827' : '#FFFFFF'
  const panelBorder = dark ? 'rgba(249,115,22,0.3)' : 'rgba(0,0,0,0.08)'
  const hoverBg = dark ? 'rgba(249,115,22,0.08)' : 'rgba(249,115,22,0.05)'
  const subText = dark ? '#9ca3af' : '#6b7280'
  const text = dark ? '#f9fafb' : '#0F172A'

  if (!show) return null

  return (
    <div
      ref={wrapRef}
      className="absolute left-0 right-0 z-[300] overflow-hidden"
      style={{
        top: 'calc(100% + 6px)',
        background: panelBg,
        border: `1px solid ${panelBorder}`,
        borderRadius: 12,
        boxShadow: dark
          ? '0 20px 60px rgba(0,0,0,0.55), 0 0 0 1px rgba(249,115,22,0.12) inset'
          : '0 20px 60px rgba(0,0,0,0.12), 0 0 0 1px rgba(255,255,255,0.6) inset',
      }}
    >
      {/* Skeleton */}
      {loading && (
        <div className="px-3 py-2.5 flex items-center gap-3" style={{ borderBottom: `1px solid ${panelBorder}` }}>
          {Array.from({ length: 3 }).map((_, i) => (
            <div key={i} className="rounded-lg" style={{ width: 40, height: 40, background: dark ? '#1f2937' : '#e5e7eb', opacity: 0.5, flexShrink: 0 }} />
          ))}
          <div className="flex flex-col gap-1.5 flex-1">
            <div className="rounded" style={{ height: 12, width: '60%', background: dark ? '#1f2937' : '#e5e7eb', opacity: 0.5 }} />
            <div className="rounded" style={{ height: 10, width: '35%', background: dark ? '#1f2937' : '#e5e7eb', opacity: 0.35 }} />
          </div>
        </div>
      )}

      {/* Product rows */}
      {!loading && items.map((p) => {
        const img = resolveImage(p.image)
        return (
          <a
            key={p.id}
            href="#"
            onClick={(e) => {
              e.preventDefault()
              onClose?.()
              onClear?.()
              navigate(`/product/${p.slug || p.id}`)
            }}
            className="flex items-center gap-3 px-3 py-2.5 transition-colors"
            style={{
              borderBottom: `1px solid ${dark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.04)'}`,
              textDecoration: 'none',
              background: 'transparent',
            }}
            onMouseEnter={(e) => { e.currentTarget.style.background = hoverBg }}
            onMouseLeave={(e) => { e.currentTarget.style.background = 'transparent' }}
          >
            {/* Thumbnail */}
            <div
              className="rounded-lg flex-shrink-0 overflow-hidden"
              style={{
                width: 44,
                height: 44,
                background: dark ? '#1f2937' : '#f3f4f6',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
              }}
            >
              {img ? (
                <img src={img} alt="" loading="lazy" onError={(e) => { e.target.style.display = 'none' }}
                  style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }} />
              ) : (
                <svg className="w-5 h-5" style={{ color: dark ? '#4b5563' : '#9ca3af' }} fill="none" stroke="currentColor" strokeWidth={1.8} viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
              )}
            </div>

            {/* Info */}
            <div className="flex-1 min-w-0">
              <div
                className="font-bold truncate"
                style={{ fontSize: 18, color: text, fontFamily: 'Rajdhani, sans-serif', letterSpacing: 0.3 }}
              >
                {p.name}
              </div>
              <div
                className="font-black"
                style={{ fontSize: 20, color: '#F97316', fontFamily: 'Rajdhani, sans-serif' }}
              >
                {formatPrice(p.price)}
              </div>
            </div>

            {/* Arrow */}
            <svg className="w-3.5 h-3.5 flex-shrink-0" style={{ color: subText }} fill="none" stroke="currentColor" strokeWidth={2.5} viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </a>
        )
      })}

      {/* View all results link */}
      {!loading && items.length > 0 && (
        <button
          onClick={() => {
            onClose?.()
            onClear?.()
            navigate(`/category/search?q=${encodeURIComponent(query.trim())}`)
          }}
          className="w-full text-center py-2 font-bold transition-colors"
          style={{
            fontSize: 12,
            letterSpacing: 0.8,
            color: '#F97316',
            background: 'transparent',
            border: 'none',
            cursor: 'pointer',
            fontFamily: 'Rajdhani, sans-serif',
          }}
          onMouseEnter={(e) => { e.currentTarget.style.background = dark ? 'rgba(249,115,22,0.06)' : 'rgba(249,115,22,0.04)' }}
          onMouseLeave={(e) => { e.currentTarget.style.background = 'transparent' }}
        >
          VIEW ALL RESULTS →
        </button>
      )}
    </div>
  )
}
