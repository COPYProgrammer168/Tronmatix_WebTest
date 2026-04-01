// src/context/DiscountContext.jsx
import { createContext, useContext, useState, useCallback, useEffect } from 'react'
import axios from '../lib/axios'

const DiscountContext = createContext(null)

export function DiscountProvider({ children }) {
  const [discount,        setDiscount]        = useState(null)   // user-applied code
  const [publicDiscounts, setPublicDiscounts] = useState([])     // admin-set badge deals (kind==='badge')
  const [loading,  setLoading]  = useState(false)
  const [error,    setError]    = useState(null)
  const [success,  setSuccess]  = useState(null)

  // ── Fetch active public BADGE discounts on mount ─────────────────────────
  // Only badge-kind discounts auto-display; code-kind require manual entry.
  useEffect(() => {
    let cancelled = false
    const load = (attempt = 1) => {
      axios.get('/api/discounts/public')
        .then(res => {
          if (cancelled) return
          const raw  = res.data?.data ?? res.data
          const data = Array.isArray(raw) ? raw : []
          // Keep only badge-kind (or legacy entries that have badge_config but no kind field)
          const badges = data.filter(d =>
            d.kind === 'badge' || (!d.kind && d.badge_config?.text)
          )
          setPublicDiscounts(badges)
        })
        .catch(err => {
          if (cancelled) return
          console.warn('[DiscountContext] /api/discounts/public failed:', err?.response?.status, err?.message)
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
      // Reject badge-kind discounts from manual code entry
      if (res.data?.kind === 'badge') {
        setError('This discount is applied automatically — no code needed.')
        return null
      }
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
   *   1. The user-applied code discount (kind='code', if active & matches category)
   *   2. Any public badge discounts from admin that match this item's category
   *
   * Returns an array so ProductCard can show multiple badges.
   */
  const getItemDiscounts = useCallback((item) => {
    const result = []

    // 1. User-applied code discount
    if (discount) {
      const cats = discount.categories
      const matches = !cats || cats.length === 0
        || cats.map(c => c.toLowerCase()).includes((item.category || '').toLowerCase())
      if (matches) result.push({ ...discount, source: 'code' })
    }

    // 2. Public badge discounts — auto-shown, no code required
    publicDiscounts.forEach(pd => {
      const cats = pd.categories
      const matches = !cats || cats.length === 0
        || cats.map(c => c.toLowerCase()).includes((item.category || '').toLowerCase())
      if (matches) result.push({ ...pd, source: 'badge' })
    })

    return result
  }, [discount, publicDiscounts])

  /**
   * bestDiscountForItem(item)
   * Returns the single best (highest saving) discount for a given item.
   */
  const bestDiscountForItem = useCallback((item) => {
    const all = getItemDiscounts(item)
    if (all.length === 0) return null
    if (all.length === 1) return all[0]
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
   * Returns total discount to deduct at checkout.
   * Considers BOTH code and badge discounts; best-one-wins per item (no double-dipping).
   */
  const calcDiscount = useCallback((subtotal, items = null) => {
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
      const total = items.reduce((sum, item) => {
        return sum + bestSavingForItem(item) * item.qty
      }, 0)
      return Math.round(total * 100) / 100
    }

    // Fallback: legacy code-discount-only logic (no items provided)
    if (!discount) return 0
    const cats = discount.categories
    if (cats && cats.length > 0) return 0
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
