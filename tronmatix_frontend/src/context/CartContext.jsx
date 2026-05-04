// src/context/CartContext.jsx
import { createContext, useContext, useState, useEffect } from 'react'

const CartContext  = createContext(null)
const STORAGE_KEY  = 'tronmatix_cart'

// ── localStorage helpers ──────────────────────────────────────────────────────
const loadCart = () => {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    const parsed = raw ? JSON.parse(raw) : []
    return Array.isArray(parsed) ? parsed : []
  } catch { return [] }
}

const saveCart = (items) => {
  try { localStorage.setItem(STORAGE_KEY, JSON.stringify(items)) } catch {}
}

export function CartProvider({ children }) {
  // FIX: lazy init from localStorage — survives page reload
  const [items,    setItems]    = useState(() => loadCart())
  const [cartOpen, setCartOpen] = useState(false)

  // FIX: persist to localStorage on every change
  useEffect(() => { saveCart(items) }, [items])

  const addItem = (product) => {
    setItems(prev => {
      const existing = prev.find(i => i.id === product.id)
      if (existing) return prev.map(i => i.id === product.id ? { ...i, qty: i.qty + 1 } : i)
      return [...prev, { ...product, qty: 1 }]
    })
    setCartOpen(true)
  }

  const removeItem = (id) => setItems(prev => prev.filter(i => i.id !== id))

  const updateQty = (id, delta) => {
    setItems(prev =>
      prev.map(i => i.id === id ? { ...i, qty: Math.max(1, i.qty + delta) } : i)
          .filter(i => i.qty > 0)
    )
  }

  const clearCart = () => setItems([])

  const subtotal = items.reduce((sum, i) => sum + i.price * i.qty, 0)
  const total    = subtotal
  const count    = items.reduce((sum, i) => sum + i.qty, 0)

  return (
    <CartContext.Provider value={{
      items, cartOpen, setCartOpen,
      addItem, removeItem, updateQty, clearCart,
      subtotal, total, count,
    }}>
      {children}
    </CartContext.Provider>
  )
}

export const useCart = () => useContext(CartContext)
export default CartContext
