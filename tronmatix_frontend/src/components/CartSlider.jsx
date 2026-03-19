import { useRef, useEffect } from 'react'
import { useCart } from '../context/CartContext'
import { useDiscount } from '../context/DiscountContext'
import { useTheme } from '../context/ThemeContext'
import { useNavigate } from 'react-router-dom'
import { resolveImage } from '../lib/resolveImage'
import logo from '../assets/logo.png'

export default function CartSlider() {
  const { items, cartOpen, setCartOpen, removeItem, updateQty, subtotal, clearCart } = useCart()
  const { discount, isItemDiscounted, calcDiscount, getItemDiscounts } = useDiscount()
  const { dark } = useTheme()
  const discountAmount = calcDiscount(subtotal, items)
  const navigate = useNavigate()
  const itemsRef = useRef(null)
  const prevCountRef = useRef(0)

  useEffect(() => {
    const current = items.reduce((s, i) => s + i.qty, 0)
    if (current > prevCountRef.current && itemsRef.current) itemsRef.current.scrollTop = 0
    prevCountRef.current = current
  }, [items])

  const panelBg  = dark ? '#111827' : '#1a1a1a'
  const headerBd = dark ? '#1f2937' : '#2a2a2a'
  const itemBd   = dark ? '#1f2937' : '#2a2a2a'
  const footerBg = dark ? '#0f172a' : '#0d0d0d'

  return (
    <>
      {cartOpen && (
        <div className="fixed inset-0 bg-black/40 z-[90]" onClick={() => setCartOpen(false)} />
      )}
      <div
        className={`fixed top-0 right-0 h-full w-80 z-[95] shadow-2xl transition-transform duration-300 flex flex-col ${cartOpen ? 'translate-x-0' : 'translate-x-full'}`}
        style={{ background: panelBg }}
      >
        {/* Header */}
        <div className="flex items-center justify-between p-4 flex-shrink-0" style={{ borderBottom: `1px solid ${headerBd}` }}>
          <div>
            <h2 className="text-white font-bold tracking-wider" style={{ fontFamily: 'Rajdhani, sans-serif', fontSize: 20 }}>SHOPPING CART</h2>
            {items.length > 0 && (
              <span className="text-gray-400" style={{ fontSize: 18 }}>
                {items.reduce((s, i) => s + i.qty, 0)} item{items.reduce((s, i) => s + i.qty, 0) !== 1 ? 's' : ''}
              </span>
            )}
          </div>
          <div className="flex items-center gap-3">
            <img src={logo} alt="" className="h-16" />
            <button onClick={() => setCartOpen(false)} className="text-gray-400 hover:text-white transition-colors" style={{ fontSize: 20, lineHeight: 1 }}>✕</button>
          </div>
        </div>

        {/* Items */}
        <div ref={itemsRef} className="flex-1 overflow-y-auto p-4 space-y-4">
          {items.length === 0 ? (
            <div className="flex flex-col items-center justify-center h-full gap-3 py-16">
              <div style={{ fontSize: 40 }}>🛒</div>
              <p className="text-gray-400 text-center" style={{ fontSize: 14 }}>Your cart is empty</p>
            </div>
          ) : items.map(item => {
            // FIX: use shared resolveImage — handles S3/R2 full URLs and local /storage/ paths
            const imgUrl = resolveImage(item.image)

            return (
              <div key={item.id} className="flex items-start gap-3 pb-4" style={{ borderBottom: `1px solid ${itemBd}` }}>
                <img
                  src={imgUrl || '/placeholder.png'}
                  alt={item.name}
                  className="w-16 h-16 object-cover rounded flex-shrink-0"
                  onError={e => { e.target.src = '/placeholder.png' }}
                />
                <div className="flex-1 min-w-0">
                  <p className="text-white font-bold leading-tight mb-1" style={{ fontSize: 18 }}>{item.name}</p>

                  {/* Discount badges */}
                  {getItemDiscounts(item).map((d, di) => (
                    <span key={di} style={{
                      fontSize: 10, borderRadius: 20, padding: '2px 8px', fontWeight: 800,
                      letterSpacing: 1, display: 'inline-flex', alignItems: 'center', gap: 3, marginBottom: 4,
                      background: d.badge_config?.bg || (d.source === 'public' ? 'rgba(124,58,237,0.85)' : '#F97316'),
                      color: d.badge_config?.color || '#fff',
                      border: d.badge_config ? `1px solid ${d.badge_config.border || 'transparent'}` : 'none',
                    }}>
                      {d.badge_config?.icon || '🏷'}{' '}
                      {d.badge_config?.text || (d.type === 'percentage' ? `${d.value}% OFF` : `-$${Number(d.value).toFixed(2)}`)}
                    </span>
                  ))}

                  {isItemDiscounted(item) === false && discount?.categories?.length > 0 && (
                    <span style={{ fontSize: 10, color: '#666', fontStyle: 'italic', display: 'block', marginBottom: 2 }}>
                      not eligible
                    </span>
                  )}

                  <div className="flex items-center gap-2 mt-1">
                    <button onClick={() => updateQty(item.id, -1)}
                      className="w-6 h-6 flex items-center justify-center rounded border border-[#3a3a3a] text-primary font-bold hover:border-primary transition-colors"
                      style={{ fontSize: 16, lineHeight: 1 }}>−</button>
                    <span className="text-white font-bold" style={{ fontSize: 18, minWidth: 14, textAlign: 'center' }}>{item.qty}</span>
                    <button onClick={() => updateQty(item.id, 1)}
                      className="w-6 h-6 flex items-center justify-center rounded border border-[#3a3a3a] text-primary font-bold hover:border-primary transition-colors"
                      style={{ fontSize: 16, lineHeight: 1 }}>+</button>
                    <span className="text-primary font-bold ml-1" style={{ fontSize: 18 }}>${(item.price * item.qty).toFixed(2)}</span>
                  </div>
                </div>
                <button onClick={() => removeItem(item.id)}
                  className="text-[#777] hover:text-red-400 transition-colors flex-shrink-0 mt-1"
                  style={{ fontSize: 20, lineHeight: 1 }}>✕</button>
              </div>
            )
          })}
        </div>

        {/* Footer */}
        {items.length > 0 && (
          <div className="flex-shrink-0 p-4 space-y-2" style={{ borderTop: `1px solid ${headerBd}`, background: footerBg }}>
            <div className="flex justify-between items-center">
              <span className="text-gray-400 font-semibold" style={{ fontSize: 18 }}>SUBTOTAL</span>
              <span className="text-white font-bold" style={{ fontSize: 18 }}>${subtotal.toFixed(2)}</span>
            </div>

            {discountAmount > 0 && (
              <div className="flex justify-between font-bold text-green-500" style={{ fontSize: 15 }}>
                <span>
                  🏷{discount?.code ? ` ${discount.code}` : ''}
                  {discount?.type
                    ? ` (${discount.type === 'percentage' ? `${discount.value}% OFF` : `$${Number(discount.value).toFixed(2)} OFF`})`
                    : ` (−$${discountAmount.toFixed(2)} OFF)`}
                </span>
                <span>−${discountAmount.toFixed(2)}</span>
              </div>
            )}

            {discount && discountAmount === 0 && (
              <div className="px-3 py-1.5 rounded-lg" style={{ background: 'rgba(249,115,22,0.08)', border: '1px solid rgba(249,115,22,0.2)' }}>
                <span style={{ fontSize: 14, color: '#F97316', fontWeight: 700 }}>
                  🏷 {discount?.code || 'Discount'} — no eligible items
                </span>
              </div>
            )}

            <div className="flex justify-between items-center pb-1">
              <span className="text-primary font-black" style={{ fontSize: 18 }}>TOTAL</span>
              <span className="text-primary font-black" style={{ fontSize: 20 }}>${Math.max(0, subtotal - discountAmount).toFixed(2)}</span>
            </div>

            <button
              onClick={() => { setCartOpen(false); navigate('/cart') }}
              className="w-full py-3 bg-primary text-white font-black rounded-xl hover:bg-orange-600 active:scale-95 transition-all shadow-lg"
              style={{ fontFamily: 'Rajdhani, sans-serif', fontSize: 16, letterSpacing: 1 }}>
              🛒 CHECKOUT NOW
            </button>
            <button
              onClick={clearCart}
              className="w-full py-2 text-[#777] font-semibold hover:text-red-400 transition-colors text-center"
              style={{ fontSize: 18 }}>
              Clear cart
            </button>
          </div>
        )}
      </div>
    </>
  )
}
