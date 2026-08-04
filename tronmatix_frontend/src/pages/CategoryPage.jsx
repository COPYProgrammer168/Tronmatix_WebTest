import { useState, useEffect, useMemo } from 'react'
import { useParams, useSearchParams, Link } from 'react-router-dom'
import ProductCard from '../components/ProductCard'
import { useTheme } from '../context/ThemeContext'
import { useLang } from '../context/LanguageContext'
import { useCategories } from '../context/CategoryContext'
import axios from '../lib/axios'

export function CategoryPage() {
  const { category, main, sub } = useParams()
  const [searchParams] = useSearchParams()
  const [products, setProducts] = useState([])
  const [loading, setLoading] = useState(true)
  const { dark } = useTheme()
  const { t, isKhmer } = useLang()
  const { categories: categoryTree } = useCategories()

  // Resolve a top-level category slug to the category/brand names that should be
  // matched. Includes main-category names (CPU, RAM, ...) so products stored under
  // those show up, PLUS sub-category names (INTEL 12TH, ...) and brand names
  // (DX RACER, SECRETLAB, ...) to catch leaf-labeled products too.
  // e.g. "pc-part" → CPU,RAM,MAINBOARD,...,"accessory" → KEYBOARD,MOUSE,...,
  // "table-chair" → DX RACER,SECRETLAB,TTR RACING,...
  const topLevelCats = useMemo(() => {
    const map = {}
    ;(categoryTree || []).forEach(cat => {
      const subs = []
      ;(cat.main_categories || []).forEach(mc => {
        // Always include the main-category name itself (CPU, RAM, ...)
        subs.push(mc.name)
        const subCates = mc.sub_categories || []
        subCates.forEach(sc => {
          // Include sub-category name
          if (sc.name) subs.push(sc.name)
          // Include brand names living under the sub-category
          const brands = (sc.brands || []).map(b => b.name).filter(b => b && b !== 'TBD')
          if (brands.length) subs.push(...brands)
        })
      })
      map[cat.slug] = [...new Set(subs.filter(Boolean))]
    })
    return map
  }, [categoryTree])

  // Resolve the exact product-category name for a deep URL.
  // e.g. /category/pc-build/pc-build-under-1k → the sub-category "PC BUILD UNDER 1K",
  // which matches the product's `category` column exactly.
  const deepCategoryName = useMemo(() => {
    const rawSlug = sub || main || ''
    if (!rawSlug) return null

    for (const cat of categoryTree || []) {
      for (const mc of cat.main_categories || []) {
        if (mc.slug === rawSlug) return mc.name
        for (const sc of mc.sub_categories || []) {
          if (sc.slug === rawSlug) return sc.name
        }
      }
    }
    return null
  }, [categoryTree, main, sub])

  const qParam = (searchParams.get('q') || '').toLowerCase()
  const sortParam = searchParams.get('sort') || ''
  const isSearch = Boolean(qParam)

  // Sort labels stay English for display (product names are English from DB)
  const SORT_LABELS = {
    newest: isKhmer ? 'ផលិតផលថ្មី' : 'NEW PRODUCTS',
    'price-asc': isKhmer ? 'តម្លៃទាបបំផុត' : 'LOWEST PRICE',
    'price-desc': isKhmer ? 'តម្លៃខ្ពស់បំផុត' : 'HIGHEST PRICE',
    name: isKhmer ? 'A – Z' : 'A – Z',
    rating: isKhmer ? 'ពិន្ទុខ្ពស់' : 'TOP RATED',
  }

  const rawSlug = sub || main || category || ''
  const slugLabel = rawSlug.replace(/-/g, ' ').toUpperCase()
  const brandParam = searchParams.get('brand')

  const label = isSearch
    ? (isKhmer ? `ស្វែងរក: "${qParam.toUpperCase()}"` : `SEARCH: "${qParam.toUpperCase()}"`)
    : (!rawSlug || rawSlug === 'all')
      ? (SORT_LABELS[sortParam] || t('common.allProducts'))
      : (brandParam ? `${slugLabel} - ${brandParam.toUpperCase()}` : slugLabel)

  const parentLabel = (category || '').replace(/-/g, ' ').toUpperCase()
  const parentPath = `/category/${category}`
  const mainLabel = (main || '').replace(/-/g, ' ').toUpperCase()
  const mainPath = main ? `/category/${category}/${main}` : null

  const bg = dark ? '#111827' : '#fff'
  const text = dark ? '#f9fafb' : '#1f2937'
  const textSub = dark ? '#9ca3af' : '#6b7280'

  useEffect(() => {
    let cancelled = false
    setLoading(true)
    setProducts([])

    const sortVal = searchParams.get('sort') || 'default'
    const catsParam = searchParams.get('cats')

    const buildParams = () => {
      const brand = searchParams.get('brand');
      if (isSearch) return { search: qParam, per_page: 999, page: 1, sort: sortVal }
      if (catsParam) return { cats: catsParam, per_page: 999, page: 1, sort: sortVal, brand: brand }
      if (!category || category === 'all') return { per_page: 999, page: 1, sort: sortVal, brand: brand }

      // Top-level category only: send its sub-category names via cats.
      const isTopLevel = !main && !sub
      const catSlug = category

      if (isTopLevel) {
        const subs = topLevelCats[catSlug]
        if (subs && subs.length) {
          return { cats: subs.join(','), per_page: 999, page: 1, sort: sortVal, brand: brand }
        }
        const catLabel = category.replace(/-/g, ' ')
        return { category: catLabel, per_page: 999, page: 1, sort: sortVal, brand: brand }
      }

      // Deep level (main/sub): filter by the exact product-category name
      // resolved from the tree (e.g. "PC BUILD UNDER 1K"). This matches the
      // product's `category` column so only the right products show.
      if (deepCategoryName) {
        return { category: deepCategoryName, per_page: 999, page: 1, sort: sortVal, brand: brand }
      }

      return { per_page: 999, page: 1, sort: sortVal, brand: brand }
    }

    axios.get('/api/products', { params: buildParams() })
      .then(res => {
        if (cancelled) return
        const d = res.data.data ?? res.data ?? []
        let items = Array.isArray(d) ? d : []

        if (items.length === 0 && !isSearch) {
          // Deep level with an exact-name miss: try a keyword search on the
          // sub-category name instead of dumping every product.
          if (deepCategoryName) {
            axios.get('/api/products', {
              params: { search: deepCategoryName, per_page: 999, page: 1, sort: sortVal, brand: brand },
            })
              .then(res2 => {
                if (cancelled) return
                const d2 = res2.data.data ?? res2.data ?? []
                setProducts(Array.isArray(d2) ? d2 : [])
              })
              .catch(() => { if (!cancelled) setProducts([]) })
              .finally(() => { if (!cancelled) setLoading(false) })
            return
          }

          const fallbackParams = { per_page: 999, page: 1, sort: sortVal }
          if (brand) fallbackParams.brand = brand

          axios.get('/api/products', { params: fallbackParams })
            .then(res2 => {
              if (cancelled) return
              const d2 = res2.data.data ?? res2.data ?? []
              let fallbackItems = Array.isArray(d2) ? d2 : []
              const brandFilter = searchParams.get('brand')
              if (brandFilter && fallbackItems.length > 0) {
                const bpLower = brandFilter.toLowerCase()
                fallbackItems = fallbackItems.filter(p => {
                  const pbp = (p.brand_pc_part || '').toLowerCase().trim()
                  if (!pbp) return false
                  return pbp.includes(bpLower) || bpLower.includes(pbp)
                })
              }
              setProducts(fallbackItems)
            })
            .catch(() => { if (!cancelled) setProducts([]) })
            .finally(() => { if (!cancelled) setLoading(false) })
          return
        }

        if (isSearch && qParam && items.length > 0) {
          items = items.filter(p =>
            (p.name || '').toLowerCase().includes(qParam) ||
            (p.category || '').toLowerCase().includes(qParam) ||
            (p.brand || '').toLowerCase().includes(qParam) ||
            (p.description || '').toLowerCase().includes(qParam)
          )
        }
        // Filter by brand_pc_part when browsing PC PART sub-categories
        const brandFilter = searchParams.get('brand')
        if (brandFilter && !isSearch && items.length > 0) {
          const bpLower = brandFilter.toLowerCase()
          items = items.filter(p => {
            const pbp = (p.brand_pc_part || '').toLowerCase().trim()
            if (!pbp) return false   // ← products with no brand_pc_part are excluded
            return pbp.includes(bpLower) || bpLower.includes(pbp)
          })
        }
        setProducts(items)
      })
      .catch(() => { if (!cancelled) setProducts([]) })
      .finally(() => { if (!cancelled) setLoading(false) })

    return () => { cancelled = true }
  }, [category, main, sub, searchParams, topLevelCats, deepCategoryName])

  return (
    <div
      className="max-w-[1280px] mx-auto px-4 py-6"
      style={{ background: bg, minHeight: "60vh" }}
    >
      {/* Breadcrumb */}
      <div
        className="flex items-center gap-2 mb-4"
        style={{ fontSize: 14, color: textSub }}
      >
        <Link to="/" className="hover:text-primary">
          {t("nav.home")}
        </Link>
        <span>›</span>
        {isSearch ? (
          <span className="text-primary font-bold">
            {t("common.searchResults")}
          </span>
        ) : sub ? (
          <>
            <Link to={parentPath} className="hover:text-primary">
              {parentLabel}
            </Link>
            <span>›</span>
            {mainPath ? (
              <>
                <Link to={mainPath} className="hover:text-primary">
                  {mainLabel}
                </Link>
                <span>›</span>
              </>
            ) : null}
            <span className="text-primary font-bold">{label}</span>
          </>
        ) : main ? (
          <>
            <Link to={parentPath} className="hover:text-primary">
              {parentLabel}
            </Link>
            <span>›</span>
            <span className="text-primary font-bold">{label}</span>
          </>
        ) : (
          <span className="text-primary font-bold">{label}</span>
        )}
      </div>

      {/* Header banner - ribbon flag style */}
      <div className="relative w-full mb-6" style={{ height: 48 }}>
        <div
          className="h-12 rounded-l"
          style={{
            width: "calc(100% - 5px)",
            background: dark ? "#374151" : "#2d2d2e",
            clipPath: "polygon(0 0, 100% 0, calc(100% - 10px) 100%, 0 100%)",
          }}
        />

        <div className="absolute right-0 h-12" style={{ top: -8 }}>
          <div
            className="relative h-full flex items-center bg-primary text-white font-bold px-10 uppercase"
            style={{
              fontFamily: isKhmer
                ? "Kh-Koulen, sans-serif"
                : "HurstBagod, Rajdhani, sans-serif",
              fontSize: 22,
              letterSpacing: isKhmer ? 0 : 2,
              clipPath:
                "polygon(10px 0%, 100% 0%, calc(100% - 10px) 100%, 0% 100%)",
            }}
          >
            {isSearch
              ? qParam
                ? `🔍 ${qParam.toUpperCase()}`
                : `🔍 ${t("common.search")}`
              : label || t("common.allProducts")}
          </div>

          {/* Folded corner - now at top-left of the ribbon */}
          {/* Folded corner - tucked at top-left of the ribbon */}
          <div
            className="absolute"
            style={{
              left: 0,
              bottom: "calc(100% - 8px)",
              width: 0,
              height: 0,
              borderLeft: "10px solid transparent",
              borderBottom: `8px solid ${dark ? "#1f2937" : "#7a5a00"}`,
            }}
          />
        </div>
      </div>

      {loading ? (
        <div className="flex justify-center py-24">
          <div className="w-14 h-14 border-4 border-primary border-t-transparent rounded-full animate-spin" />
        </div>
      ) : (
        <>
          <p
            className="mb-4"
            style={{
              fontFamily: isKhmer
                ? "Kdam Thmor Pro, sans-serif"
                : "Rajdhani, sans-serif",
              fontSize: 15,
              color: textSub,
            }}
          >
            {isSearch && products.length === 0
              ? `${t("common.searchNo")} "${qParam}"`
              : products.length === 0
                ? t("common.noProductsInCategory")
                : products.length === 1
                  ? t("common.showingProducts", { count: products.length })
                  : t("common.showingProductsPlural", { count: products.length })}
          </p>

          {(isSearch || !loading) && products.length === 0 && (
            <div
              className="flex flex-col items-center py-20 gap-4"
              style={{ color: textSub }}
            >
              {isSearch ? (
                <div style={{ fontSize: 48 }}>🔍</div>
              ) : (
                <svg
                  width="72"
                  height="72"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke={dark ? "#4b5563" : "#d1d5db"}
                  strokeWidth="1.2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  style={{ opacity: 0.7 }}
                >
                  {/* Empty product box */}
                  <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                  <path d="M3.27 6.96 12 12.01l8.73-5.05" />
                  <path d="M12 22.08V12" />
                  {/* "empty" minus inside the box */}
                  <line x1="9" y1="15" x2="15" y2="15" opacity="0" />
                </svg>
              )}
              <p
                style={{
                  fontFamily: isKhmer
                    ? "Kdam Thmor Pro, sans-serif"
                    : "Rajdhani, sans-serif",
                  fontSize: 18,
                  fontWeight: 700,
                  textAlign: "center",
                  color: text,
                }}
              >
                {isSearch ? (
                  <>
                    {t("common.searchNo")}{" "}
                    <strong style={{ color: text }}>"{qParam}"</strong>
                  </>
                ) : (
                  t("common.noProductsInCategory")
                )}
              </p>
              {brandParam && (
                <p
                  style={{
                    fontFamily: isKhmer
                      ? "Kdam Thmor Pro, sans-serif"
                      : "Rajdhani, sans-serif",
                    fontSize: 14,
                  }}
                >
                  {isKhmer ? `ម៉ាក: ${brandParam}` : `Brand: ${brandParam}`}
                </p>
              )}
              <Link
                to="/"
                className="text-primary font-bold hover:underline"
                style={{ fontSize: 15 }}
              >
                {t("common.backToHome")}
              </Link>
            </div>
          )}

          {products.length > 0 && (
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
              {products.map((p, i) => (
                <ProductCard key={p.id || i} product={p} />
              ))}
            </div>
          )}
        </>
      )}
    </div>
  );
}

export default CategoryPage
