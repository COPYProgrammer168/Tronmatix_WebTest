import { useState, useEffect } from 'react'
import { useParams, useSearchParams, Link } from 'react-router-dom'
import ProductCard from '../components/ProductCard'
import { useTheme } from '../context/ThemeContext'
import axios from '../lib/axios'

export function CategoryPage() {
  const { category, sub } = useParams()
  const [searchParams] = useSearchParams()
  const [products, setProducts] = useState([])
  const [loading, setLoading] = useState(true)
  const { dark } = useTheme()

  const qParam    = (searchParams.get('q') || '').toLowerCase()
  const sortParam = searchParams.get('sort') || ''
  const isSearch  = Boolean(qParam)

  const SORT_LABELS = {
    newest:      'NEW PRODUCTS',
    'price-asc': 'LOWEST PRICE',
    'price-desc':'HIGHEST PRICE',
    name:        'A – Z',
    rating:      'TOP RATED',
  }

  const rawSlug   = sub || category || ''
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

  useEffect(() => {
    let cancelled = false
    setLoading(true)
    setProducts([])

    const sortVal  = searchParams.get('sort') || 'default'
    const catsParam = searchParams.get('cats')

    const buildParams = () => {
      if (isSearch) return { search: qParam, per_page: 999, page: 1, sort: sortVal }
      const slug = (sub || category || '').toLowerCase()
      if (!slug || slug === 'all') return { per_page: 999, page: 1, sort: sortVal }
      if (catsParam) return { cats: catsParam, per_page: 999, page: 1, sort: sortVal }
      const catName = slug.replace(/-/g, ' ')
      return { category: catName, per_page: 999, page: 1, sort: sortVal }
    }

    axios.get('/api/products', { params: buildParams() })
      .then(res => {
        if (cancelled) return
        const d = res.data.data ?? res.data ?? []
        let items = Array.isArray(d) ? d : []
        if (isSearch && qParam && items.length > 0) {
          items = items.filter(p =>
            (p.name || '').toLowerCase().includes(qParam) ||
            (p.category || '').toLowerCase().includes(qParam) ||
            (p.brand || '').toLowerCase().includes(qParam) ||
            (p.description || '').toLowerCase().includes(qParam)
          )
        }
        setProducts(items)
      })
      .catch(() => { if (!cancelled) setProducts([]) })
      .finally(() => { if (!cancelled) setLoading(false) })

    return () => { cancelled = true }
  }, [category, sub, searchParams])

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
              : `Showing ${products.length} product${products.length !== 1 ? 's' : ''}`}
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
              {products.map((p, i) => <ProductCard key={p.id || i} product={p} />)}
            </div>
          )}
        </>
      )}
    </div>
  )
}

export default CategoryPage
