import { useState, useEffect } from 'react'
import { useParams, useSearchParams, Link } from 'react-router-dom'
import ProductCard from '../components/ProductCard'
import { useTheme } from '../context/ThemeContext'
import axios from '../lib/axios'

const PAGE_SIZE = 12

export function CategoryPage() {
  const { category, sub } = useParams()
  const [searchParams] = useSearchParams()
  const [products, setProducts] = useState([])
  const [loading, setLoading] = useState(true)
  const [page, setPage] = useState(1)
  const { dark } = useTheme()

  const qParam    = searchParams.get('q') || ''
  const sortParam = searchParams.get('sort') || ''
  const isSearch  = Boolean(qParam)

  // Human-friendly sort labels shown in the header
  const SORT_LABELS = {
    newest:      'NEW PRODUCTS',
    'price-asc': 'LOWEST PRICE',
    'price-desc':'HIGHEST PRICE',
    name:        'A – Z',
    rating:      'TOP RATED',
  }

  // Derive the page title:
  // /search?sort=newest  → "NEW PRODUCTS"
  // /search?q=ryzen      → 'SEARCH: "RYZEN"'
  // /category/cpu        → "CPU"
  // /category/monitor/monitor-42inch → "MONITOR 42INCH"
  const rawSlug = sub || category || ''
  const slugLabel = rawSlug.replace(/-/g, ' ').toUpperCase()

  const label = isSearch
    ? `SEARCH: "${qParam.toUpperCase()}"`
    : (!rawSlug || rawSlug === 'all')
      ? (SORT_LABELS[sortParam] || 'ALL PRODUCTS')
      : slugLabel

  const parentLabel = (category || '').replace(/-/g, ' ').toUpperCase()
  const parentPath  = `/category/${category}`

  const bg      = dark ? '#111827' : '#fff'
  const text    = dark ? '#f9fafb' : '#1f2937'
  const textSub = dark ? '#9ca3af' : '#6b7280'
  const border  = dark ? '#374151' : '#e5e7eb'

  useEffect(() => {
    let cancelled = false
    setLoading(true)
    setPage(1)
    setProducts([])

    const sortParam = searchParams.get('sort') || 'default'
    const catsParam = searchParams.get('cats')

    // Build API params for this category
    const buildParams = () => {
      // Search mode
      if (isSearch) return { search: qParam, per_page: 48, page: 1, sort: sortParam }

      // "all" slug or no category — return everything, just apply sort
      const slug = (sub || category || '').toLowerCase()
      if (!slug || slug === 'all') return { per_page: 48, page: 1, sort: sortParam }

      // Multi-category via ?cats= URL param (from navbar subcategory links)
      if (catsParam) return { cats: catsParam, per_page: 48, page: 1, sort: sortParam }

      // Single category slug
      const catName = slug.replace(/-/g, ' ')
      return { category: catName, per_page: 48, page: 1, sort: sortParam }
    }

    axios.get('/api/products', { params: buildParams() })
      .then(res => {
        if (cancelled) return
        const d = res.data.data ?? res.data ?? []
        // Never fall back to MOCK — show real empty state instead
        setProducts(Array.isArray(d) ? d : [])
      })
      .catch(() => { if (!cancelled) setProducts([]) })
      .finally(() => { if (!cancelled) setLoading(false) })

    return () => { cancelled = true }
  }, [category, sub, searchParams])

  const totalPages = Math.ceil(products.length / PAGE_SIZE)
  const paged      = products.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE)

  return (
    <div className="max-w-[1280px] mx-auto px-4 py-6" style={{ background: bg, minHeight: '60vh' }}>
      {/* Breadcrumb */}
      <div className="flex items-center gap-2 mb-4" style={{ fontSize: 14, color: textSub }}>
        <Link to="/" className="hover:text-primary">HOME</Link>
        <span>›</span>
        {isSearch ? (
          <span className="text-primary font-bold">SEARCH RESULTS</span>
        ) : sub ? (
          <>
            <Link to={parentPath} className="hover:text-primary">{parentLabel}</Link>
            <span>›</span>
            <span className="text-primary font-bold">{label}</span>
          </>
        ) : (
          <span className="text-primary font-bold">{label}</span>
        )}
      </div>

      {/* Header */}
      <div className="flex items-center mb-6">
        <div className="flex-1 h-12 rounded-l" style={{ background: dark ? '#374151' : '#000' }} />
        <div className="bg-primary text-white font-bold px-10 py-3 uppercase"
          style={{ fontFamily: 'HurstBagod, Rajdhani, sans-serif', fontSize: 20, letterSpacing: 2 }}>
          {isSearch
            ? (qParam ? `🔍 ${qParam.toUpperCase()}` : '🔍 SEARCH')
            : (label || 'ALL PRODUCTS')}
        </div>
      </div>

      {loading ? (
        <div className="flex justify-center py-24">
          <div className="w-14 h-14 border-4 border-primary border-t-transparent rounded-full animate-spin" />
        </div>
      ) : (
        <>
          <p className="mb-4" style={{ fontSize: 15, color: textSub }}>
            {isSearch && products.length === 0
              ? `No results found for "${qParam}"`
              : `Showing ${paged.length} of ${products.length} product${products.length !== 1 ? 's' : ''}`}
          </p>

          {isSearch && products.length === 0 && (
            <div className="flex flex-col items-center py-20 gap-4" style={{ color: textSub }}>
              <div style={{ fontSize: 48 }}>🔍</div>
              <p style={{ fontSize: 18 }}>We couldn't find anything matching <strong style={{ color: text }}>"{qParam}"</strong></p>
              <Link to="/" className="text-primary font-bold hover:underline" style={{ fontSize: 15 }}>← Back to home</Link>
            </div>
          )}

          {products.length > 0 && (
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
              {paged.map((p, i) => <ProductCard key={p.id || i} product={p} />)}
            </div>
          )}

          {totalPages > 1 && (
            <div className="flex items-center justify-center gap-2 mt-10">
              <button onClick={() => { setPage(p => Math.max(1, p - 1)); window.scrollTo(0,0) }}
                disabled={page === 1}
                className="px-4 py-2 rounded font-bold hover:border-primary hover:text-primary disabled:opacity-40 transition-colors"
                style={{ border: `1px solid ${border}`, color: text, fontSize: 15 }}>‹ Prev</button>
              {Array.from({ length: totalPages }, (_, i) => i + 1).map(n => (
                <button key={n} onClick={() => { setPage(n); window.scrollTo(0,0) }}
                  className="w-10 h-10 rounded font-bold transition-colors"
                  style={{
                    fontSize: 15,
                    background: page === n ? '#F97316' : 'transparent',
                    color: page === n ? '#fff' : text,
                    border: `1px solid ${page === n ? '#F97316' : border}`,
                  }}>{n}</button>
              ))}
              <button onClick={() => { setPage(p => Math.min(totalPages, p + 1)); window.scrollTo(0,0) }}
                disabled={page === totalPages}
                className="px-4 py-2 rounded font-bold hover:border-primary hover:text-primary disabled:opacity-40 transition-colors"
                style={{ border: `1px solid ${border}`, color: text, fontSize: 15 }}>Next ›</button>
            </div>
          )}
        </>
      )}
    </div>
  )
}

export default CategoryPage
