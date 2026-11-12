// src/context/CartContext.jsx
import { createContext, useContext, useState, useEffect } from "react";
import { useLang } from "./LanguageContext";

const CartContext = createContext(null);
const STORAGE_KEY = "tronmatix_cart";

// ── localStorage helpers ──────────────────────────────────────────────────────
const loadCart = () => {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    const parsed = raw ? JSON.parse(raw) : [];
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
};

const saveCart = (items) => {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
  } catch {}
};

export function CartProvider({ children }) {
  const [items, setItems] = useState(() => loadCart());
  const [cartOpen, setCartOpen] = useState(false);
  const [notification, setNotification] = useState(null);
  const { t } = useLang();

  useEffect(() => {
    saveCart(items);
  }, [items]);

  // Stock limit for a product. null / undefined / 0 / NaN stock means
  // "unlimited" (legacy products without tracked inventory).
  const stockOf = (item) => {
    const s = item?.stock;
    return s == null || isNaN(s) || s <= 0 ? null : Number(s);
  };

  // Max quantity for a product: its stock, or null when unlimited.
  // addItem/updateQty compare against the qty already in the cart, so the
  // combined total never exceeds stock.
  const maxQtyFor = (item) => {
    const stock = stockOf(item);
    if (stock === null) return null;
    return Math.max(0, stock);
  };

  const addItem = (product) => {
    setItems((prev) => {
      const existing = prev.find((i) => i.id === product.id);

      // Limit how many of this product can sit in the cart. null = unlimited.
      const max = maxQtyFor(product);
      const currentQty = existing?.qty ?? 0;
      if (max !== null && currentQty >= max) {
        return prev; // already at (or over) stock — don't add more
      }

      if (existing)
        return prev.map((i) =>
          i.id === product.id ? { ...i, qty: i.qty + 1 } : i,
        );
      return [...prev, { ...product, qty: 1, warranty: product.warranty }];
    });
    setNotification(`${product.name || "Item"} ${t("product.added")}`);
  };

  const removeItem = (id) =>
    setItems((prev) => prev.filter((i) => i.id !== id));

  const updateQty = (id, delta) => {
    setItems((prev) =>
      prev
        .map((i) => {
          if (i.id !== id) return i;
          const max = maxQtyFor(i);
          const next = max === null
            ? i.qty + delta
            : Math.min(max, Math.max(1, i.qty + delta));
          return { ...i, qty: next };
        })
        .filter((i) => i.qty > 0),
    );
  };

  const clearCart = () => {
    setItems([]);
    saveCart([]);
  };

  // FIX: guard against symbol-only prices ('$', '$$', '$$$') to avoid NaN
  const subtotal = items.reduce((sum, i) => {
    const price = parseFloat(i.price);
    return sum + (isNaN(price) ? 0 : price * i.qty);
  }, 0);
  const total = subtotal;
  const count = items.reduce((sum, i) => sum + i.qty, 0);

  return (
    <CartContext.Provider
      value={{
        items,
        cartOpen,
        setCartOpen,
        notification,
        setNotification,
        addItem,
        removeItem,
        updateQty,
        clearCart,
        subtotal,
        total,
        count,
      }}
    >
      {children}
    </CartContext.Provider>
  );
}

export const useCart = () => useContext(CartContext);
export default CartContext;
