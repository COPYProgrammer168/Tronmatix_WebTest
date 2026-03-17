// src/context/DiscountContext.jsx
import { createContext, useContext, useState, useCallback, useEffect } from 'react'
import axios from '../lib/axios'

const DiscountContext = createContext(null)

export function DiscountProvider({ children }) {
  const [discount,        setDiscount]        = useState(null)   // user-applied code
  const [publicDiscounts, setPublicDiscounts] = useState([])     // admin-set sitewide deals
  const [loading,  setLoading]  = useState(false)
  const [error,    setError]    = useState(null)
  const [success,  setSuccess]  = useState(null)

  // ── Fetch active public discounts on mount ───────────────────────────────
  // These are discounts the admin created that are active — shown as badges
  // automatically without the user needing to enter a code.
  useEffect(() => {
    let cancelled = false
    const load = (attempt = 1) => {
      axios.get('/api/discounts/public')
        .then(res => {
          if (cancelled) return
          // Handle both { data: [...] } and plain [...] response shapes
          const raw = res.data?.data ?? res.data
          const data = Array.isArray(raw) ? raw : []
          setPublicDiscounts(data)
        })
        .catch(err => {
          if (cancelled) return
          console.warn('[DiscountContext] /api/discounts/public failed:', err?.response?.status, err?.message)
          // Retry once after 3 s (handles cold-start / race on page load)
          if (attempt < 2) setTimeout(() => load(attempt + 1), 3000)
        })
    }
    load()
    return () => { cancelled = true }
  }, [])

  const applyDiscount = useCallback(async (code, subtotal) => {
    if (!code?.trim()) return null
    setLoading(true); setError(null); setSuccess(null)
    try {
      const res = await axios.post('/api/apply-discount', {
        code: code.trim().toUpperCase(), subtotal,
      })
      setDiscount(res.data)
      setSuccess(res.data.message || 'Discount applied!')
      return res.data
    } catch (e) {
      setError(e.response?.data?.message || 'Invalid discount code.')
      setDiscount(null)
      return null
    } finally {
      setLoading(false)
    }
  }, [])

  const removeDiscount = useCallback(() => {
    setDiscount(null); setError(null); setSuccess(null)
  }, [])

  /**
   * getItemDiscounts(item)
   * Returns ALL discounts that apply to this item:
   *   1. The user-applied code discount (if active & matches category)
   *   2. Any public/sitewide discounts from admin that match this item's category
   *
   * Returns an array so ProductCard can show multiple badges.
   */
  const getItemDiscounts = useCallback((item) => {
    const result = []

    // 1. User-applied code
    if (discount) {
      const cats = discount.categories
      const matches = !cats || cats.length === 0
        || cats.map(c => c.toLowerCase()).includes((item.category || '').toLowerCase())
      if (matches) result.push({ ...discount, source: 'code' })
    }

    // 2. Public admin discounts — badge_config comes straight from the DB via /api/discounts/public
    publicDiscounts.forEach(pd => {
      const cats = pd.categories
      const matches = !cats || cats.length === 0
        || cats.map(c => c.toLowerCase()).includes((item.category || '').toLowerCase())
      if (matches) result.push({ ...pd, source: 'public' })
    })

    return result
  }, [discount, publicDiscounts])

  /**
   * bestDiscountForItem(item)
   * Returns the single best (highest saving) discount for a given item.
   * Used by ProductCard price display.
   */
  const bestDiscountForItem = useCallback((item) => {
    const all = getItemDiscounts(item)
    if (all.length === 0) return null
    if (all.length === 1) return all[0]
    // Pick highest saving
    return all.reduce((best, d) => {
      const saving = d.type === 'percentage'
        ? item.price * d.value / 100
        : Math.min(d.value, item.price)
      const bestSaving = best.type === 'percentage'
        ? item.price * best.value / 100
        : Math.min(best.value, item.price)
      return saving > bestSaving ? d : best
    })
  }, [getItemDiscounts])

  /**
   * calcDiscount(subtotal, items)
   * Returns total discount amount to deduct at checkout / cart summary.
   * Considers BOTH the user-applied code discount AND public sitewide discounts.
   * When both apply to the same item we use the best one (no double-dipping).
   */
  const calcDiscount = useCallback((subtotal, items = null) => {
    // Helper: best saving for one cart item across all active discounts
    const bestSavingForItem = (item) => {
      const all = getItemDiscounts(item)
      if (all.length === 0) return 0
      return all.reduce((best, d) => {
        const saving = d.type === 'percentage'
          ? item.price * d.value / 100
          : Math.min(d.value, item.price)
        return saving > best ? saving : best
      }, 0)
    }

    if (items && items.length > 0) {
      // Per-item calculation — respects category restrictions correctly
      const total = items.reduce((sum, item) => {
        return sum + bestSavingForItem(item) * item.qty
      }, 0)
      return Math.round(total * 100) / 100
    }

    // Fallback: no items provided, use legacy code-discount-only logic
    if (!discount) return 0
    const cats = discount.categories
    if (cats && cats.length > 0) return 0 // can't calc without items
    if (discount.type === 'percentage')
      return Math.round(subtotal * (discount.value / 100) * 100) / 100
    return Math.min(discount.value, subtotal)
  }, [discount, getItemDiscounts])

  const isItemDiscounted = useCallback((item) => {
    return getItemDiscounts(item).length > 0
  }, [getItemDiscounts])

  return (
    <DiscountContext.Provider value={{
      discount, publicDiscounts, loading, error, success,
      applyDiscount, removeDiscount, calcDiscount,
      isItemDiscounted, getItemDiscounts, bestDiscountForItem,
    }}>
      {children}
    </DiscountContext.Provider>
  )
}

export const useDiscount = () => {
  const ctx = useContext(DiscountContext)
  if (!ctx) return {
    discount: null, publicDiscounts: [], loading: false, error: null, success: null,
    applyDiscount: async () => null,
    removeDiscount: () => {},
    calcDiscount: () => 0,
    isItemDiscounted: () => false,
    getItemDiscounts: () => [],
    bestDiscountForItem: () => null,
  }
  return ctx
}
