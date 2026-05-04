import { useState, useEffect, useRef } from 'react'
import { Link } from 'react-router-dom'
import ProductCard from '../components/ProductCard'
import { useTheme } from '../context/ThemeContext'
import axios from '../lib/axios'
import { useLang } from '../context/LanguageContext'

const LARAVEL_URL = (import.meta.env.VITE_API_URL || '').replace(/\/$/, '')

const FALLBACK_BANNERS = [
  { id: 1, title: 'WHITE SET\nHIGH END PC BUILD', subtitle: 'AMD RYZEN 9950X3D / RTX5080', badge: 'NEW ARRIVAL', bg_color: '#f5f0e8', text_color: '#c8860a', image: null },
  { id: 2, title: 'PC BUILD BUDGET 3K\nFOR GAMING',       subtitle: 'Best price guaranteed',       badge: 'HOT DEAL',    bg_color: '#111111',  text_color: '#F97316', image: null },
]

const categories = ['CPU', 'RAM', 'MAINBOARD', 'MONITOR', 'COOLING', 'M2', 'VGA', 'POWER SUPPLY']

// Sub-categories that map to each header category.
// For MONITOR we send multiple `category[]` values so the backend
// can use whereIn — single `cats` string param is non-standard.
const CAT_SUBS = {
  'MONITOR':       ['MONITOR 25INCH','MONITOR 27INCH','MONITOR 32INCH','MONITOR 34INCH','MONITOR 39INCH','MONITOR 42INCH','MONITOR 48INCH','MONITOR 49INCH'],
  'CPU':           ['CPU'],
  'RAM':           ['RAM'],
  'MAINBOARD':     ['MAINBOARD'],
  'COOLING':       ['COOLING'],
  'M2':            ['M2'],
  'VGA':           ['VGA'],
  'POWER SUPPLY':  ['POWER SUPPLY'],
}

export function resolveImage(path) {
  if (!path || typeof path !== 'string') return null
  const t = path.trim()
  if (!t) return null
  if (t.startsWith('http://') || t.startsWith('https://')) return t
  return LARAVEL_URL + (t.startsWith('/') ? t : '/' + t)
}

export default function HomePage() {
  const [slide, setSlide]           = useState(0)
  const [banners, setBanners]       = useState(FALLBACK_BANNERS)
  const [products, setProducts]     = useState({})   // { CPU: { items, total, page, error } }
  const [pageLoading, setPageLoading] = useState(true)
  const [newProducts, setNewProducts] = useState([])
  const [catPage, setCatPage]         = useState({})
  const newProdRef = useRef(null)
  const catRefs    = useRef({})
  const { dark }   = useTheme()
  const { t, isKhmer } = useLang()

  // Font stacks that auto-switch based on language
  const headingFont = isKhmer
    ? 'Kh_Jrung_Thom, Khmer OS, sans-serif'
    : 'HurstBagod, Rajdhani, sans-serif'
  const bodyFont = isKhmer
    ? 'KantumruyPro, Khmer OS, sans-serif'
    : 'Rajdhani, sans-serif'

  const bg      = dark ? '#111827' : '#fff'
  const text    = dark ? '#f9fafb' : '#1f2937'
  const headerL = dark ? '#1f2937' : '#000'
  const navBtn  = dark ? '#374151' : '#fff'
  const navBrd  = dark ? '#4b5563' : '#d1d5db'

  // ── Banners ────────────────────────────────────────────────────────────────
  useEffect(() => {
    axios.get('/api/banners')
      .then(res => {
        const data   = Array.isArray(res.data) ? res.data : (res.data?.data ?? [])
        const active = data.filter(b => b.active !== false)
        if (active.length > 0) setBanners(active)
      })
      .catch(err => {
        console.warn('[HomePage] banners fetch failed:', err?.response?.status, err?.message)
        // Keep FALLBACK_BANNERS — no user-visible error needed
      })
  }, [])

  // Auto-advance banner
  useEffect(() => {
    if (banners.length <= 1) return
    const t = setInterval(() => setSlide(s => (s + 1) % banners.length), 5000)
    return () => clearInterval(t)
  }, [banners.length])

  useEffect(() => {
    setSlide(s => Math.min(s, Math.max(banners.length - 1, 0)))
  }, [banners.length])

  // ── New Products ───────────────────────────────────────────────────────────
  useEffect(() => {
    axios.get('/api/products', { params: { sort: 'newest', per_page: 12, page: 1 } })
      .then(res => {
        const raw   = res.data
        const items = Array.isArray(raw) ? raw : (raw?.data ?? [])
        setNewProducts(items)
      })
      .catch(err => {
        console.warn('[HomePage] new products fetch failed:', err?.response?.status, err?.message)
      })
  }, [])

  // ── Category fetch ─────────────────────────────────────────────────────────
  const fetchCatPage = async (cat, page) => {
    const subs = CAT_SUBS[cat] ?? [cat]

    // Build URLSearchParams so array values are properly serialized as
    // repeated keys: category[]=X&category[]=Y — Laravel reads these correctly.
    const qs = new URLSearchParams()
    if (subs.length > 1) {
      subs.forEach(s => qs.append('category[]', s))
    } else {
      qs.append('category', subs[0])
    }
    qs.append('per_page', 10)
    qs.append('page', page)

    try {
      const res   = await axios.get(`/api/products?${qs.toString()}`)
      const raw   = res.data
      const items = Array.isArray(raw) ? raw : (raw?.data ?? [])
      const total = raw?.total ?? items.length

      setProducts(prev => ({ ...prev, [cat]: { items, total, page, error: false } }))
    } catch (err) {
      const status = err?.response?.status
      console.error(`[HomePage] category "${cat}" fetch failed:`, status, err?.message)
      setProducts(prev => ({ ...prev, [cat]: { items: [], total: 0, page, error: true } }))
    }
  }

  useEffect(() => {
    const init = async () => {
      const pages = {}
      categories.forEach(cat => { pages[cat] = 1 })
      setCatPage(pages)
      // Use allSettled so one failing category (e.g. 500 on MONITOR)
      // never blocks the rest from rendering.
      await Promise.allSettled(categories.map(cat => fetchCatPage(cat, 1)))
      setPageLoading(false)
    }
    init()
  }, []) // eslint-disable-line

  // ── Derived banner values ──────────────────────────────────────────────────
  const b          = banners[slide] ?? FALLBACK_BANNERS[0]
  const bgColor    = b.bg_color   || '#111'
  const txtColor   = b.text_color || '#fff'
  const imgUrl     = resolveImage(b.image)
  const hasVideo   = b.has_video || !!b.video
  const videoType  = b.video_type
  const videoSrc   = b.video
  const hasMedia   = imgUrl || hasVideo
  const detailLink = b.link
    || (b.category ? `/category/${b.category.toLowerCase()}` : null)
    || `/category/search?q=${encodeURIComponent((b.title || '').replace('\n', ' ').split(' ').slice(0, 3).join(' '))}`

  return (
    <div style={{ background: bg }}>
      <div className="max-w-[1280px] mx-auto px-4 pt-6 pb-2">

        {/* ── BANNER SLIDER ──────────────────────────────────────────────── */}
        <style>{`
          /* Hide browser native video controls & media overlay on banner */
          .banner-video::-webkit-media-controls { display: none !important; }
          .banner-video::-webkit-media-controls-enclosure { display: none !important; }
          .banner-video::-webkit-media-controls-panel { display: none !important; }
          .banner-video::--webkit-media-controls-play-button { display: none !important; }
          .banner-video::-webkit-media-controls-timeline { display: none !important; }
          .banner-video { -webkit-appearance: none; }
          /* Hide media overlay icons on mobile Chrome/Safari */
          .banner-video::-webkit-media-controls-start-playback-button { display: none !important; }
          .banner-video::-webkit-media-controls-overlay-play-button { display: none !important; }
        `}</style>
        <div className="max-w-[1280px] mx-auto px-4 mb-8">
          <div className="relative overflow-hidden rounded-xl"
            style={{ minHeight: 444, background: hasMedia ? '#000' : bgColor, transition: 'background 4s' }}>

            {hasVideo && videoType === 'upload' && videoSrc && (
              <video key={videoSrc} className="absolute inset-0 w-full h-full object-cover banner-video"
                style={{ opacity: 0.4, pointerEvents: 'none' }}
                src={videoSrc} autoPlay muted loop playsInline
                disablePictureInPicture disableRemotePlayback
                onError={e => { e.currentTarget.style.display = 'none' }} />
            )}

            {hasVideo && ['youtube','vimeo','facebook'].includes(videoType) && videoSrc && (
              <iframe key={videoSrc} src={videoSrc}
                className="absolute inset-0 w-full h-full"
                style={{ opacity: 0.85, pointerEvents: 'none' }}
                frameBorder="0" allow="autoplay; encrypted-media" allowFullScreen />
            )}

            {imgUrl && (
              <img src={imgUrl} alt={b.title}
                className="absolute inset-0 w-full h-full object-cover"
                style={{ opacity: hasVideo ? 0.25 : 0.7 }}
                onError={e => { e.currentTarget.style.display = 'none' }} />
            )}

            <div className="absolute inset-0"
              style={{ background: 'linear-gradient(to right, rgba(0,0,0,1) 0%, rgba(0,0,0,0.45) 55%, rgba(0,0,0,0.2) 100%)' }} />

            <div className="relative flex items-center min-h-[260px] px-10 md:px-16 py-10" style={{ zIndex: 2 }}>
              <div style={{ maxWidth: 480 }}>
                {b.badge && (
                  <span className="inline-block bg-primary text-white font-bold px-4 py-1 rounded-full mb-4"
                    style={{ fontSize: 12, letterSpacing: 2 }}>
                    {b.badge}
                  </span>
                )}
                <div className="font-black whitespace-pre-line leading-tight mb-3"
                  style={{ fontFamily: 'HurstBagod, Kh_Jrung_Thom, sans-serif', fontSize: 'clamp(24px,4vw,42px)', color: hasMedia ? '#fff' : txtColor, lineHeight: isKhmer ? 1.7 : 1.2, letterSpacing: isKhmer ? 0 : undefined }}>
                  {b.title}
                </div>
                {b.subtitle && (
                  <div className="font-semibold mb-6"
                    style={{ fontFamily: 'Rajdhani, KantumruyPro, sans-serif', fontSize: 'clamp(14px,2vw,20px)', color: hasMedia ? 'rgba(255,255,255,0.85)' : txtColor, opacity: 0.85, lineHeight: isKhmer ? 1.8 : undefined, letterSpacing: isKhmer ? 0 : undefined }}>
                    {b.subtitle}
                  </div>
                )}
                <Link to={detailLink}
                  className="inline-flex items-center gap-2 font-bold px-6 py-3 rounded-lg transition-all hover:scale-105 hover:opacity-90"
                  style={{ fontFamily: 'Rajdhani, KantumruyPro, sans-serif', fontSize: 15, letterSpacing: isKhmer ? 0 : 1, background: hasMedia ? '#fff' : txtColor, color: hasMedia ? '#111' : bgColor, textDecoration: 'none' }}>
                  {isKhmer ? t('home.viewProduct') : 'VIEW PRODUCT'}
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth={2.5} viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                  </svg>
                </Link>
              </div>
            </div>

            {banners.length > 1 && (<>
              <button onClick={() => setSlide(s => (s - 1 + banners.length) % banners.length)}
                className="absolute left-3 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-primary text-white w-10 h-10 flex items-center justify-center rounded-full transition-colors text-xl"
                style={{ zIndex: 3 }}>‹</button>
              <button onClick={() => setSlide(s => (s + 1) % banners.length)}
                className="absolute right-3 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-primary text-white w-10 h-10 flex items-center justify-center rounded-full transition-colors text-xl"
                style={{ zIndex: 3 }}>›</button>
            </>)}

            {banners.length > 1 && (
              <div className="absolute bottom-4 left-10 md:left-16 flex gap-2" style={{ zIndex: 3 }}>
                {banners.map((_, i) => (
                  <button key={i} onClick={() => setSlide(i)}
                    className={`banner-dot h-2 rounded-full transition-all ${i === slide ? 'bg-primary w-6' : 'bg-white/50 w-2'}`} />
                ))}
              </div>
            )}
          </div>
        </div>

        {/* NEW ARRIVAL heading */}
        <div className="flex items-center gap-1 justify-center">
          <span className="text-primary font-black tracking-widest" style={{ fontFamily: headingFont, fontSize: 32, letterSpacing: isKhmer ? 0 : undefined }}>
            {isKhmer ? t('home.newArrival') : 'NEW'}
          </span>
          {!isKhmer && (
            <span className="font-black tracking-widest" style={{ fontFamily: headingFont, fontSize: 32, color: text }}>ARRIVAL</span>
          )}
        </div>
      </div>

      {/* ── NEW PRODUCTS carousel ────────────────────────────────────────────── */}
      {newProducts.length > 0 && (
        <div className="max-w-[1280px] mx-auto px-4 mb-10">
          <div className="flex items-center justify-between mb-5">
            <div className="flex items-center gap-3">
              <div className="w-1 h-8 bg-primary rounded-full" />
              <span className="font-black tracking-widest"
                style={{ fontFamily: headingFont, fontSize: 20, color: text, letterSpacing: isKhmer ? 0 : undefined }}>
                {isKhmer ? t('home.newProducts') : 'NEW PRODUCTS'}
              </span>
              <span className="inline-flex items-center justify-center font-bold px-3 rounded-full"
                style={{ fontFamily: bodyFont, background: 'rgba(249,115,22,0.12)', color: '#F97316', border: '1px solid rgba(249,115,22,0.3)', fontSize: 12, height: 30, lineHeight: 1 }}>
                {isKhmer ? 'ទើបបន្ថែមថ្មីៗ':'just Added'}
              </span>
            </div>
            <div className="flex items-center gap-3">
              <div className="flex gap-2">
                {['‹','›'].map((a, i) => (
                  <button key={i}
                    onClick={() => newProdRef.current?.scrollBy({ left: i === 0 ? -300 : 300, behavior: 'smooth' })}
                    className="w-8 h-8 flex items-center justify-center font-bold hover:border-primary hover:text-primary transition-colors rounded"
                    style={{ border: `1px solid ${navBrd}`, color: text, background: navBtn, fontSize: 16 }}>
                    {a}
                  </button>
                ))}
              </div>
              <Link to="/search?sort=newest" className="text-primary font-bold hover:underline" style={{ fontFamily:bodyFont, fontSize: 15 }}>
                {isKhmer ? 'មើលផលិតផលថ្មី':'​View All New Product'} →
              </Link>
            </div>
          </div>
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

      {/* ── CATEGORY ROWS ────────────────────────────────────────────────────── */}
      {categories.map(cat => {
        const catData  = products[cat]
        const catItems = catData?.items ?? []
        const hasError = catData?.error === true
        const isLoading = pageLoading || catData === undefined
        const catSlug  = cat.toLowerCase().replace(/ /g, '-')
        const scrollId = 'cat-' + cat.replace(/ /g, '-')

        return (
          <div key={cat} className="max-w-[1280px] mx-auto px-4 mb-10">

            {/* Row header */}
            <div className="flex items-center mb-4">
              <div className="flex-1 h-12 rounded-l" style={{ background: headerL }} />
              <div className="hidden lg:flex gap-1 mr-2">
                {['‹','›'].map((a, i) => (
                  <button key={i}
                    onClick={() => catRefs.current[cat]?.scrollBy({ left: i === 0 ? -500 : 500, behavior: 'smooth' })}
                    className="w-8 h-8 flex items-center justify-center font-bold rounded transition-colors"
                    style={{ border: `1px solid ${navBrd}`, color: text, background: navBtn, fontSize: 16 }}>
                    {a}
                  </button>
                ))}
              </div>
              <Link to={`/category/${catSlug}`}
                className="bg-primary text-white font-bold px-10 py-3 hover:bg-orange-600 transition-colors"
                style={{ fontFamily: headingFont, fontSize: 18, letterSpacing: isKhmer ? 0 : 2, clipPath: 'polygon(10px 0%,100% 0%,calc(100% - 10px) 100%,0% 100%)' }}>
                {cat}
              </Link>
            </div>

            <style>{`
              .${scrollId}::-webkit-scrollbar { height: 4px; }
              .${scrollId}::-webkit-scrollbar-track { background: rgba(249,115,22,0.10); border-radius: 2px; }
              .${scrollId}::-webkit-scrollbar-thumb { background: #F97316; border-radius: 2px; }
            `}</style>

            {/* Error state */}
            {hasError && (
              <div className="py-6 text-center" style={{ color: '#ef4444', fontSize: 13 }}>
                ⚠️ Failed to load {cat} products.{' '}
                <button className="underline" onClick={() => fetchCatPage(cat, 1)}>Retry</button>
              </div>
            )}

            {/* Empty state */}
            {!isLoading && !hasError && catItems.length === 0 && (
              <div className="py-8 text-center" style={{ color: dark ? '#6b7280' : '#9ca3af' }}>
                <div style={{ fontSize: 28, marginBottom: 4 }}>📦</div>
                <div style={{ fontSize: 13, fontWeight: 600 }}>No {cat} products yet</div>
              </div>
            )}

            {/* Product rows */}
            {(isLoading || catItems.length > 0) && (
              <>
                {/* Desktop: single-row horizontal scroll */}
                <div
                  ref={el => { catRefs.current[cat] = el }}
                  className={`hidden lg:flex gap-4 overflow-x-auto pb-3 ${scrollId}`}
                  style={{ scrollbarWidth: 'thin', scrollbarColor: '#F97316 rgba(249,115,22,0.10)' }}>
                  {isLoading
                    ? Array(6).fill(null).map((_, i) => (
                        <div key={i} className="rounded-xl animate-pulse flex-shrink-0"
                          style={{ width: 210, height: 300, background: dark ? '#1f2937' : '#f3f4f6' }} />
                      ))
                    : catItems.map((p, i) => (
                        <div key={p.id || i} style={{ minWidth: 210, maxWidth: 210, flexShrink: 0 }}>
                          <ProductCard product={p} />
                        </div>
                      ))
                  }
                </div>

                {/* Mobile: 2-row grid horizontal scroll */}
                <div
                  className={`lg:hidden overflow-x-auto pb-2 ${scrollId}`}
                  style={{ scrollbarWidth: 'thin', scrollbarColor: '#F97316 rgba(249,115,22,0.10)', WebkitOverflowScrolling: 'touch' }}>
                  <div style={{
                    display: 'grid',
                    gridTemplateRows: 'repeat(2, auto)',
                    gridAutoFlow: 'column',
                    gridAutoColumns: '200px',
                    gap: '8px',
                    width: 'max-content',
                  }}>
                    {isLoading
                      ? Array(8).fill(null).map((_, i) => (
                          <div key={i} className="rounded-xl animate-pulse"
                            style={{ width: 200, height: 220, background: dark ? '#1f2937' : '#f3f4f6' }} />
                        ))
                      : catItems.map((p, i) => (
                          <div key={p.id || i} style={{ width: 200 }}>
                            <ProductCard product={p} />
                          </div>
                        ))
                    }
                  </div>
                </div>
              </>
            )}

            <div className="flex justify-end mt-3">
              <Link to={`/category/${catSlug}`} className="text-primary font-bold hover:underline" style={{ fontFamily: bodyFont, fontSize: 15 }}>
                {isKhmer ? 'មើលទាំងអស់នៃ':'View All'} {cat} →
              </Link>
            </div>
          </div>
        )
      })}
    </div>
  )
}