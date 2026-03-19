import { useCart } from '../context/CartContext'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { useDeliveryLocation } from '../context/LocationContext'
import { useDiscount } from '../context/DiscountContext'
import { useTheme } from '../context/ThemeContext'
import { resolveImage } from '../lib/resolveImage'

export default function CartPage() {
  const { items, removeItem, updateQty, subtotal } = useCart()
  const { user } = useAuth()
  const { savedLocation } = useDeliveryLocation()
  const navigate = useNavigate()
  const { discount, isItemDiscounted, calcDiscount, getItemDiscounts } = useDiscount()
  const { dark } = useTheme()
  const discountAmount = calcDiscount(subtotal, items)

  const bg       = dark ? '#111827' : '#fff'
  const cardBg   = dark ? '#1f2937' : '#fff'
  const border   = dark ? '#374151' : '#e5e7eb'
  const textMain = dark ? '#f9fafb' : '#1f2937'
  const textSub  = dark ? '#9ca3af' : '#6b7280'
  const rowBd    = dark ? '#374151' : '#e5e7eb'
  const hoverBg  = dark ? '#374151' : '#f3f4f6'

  return (
    <div className="max-w-[1280px] mx-auto px-4 py-8 min-h-[60vh]" style={{ background: bg }}>
      <h1 className="text-2xl font-bold text-primary mb-6 underline" style={{ fontFamily: 'Rajdhani, sans-serif' }}>
        Shopping Cart
      </h1>

      <div className="flex flex-col lg:flex-row gap-8">
        {/* Cart items */}
        <div className="flex-1">
          {items.length === 0 ? (
            <div className="text-center py-16" style={{ color: textSub }}>
              <p className="text-lg mb-4">Your cart is empty</p>
              <Link to="/" className="text-primary hover:underline">Continue Shopping</Link>
            </div>
          ) : (
            <>
              <div className="text-right text-sm mb-4" style={{ color: textSub }}>
                <span style={{ fontSize: 18 }}>Deliver To</span><br/>
                {savedLocation?.address ? (
                  <span className="font-semibold" style={{ fontSize: 18, color: textMain }}>
                    {savedLocation.address}{savedLocation.city ? ', ' + savedLocation.city : ''}
                  </span>
                ) : (
                  <Link to="/checkout" className="text-primary font-semibold hover:underline">+ Add delivery address</Link>
                )}
              </div>

              {items.map(item => (
                <div key={item.id} className="flex items-center gap-4 py-4" style={{ borderBottom: `1px solid ${rowBd}` }}>
                  <img
                    src={resolveImage(
                      Array.isArray(item.images) ? item.images[0] : item.image
                    ) || '/placeholder.png'}
                    alt={item.name}
                    className="w-20 h-20 object-cover rounded flex-shrink-0"
                    onError={e => { e.target.src = '/placeholder.png' }}
                  />
                  <div className="flex-1">
                    <p className="font-bold text-sm" style={{ fontFamily: 'HurstBagod, Rajdhani, sans-serif', fontSize: 18, color: textMain }}>
                      {item.name}
                    </p>
                    {getItemDiscounts(item).map((d, di) => (
                      <span key={di} style={{ fontSize: 12, borderRadius: 20, padding: '3px 10px', fontWeight: 800, letterSpacing: 1, display:'inline-flex', alignItems:'center', gap:4,
                        background: d.badge_config?.bg || (d.source === 'public' ? 'rgba(124,58,237,0.85)' : '#F97316'),
                        color: d.badge_config?.color || '#fff',
                        border: d.badge_config ? `1px solid ${d.badge_config.border || 'transparent'}` : 'none',
                      }}>
                        {d.badge_config?.icon || '🏷'}{' '}
                        {d.badge_config?.text || (d.type === 'percentage' ? `${d.value}% OFF` : `-$${Number(d.value).toFixed(2)}`)}
                      </span>
                    ))}
                    {!isItemDiscounted(item) && discount?.categories?.length > 0 && (
                      <span style={{ fontSize: 11, color: textSub, fontStyle: 'italic' }}>not eligible for this discount</span>
                    )}
                  </div>
                  <div className="flex items-center gap-2">
                    <button onClick={() => updateQty(item.id, -1)} className="hover:text-primary font-bold text-lg" style={{ color: textSub }}>−</button>
                    <span className="text-primary font-bold" style={{ fontSize: 20 }}>{item.qty}x = <span>${(item.price * item.qty).toFixed(2)}</span></span>
                    <button onClick={() => updateQty(item.id, 1)} className="hover:text-primary font-bold text-lg" style={{ color: textSub }}>+</button>
                  </div>
                  <button onClick={() => removeItem(item.id)} className="text-red-400 hover:text-red-600 ml-2">✕</button>
                </div>
              ))}

              <div className="mt-4">
                <Link to="/" className="text-primary hover:underline font-semibold" style={{ fontSize: 20 }}>Continue Shopping</Link>
              </div>
            </>
          )}
        </div>

        {/* Summary */}
        {items.length > 0 && (
          <div className="w-full lg:w-80">
            <div className="rounded-lg p-5" style={{ border: `1px solid ${border}`, background: cardBg }}>
              <div className="flex justify-between mb-3 pb-3" style={{ borderBottom: `1px solid ${border}` }}>
                <span className="font-bold" style={{ fontSize: 20, color: textMain }}>Summary</span>
                <span style={{ fontSize: 20, color: textSub }}>Total item {items.reduce((s, i) => s + i.qty, 0)}</span>
              </div>
              <div className="space-y-2 mb-4 text-sm">
                {[
                  { label: 'Subtotal :', val: `$${subtotal.toFixed(2)}` },
                  { label: 'Delivery :', val: '$0.00' },
                  { label: 'Tax :', val: '$0.00' },
                ].map(({ label, val }) => (
                  <div key={label} className="flex justify-between">
                    <span style={{ fontSize: 20, color: textSub }}>{label}</span>
                    <span className="font-bold" style={{ fontSize: 20, color: textMain }}>{val}</span>
                  </div>
                ))}
                {discountAmount > 0 && (
            <div className="flex justify-between font-bold text-green-500" style={{ fontSize: 13 }}>
              <span>
                🏷{discount?.code ? ` ${discount.code}` : ''}
                {discount?.type
                  ? ` (${discount.type === "percentage"
                      ? `${discount.value}% OFF`
                      : `$${Number(discount.value).toFixed(2)} OFF`})`
                  : ` (−$${discountAmount.toFixed(2)} OFF)`}
              </span>
              <span>−${discountAmount.toFixed(2)}</span>
            </div>
          )}
                {discount && discountAmount === 0 && (
                  <div className="px-3 py-2 rounded-lg" style={{ background:'rgba(249,115,22,0.06)', border:'1px solid rgba(249,115,22,0.2)' }}>
                    <span style={{ fontSize: 13, color: '#F97316', fontWeight: 700 }}>🏷 {discount?.code || 'Discount'} — no items eligible</span>
                  </div>
                )}
              </div>
              <div className="pt-3 flex justify-between font-bold mb-4" style={{ borderTop: `1px solid ${border}` }}>
                <span style={{ fontSize: 20, color: textMain }}>Total :</span>
                <span style={{ fontSize: 20 }} className="text-primary">${Math.max(0, subtotal - discountAmount).toFixed(2)}</span>
              </div>
              <button onClick={() => navigate('/checkout')}
                className="w-full text-center text-primary font-bold py-2 border border-primary rounded hover:bg-primary hover:text-white transition-colors"
                style={{ fontFamily: 'Rajdhani, sans-serif' }}>
                Check Out
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
