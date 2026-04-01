import { useState, useEffect, useRef } from 'react'
import { Link } from 'react-router-dom'
import ProductCard from '../components/ProductCard'
import { useTheme } from '../context/ThemeContext'
import axios from '../lib/axios'

const LARAVEL_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

const FALLBACK_BANNERS = [
  { id: 1, title: 'WHITE SET\nHIGH END PC BUILD', subtitle: 'AMD RYZEN 9950X3D / RTX5080', badge: 'NEW ARRIVAL', bg_color: '#f5f0e8', text_color: '#c8860a', image: null },
  { id: 2, title: 'PC BUILD BUDGET 3K\nFOR GAMING', subtitle: 'Best price guaranteed', badge: 'HOT DEAL', bg_color: '#111111', text_color: '#F97316', image: null },
]

const categories = ['CPU', 'RAM', 'MAINBOARD', 'MONITOR', 'COOLING', 'M2', 'VGA', 'POWER SUPPLY']

const CAT_SUBS = {
  'MONITOR':      ['MONITOR 25INCH','MONITOR 27INCH','MONITOR 32INCH','MONITOR 34INCH','MONITOR 39INCH','MONITOR 42INCH','MONITOR 48INCH','MONITOR 49INCH'],
  'CPU':          ['CPU'],
  'RAM':          ['RAM'],
  'MAINBOARD':    ['MAINBOARD'],
  'COOLING':      ['COOLING'],
  'M2':           ['M2'],
  'VGA':          ['VGA'],
  'POWER SUPPLY': ['POWER SUPPLY'],
}
const mockProducts = Array(6).fill(null).map((_, i) => ({
  id: i + 1, name: i === 0 ? 'AMD RYZEN 7 9800X3D' : 'AMD RYZEN 7 9700X', price: [349, 299, 389, 319, 279, 359][i], image: null,
}))

export function resolveImage(path) {
  if (!path) return null
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  return LARAVEL_URL + (path.startsWith('/') ? path : '/' + path)
}

export default function HomePage() {
  const [slide, setSlide]       = useState(0)
  const [banners, setBanners]   = useState(FALLBACK_BANNERS)
  const [products, setProducts] = useState({})
  const [loading, setLoading]   = useState(true)
  const [newProducts, setNewProducts] = useState([])
  const [catPage, setCatPage]         = useState({})   // { CPU: 1, RAM: 1, ... }
  const newProdRef = useRef(null)
  const catRefs   = useRef({})
  const { dark } = useTheme()

  const bg      = dark ? '#111827' : '#fff'
  const text    = dark ? '#f9fafb' : '#1f2937'
  const headerL = dark ? '#1f2937' : '#000'
  const navBtn  = dark ? '#374151' : '#fff'
  const navBrd  = dark ? '#4b5563' : '#d1d5db'

  useEffect(() => {
    axios.get('/api/banners')
      .then(res => {
        const data = Array.isArray(res.data) ? res.data : (res.data?.data ?? [])
        const active = data.filter(b => b.active !== false)
        if (active.length > 0) setBanners(active)
      }).catch(() => {})
  }, [])

  useEffect(() => {
    if (banners.length <= 1) return
    const t = setInterval(() => setSlide(s => (s + 1) % banners.length), 5000)
    return () => clearInterval(t)
  }, [banners.length])

  useEffect(() => {
    setSlide(s => Math.min(s, Math.max(banners.length - 1, 0)))
  }, [banners.length])

  useEffect(() => {
    // Fetch newest products for the "NEW PRODUCTS" section
    axios.get('/api/products', { params: { sort: 'newest', per_page: 12, page: 1 } })
      .then(res => {
        const raw   = res.data
        const items = Array.isArray(raw) ? raw : (raw?.data ?? [])
        if (Array.isArray(items) && items.length > 0) setNewProducts(items)
      })
      .catch(() => {})
  }, [])

  const fetchCatPage = async (cat, page) => {
    const subs = CAT_SUBS[cat] ?? [cat]
    const params = subs.length > 1
      ? { cats: subs.join(','), per_page: 10, page }
      : { category: subs[0],   per_page: 10, page }
    try {
      const res   = await axios.get('/api/products', { params })
      const raw   = res.data
      // Handle both { data: [...], total: N } and direct array response shapes
      const items = Array.isArray(raw) ? raw : (raw?.data ?? [])
      const total = raw?.total ?? items.length
      setProducts(prev => ({ ...prev, [cat]: { items, total, page } }))
    } catch(err) {
      console.error('fetchCatPage error', cat, err?.response?.status, err?.message)
      setProducts(prev => ({ ...prev, [cat]: { items: [], total: 0, page } }))
    }
  }

  useEffect(() => {
    const init = async () => {
      const pages = {}
      await Promise.all(categories.map(cat => {
        pages[cat] = 1
        return fetchCatPage(cat, 1)
      }))
      setCatPage(pages)
      setLoading(false)
    }
    init()
  }, []) // eslint-disable-line

  const b        = banners[slide] ?? FALLBACK_BANNERS[0]
  const bgColor  = b.bg_color   || '#111'
  const txtColor = b.text_color || '#fff'
  const imgUrl   = resolveImage(b.image)
  const hasVideo = b.has_video || !!b.video
  const isGif    = b.is_gif || (imgUrl && imgUrl.toLowerCase().endsWith('.gif'))
  const videoType = b.video_type   // 'upload' | 'youtube' | 'vimeo'
  const videoSrc  = b.video        // path or embed URL
  const hasMedia  = imgUrl || hasVideo
  const detailLink = b.link
    || (b.category ? `/category/${b.category.toLowerCase()}` : null)
    || `/category/search?q=${encodeURIComponent((b.title || '').replace('\n', ' ').split(' ').slice(0, 3).join(' '))}`

  return (
    <div style={{ background: bg }}>

      <div className="max-w-[1280px] mx-auto px-4 pt-6 pb-2">
      {/* ── BANNER SLIDER ─────────────────────────────────────────────────── */}
      <div className="max-w-[1280px] mx-auto px-4 mb-8">
        <div className="relative overflow-hidden rounded-xl"
          style={{ minHeight: 444, background: hasMedia ? '#000' : bgColor, transition: 'background 0.5s' }}>

          {/* ── Video background ── */}
          {hasVideo && videoType === 'upload' && videoSrc && (
            <video
              key={videoSrc}
              className="absolute inset-0 w-full h-full object-cover"
              style={{ opacity: 0.65 }}
              src={videoSrc}
              autoPlay muted loop playsInline
              onError={e => { e.currentTarget.style.display = 'none' }}
            />
          )}

          {/* YouTube / Vimeo / Facebook embed */}
          {hasVideo && (videoType === 'youtube' || videoType === 'vimeo' || videoType === 'facebook') && videoSrc && (
            <iframe
              key={videoSrc}
              src={videoSrc}
              className="absolute inset-0 w-full h-full"
              style={{ opacity: 0.85, pointerEvents: 'none' }}
              frameBorder="0"
              allow="autoplay; encrypted-media"
              allowFullScreen
            />
          )}

          {/* ── Image overlay (shows on top of video for branding, or alone) ── */}
          {imgUrl && (
            <img
              src={imgUrl} alt={b.title}
              className="absolute inset-0 w-full h-full object-cover"
              style={{ opacity: hasVideo ? 0.25 : 0.7 }}
              onError={e => { e.currentTarget.style.display = 'none' }}
            />
          )}

          {/* Gradient overlay — always present to keep text readable */}
          <div className="absolute inset-0"
            style={{ background: 'linear-gradient(to right, rgba(0, 0, 0, 1) 0%, rgba(0,0,0,0.45) 55%, rgba(0,0,0,0.2) 100%)' }} />

          {/* ── Banner content ── */}
          <div className="relative flex items-center min-h-[260px] px-10 md:px-16 py-10" style={{ zIndex: 2 }}>
            <div style={{ maxWidth: 480 }}>
              {b.badge && (
                <span className="inline-block bg-primary text-white font-bold px-4 py-1 rounded-full mb-4"
                  style={{ fontSize: 12, letterSpacing: 2 }}>
                  {b.badge}
                </span>
              )}
              <div className="font-black whitespace-pre-line leading-tight mb-3"
                style={{ fontFamily: 'HurstBagod, Rajdhani, sans-serif', fontSize: 'clamp(24px, 4vw, 42px)', color: hasMedia ? '#fff' : txtColor }}>
                {b.title}
              </div>
              {b.subtitle && (
                <div className="font-semibold mb-6"
                  style={{ fontFamily: 'Rajdhani, sans-serif', fontSize: 'clamp(14px, 2vw, 20px)', color: hasMedia ? 'rgba(255,255,255,0.85)' : txtColor, opacity: 0.85 }}>
                  {b.subtitle}
                </div>
              )}
              <Link to={detailLink}
                className="inline-flex items-center gap-2 font-bold px-6 py-3 rounded-lg transition-all hover:scale-105 hover:opacity-90"
                style={{ fontFamily: 'Rajdhani, sans-serif', fontSize: 15, letterSpacing: 1, background: hasMedia ? '#fff' : txtColor, color: hasMedia ? '#111' : bgColor, textDecoration: 'none' }}>
                VIEW PRODUCT
                <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth={2.5} viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
              </Link>
            </div>
          </div>

          {/* Prev / Next arrows */}
          {banners.length > 1 && (<>
            <button onClick={() => setSlide(s => (s - 1 + banners.length) % banners.length)}
              className="absolute left-3 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-primary text-white w-10 h-10 flex items-center justify-center rounded-full transition-colors text-xl"
              style={{ zIndex: 3 }}>‹</button>
            <button onClick={() => setSlide(s => (s + 1) % banners.length)}
              className="absolute right-3 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-primary text-white w-10 h-10 flex items-center justify-center rounded-full transition-colors text-xl"
              style={{ zIndex: 3 }}>›</button>
          </>)}

          {/* Dot indicators */}
          {banners.length > 1 && (
            <div className="absolute bottom-4 left-10 md:left-16 flex gap-2" style={{ zIndex: 3 }}>
              {banners.map((_, i) => (
                <button key={i} onClick={() => setSlide(i)}
                  className={`h-2 rounded-full transition-all ${i === slide ? 'bg-primary w-6' : 'bg-white/50 w-2'}`} />
              ))}
            </div>
          )}
        </div>
      </div>

      {/* NEW ARRIVAL*/}
        <div className="flex items-center gap-1 justify-center">
          <span className="text-primary font-black tracking-widest" style={{ fontFamily: 'HurstBagod, Rajdhani, sans-serif', fontSize: 32 }}>NEW</span>
          <span className="font-black tracking-widest" style={{ fontFamily: 'HurstBagod, Rajdhani, sans-serif', fontSize: 32, color: text }}>ARRIVAL</span>
        </div>
      </div>

      {/* ── NEW PRODUCTS — horizontal scroll carousel ─────────────────────── */}
      {newProducts.length > 0 && (
        <div className="max-w-[1280px] mx-auto px-4 mb-10">
          {/* Header */}
          <div className="flex items-center justify-between mb-5">
            <div className="flex items-center gap-3">
              <div className="w-1 h-8 bg-primary rounded-full" />
              <span className="font-black tracking-widest"
                style={{ fontFamily: 'HurstBagod, Rajdhani, sans-serif', fontSize: 22, color: text }}>
                NEW PRODUCTS
              </span>
              <span className="inline-flex items-center justify-center font-bold px-3 rounded-full"
                style={{ background: 'rgba(249,115,22,0.12)', color: '#F97316', border: '1px solid rgba(249,115,22,0.3)', letterSpacing: 1, fontSize: 11, height: 22, lineHeight: 1 }}>
                JUST ADDED
              </span>
            </div>
            <div className="flex items-center gap-3">
              {/* Scroll arrows */}
              <div className="flex gap-2">
                {['‹','›'].map((a, i) => (
                  <button key={i}
                    onClick={() => {
                      if (!newProdRef.current) return
                      newProdRef.current.scrollBy({ left: i === 0 ? -300 : 300, behavior: 'smooth' })
                    }}
                    className="w-8 h-8 flex items-center justify-center font-bold hover:border-primary hover:text-primary transition-colors rounded"
                    style={{ border: `1px solid ${navBrd}`, color: text, background: navBtn, fontSize: 16 }}>
                    {a}
                  </button>
                ))}
              </div>
              <Link to="/search?sort=newest"
                className="text-primary font-bold hover:underline" style={{ fontSize: 15 }}>
                View all new →
              </Link>
            </div>
          </div>
          {/* Horizontal scroll row */}
          <div ref={newProdRef}
            className="new-prod-scroll flex gap-4 overflow-x-auto pb-2"
            style={{ scrollbarWidth: 'none', msOverflowStyle: 'none' }}>
            <style>{`.new-prod-scroll::-webkit-scrollbar{display:none}`}</style>
            {newProducts.map((p, i) => (
              <div key={p.id || i} style={{ minWidth: 200, maxWidth: 200, flexShrink: 0 }}>
                <ProductCard product={p} />
              </div>
            ))}
          </div>
        </div>
      )}

      {/* CATEGORY ROWS */}
      {categories.map(cat => {
        const catData  = products[cat]
        const catItems = catData?.items ?? []
        const catSlug  = cat.toLowerCase().replace(/ /g, '-')
        const scrollId = cat.replace(/ /g, '-')

        return (
          <div key={cat} className="max-w-[1280px] mx-auto px-4 mb-10">
            {/* Row header */}
            <div className="flex items-center mb-5">
              <div className="flex-1 h-12 rounded-l" style={{ background: headerL }} />
              {/* Scroll arrows — desktop only */}
              <div className="hidden lg:flex gap-1 mr-2">
                {['‹','›'].map((a, i) => (
                  <button key={i}
                    onClick={() => {
                      const el = catRefs.current[cat]
                      if (el) el.scrollBy({ left: i === 0 ? -600 : 600, behavior: 'smooth' })
                    }}
                    className="w-8 h-8 flex items-center justify-center font-bold transition-colors rounded"
                    style={{ border: `1px solid ${navBrd}`, color: text, background: navBtn, fontSize: 16 }}>
                    {a}
                  </button>
                ))}
              </div>
              <Link to={`/category/${catSlug}`}
                className="bg-primary text-white font-bold px-10 py-3 hover:bg-orange-600 transition-colors"
                style={{ fontFamily: 'Rajdhani, sans-serif', fontSize: 18, letterSpacing: 2, clipPath: 'polygon(10px 0%,100% 0%,calc(100% - 10px) 100%,0% 100%)' }}>
                {cat}
              </Link>
            </div>

            {/* Scrollbar styles */}
            <style>{`
              .cat-scroll-${scrollId}::-webkit-scrollbar { height: 4px; }
              .cat-scroll-${scrollId}::-webkit-scrollbar-track { background: rgba(249,115,22,0.10); border-radius: 2px; }
              .cat-scroll-${scrollId}::-webkit-scrollbar-thumb { background: #F97316; border-radius: 2px; }
            `}</style>

            {/* Desktop: single-row horizontal scroll with arrow nav */}
            <div
              ref={el => { catRefs.current[cat] = el }}
              className={`hidden lg:flex gap-4 overflow-x-auto pb-2 cat-scroll-${scrollId}`}
              style={{ scrollbarWidth: 'thin', scrollbarColor: '#F97316 rgba(249,115,22,0.10)', WebkitOverflowScrolling: 'touch' }}>
              {loading
                ? Array(5).fill(null).map((_, i) => (
                    <div key={i} className="rounded-xl animate-pulse flex-shrink-0"
                      style={{ width: 220, height: 280, background: dark ? '#1f2937' : '#f3f4f6' }} />
                  ))
                : catItems.length > 0
                  ? catItems.map((p, i) => (
                      <div key={p.id || i} style={{ minWidth: 220, maxWidth: 220, flexShrink: 0 }}>
                        <ProductCard product={p} />
                      </div>
                    ))
                  : (
                    <div className="py-10 text-center w-full" style={{ color: dark ? '#6b7280' : '#9ca3af' }}>
                      <div style={{ fontSize: 32, marginBottom: 6 }}>📦</div>
                      <div style={{ fontSize: 13, fontWeight: 600 }}>No {cat} products yet</div>
                    </div>
                  )
              }
            </div>

            {/* Mobile & tablet: 2-row horizontal scroll */}
            <div
              className={`lg:hidden overflow-x-auto cat-scroll-${scrollId}`}
              style={{ scrollbarWidth: 'thin', scrollbarColor: '#F97316 rgba(249,115,22,0.10)', WebkitOverflowScrolling: 'touch', paddingBottom: 6 }}>
              <div style={{
                display: 'grid',
                gridTemplateRows: 'repeat(2, auto)',
                gridAutoFlow: 'column',
                gridAutoColumns: '160px',
                gap: '10px',
                width: 'max-content',
              }}>
                {loading
                  ? Array(10).fill(null).map((_, i) => (
                      <div key={i} className="rounded-xl animate-pulse"
                        style={{ width: 160, height: 220, background: dark ? '#1f2937' : '#f3f4f6' }} />
                    ))
                  : catItems.length > 0
                    ? catItems.map((p, i) => (
                        <div key={p.id || i} style={{ width: 160 }}>
                          <ProductCard product={p} />
                        </div>
                      ))
                    : (
                      <div className="py-8 text-center" style={{ gridColumn: 'span 5', color: dark ? '#6b7280' : '#9ca3af' }}>
                        <div style={{ fontSize: 28, marginBottom: 4 }}>📦</div>
                        <div style={{ fontSize: 12, fontWeight: 600 }}>No {cat} products yet</div>
                      </div>
                    )
                }
              </div>
            </div>

            {/* Footer — View all */}
            <div className="flex justify-end mt-3">
              <Link to={`/category/${catSlug}`}
                className="text-primary font-bold hover:underline" style={{ fontSize: 15 }}>
                View all {cat} →
              </Link>
            </div>
          </div>
        )
      })}
    </div>
  )
}