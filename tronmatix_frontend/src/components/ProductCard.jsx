import { useState } from 'react'
import { Link } from 'react-router-dom'
import { useCart } from '../context/CartContext'
import { useFavorites } from '../context/FavoritesContext'
import { useDiscount } from '../context/DiscountContext'
import { useTheme } from '../context/ThemeContext'

const LARAVEL_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

function resolveImage(path) {
  if (!path) return null
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  return LARAVEL_URL + (path.startsWith('/') ? path : '/' + path)
}

function PlaceholderImg({ name, dark }) {
  return (
    <div className="h-32 w-full flex flex-col items-center justify-center" style={{ color: dark ? '#4b5563' : '#d1d5db' }}>
      <svg className="w-10 h-10 mb-1" fill="none" stroke="currentColor" strokeWidth={1.5} viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round"
          d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 19.5h18M3 4.5h18M12 9.75a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
      </svg>
      <span style={{ fontSize: 10, textAlign: 'center', padding: '0 8px', lineHeight: 1.3 }}>{name}</span>
    </div>
  )
}

function AddToCartBtn({ onAdd, dark, cardHovered }) {
  const [state, setState] = useState('idle') // idle | adding | added

  const handleClick = () => {
    if (state !== 'idle') return
    setState('adding')
    setTimeout(() => {
      onAdd()
      setState('added')
      setTimeout(() => setState('idle'), 1800)
    }, 220)
  }

  const isAdded  = state === 'added'
  const isAdding = state === 'adding'

  const bgColor = isAdded
    ? '#22c55e'
    : isAdding
      ? '#F97316'
      : cardHovered          // whole-card hover drives the orange
        ? '#F97316'
        : '#111827'

  return (
    <button
      onClick={handleClick}
      disabled={state !== 'idle'}
      className="mt-auto w-full font-bold rounded transition-all duration-200"
      style={{
        fontFamily: 'Rajdhani, sans-serif',
        fontSize: 15,
        letterSpacing: 1,
        height: 42,
        background: bgColor,
        color: '#fff',
        border: 'none',
        transform: isAdding ? 'scale(0.97)' : 'scale(1)',
        boxShadow: isAdded
          ? '0 0 0 3px rgba(34,197,94,0.3)'
          : cardHovered && !isAdding
            ? '0 4px 14px rgba(249,115,22,0.4)'
            : 'none',
        cursor: state !== 'idle' ? 'not-allowed' : 'pointer',
      }}>
      <span className="flex items-center justify-center gap-2 w-full h-full">
        {isAdded ? (
          <>
            <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth={2.5} viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span>ADDED!</span>
          </>
        ) : isAdding ? (
          <>
            <svg className="w-4 h-4 animate-spin" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>ADDING...</span>
          </>
        ) : (
          <>
            <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>ADD TO CART</span>
          </>
        )}
      </span>
    </button>
  )
}

export default function ProductCard({ product }) {
  const { addItem }                                           = useCart()
  const { toggleFavorite, isFavorite }                        = useFavorites()
  const { getItemDiscounts, bestDiscountForItem }              = useDiscount()
  const { dark }                                              = useTheme()
  const [hovered, setHovered]                                 = useState(false)

  const fav             = isFavorite(product.id)
  const images          = Array.isArray(product.images) ? product.images : []
  const image1Path      = images[0] ?? product.image ?? null
  const image2Path      = images[1] ?? null
  const imageUrl        = resolveImage(image1Path)
  const image2Url       = resolveImage(image2Path)
  const hasHoverImg     = image2Url && image2Url !== imageUrl

  // All discounts that apply to this product (code + public/auto)
  const itemDiscounts   = getItemDiscounts(product)
  const isDiscounted    = itemDiscounts.length > 0
  const bestDiscount    = bestDiscountForItem(product)          // best single discount for price calc

  const discountedPrice = bestDiscount && product.price
    ? Math.max(0, product.price - (
        bestDiscount.type === 'percentage'
          ? product.price * bestDiscount.value / 100
          : Math.min(bestDiscount.value, product.price)
      ))
    : null

  // Product badge (set from admin dashboard)
  const badge = product.badge ?? null

  const cardBg = dark ? '#1f2937' : '#fff'
  const border = dark ? '#374151' : '#e5e7eb'
  const imgBg  = dark ? '#111827' : '#f9fafb'
  const text   = dark ? '#f9fafb' : '#1f2937'
  const favBg  = dark ? '#1f2937' : '#fff'

  return (
    <div
      className="product-card rounded-lg overflow-hidden transition-shadow relative group flex flex-col"
      onMouseEnter={() => setHovered(true)}
      onMouseLeave={() => setHovered(false)}
      style={{
        border: isDiscounted ? '1px solid rgba(249,115,22,0.45)' : `1px solid ${border}`,
        background: cardBg,
        boxShadow: hovered
          ? isDiscounted ? '0 8px 30px rgba(249,115,22,0.18)' : '0 8px 30px rgba(0,0,0,0.12)'
          : isDiscounted ? '0 2px 10px rgba(249,115,22,0.08)' : '0 1px 4px rgba(0,0,0,0.06)',
        transition: 'box-shadow 0.2s ease, border-color 0.2s ease',
      }}>

      {/* Fav button */}
      <button onClick={() => toggleFavorite(product)}
        className="absolute top-2 right-2 z-10 w-8 h-8 flex items-center justify-center rounded-full shadow-md transition-transform hover:scale-110"
        style={{ background: favBg }}
        title={fav ? 'Remove from favorites' : 'Add to favorites'}>
        <svg className="w-5 h-5" fill={fav ? '#F97316' : 'none'} stroke={fav ? '#F97316' : '#9ca3af'} strokeWidth={2} viewBox="0 0 24 24">
          <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
        </svg>
      </button>

      {/* ── Top-left badges ── */}
      <div className="absolute top-2 left-2 z-10 flex flex-col gap-1">

        {/* Admin product badge */}
        {badge && (
          <div className="flex items-center gap-1 font-black rounded-full shadow-lg"
            style={{
              fontSize: 11, letterSpacing: 0.5, padding: '4px 10px',
              background: badge.bg || 'rgba(249,115,22,0.18)',
              border: `1.5px solid ${badge.border || 'rgba(249,115,22,0.55)'}`,
              color: badge.color || '#F97316',
              boxShadow: '0 2px 8px rgba(0,0,0,0.25)',
            }}>
            {badge.icon || '🏷️'} {badge.text}
          </div>
        )}

        {/* One badge per discount that applies to this product */}
        {itemDiscounts.map((d, idx) => {
          // If the admin configured a custom badge for this discount, use its styling.
          // Otherwise fall back to the default purple (public/sitewide) or orange (code) look.
          const bc = d.badge_config
          const bgStyle = bc
            ? { background: bc.bg || 'rgba(249,115,22,0.18)', border: `1.5px solid ${bc.border || 'rgba(249,115,22,0.55)'}` }
            : d.source === 'public'
              ? { background: 'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)', border: 'none' }
              : { background: 'linear-gradient(135deg, #F97316 0%, #ea580c 100%)', border: 'none' }
          const badgeColor = bc ? (bc.color || '#F97316') : '#fff'
          const badgeIcon  = bc ? (bc.icon || '🏷') : '🏷'
          const badgeText  = bc
            ? bc.text
            : (d.type === 'percentage' ? `${d.value}% OFF` : `-$${Number(d.value).toFixed(2)}`)
          const shadowStyle = bc
            ? {}
            : d.source === 'public'
              ? { boxShadow: '0 2px 8px rgba(124,58,237,0.5)' }
              : { boxShadow: '0 2px 8px rgba(249,115,22,0.5)' }

          return (
            <div key={idx}>
              <div className="flex items-center gap-1 font-black rounded-full shadow-lg"
                style={{
                  fontSize: 11, letterSpacing: 0.5, padding: '4px 10px',
                  color: badgeColor,
                  ...bgStyle,
                  ...shadowStyle,
                }}>
                {badgeIcon} {badgeText}
                {d.source === 'code' && !bc && (
                  <span style={{ fontSize: 9, opacity: 0.8, marginLeft: 2 }}>({d.code})</span>
                )}
              </div>
              {/* Savings sub-badge — only show when no custom badge text is set */}
              {!bc && d.type === 'percentage' && product.price && (
                <div className="flex items-center gap-1 rounded-full font-bold"
                  style={{
                    fontSize: 10, padding: '2px 8px',
                    background: 'rgba(34,197,94,0.15)', color: '#22c55e',
                    border: '1px solid rgba(34,197,94,0.35)', width: 'fit-content',
                  }}>
                  save ${(product.price * d.value / 100).toFixed(2)}
                </div>
              )}
            </div>
          )
        })}
      </div>

      {/* Image */}
      <Link to={`/product/${product.id}`} className="block">
        <div className="relative overflow-hidden flex items-center justify-center" style={{ height: 200, background: imgBg }}>
          {imageUrl ? (
            <>
              <img src={imageUrl} alt={product.name}
                className="h-40 object-contain absolute inset-0 m-auto transition-all duration-300"
                style={{
                  opacity: (hovered && hasHoverImg) ? 0 : 1,
                  transform: (hovered && hasHoverImg) ? 'scale(1.05) translateY(-4px)' : 'scale(1) translateY(0)',
                }}
                onError={e => { e.target.style.display = 'none' }} />
              {hasHoverImg && (
                <img src={image2Url} alt={`${product.name} hover`}
                  className="h-40 object-contain absolute inset-0 m-auto transition-all duration-300"
                  style={{
                    opacity: hovered ? 1 : 0,
                    transform: hovered ? 'scale(1.05) translateY(-4px)' : 'scale(0.95) translateY(4px)',
                  }}
                  onError={e => { e.target.style.display = 'none' }} />
              )}
            </>
          ) : (
            <div className="absolute inset-0 flex items-center justify-center">
              <PlaceholderImg name={product.name} dark={dark} />
            </div>
          )}
        </div>
      </Link>

      <div className="p-3 text-center flex flex-col flex-1">
        <Link to={`/product/${product.id}`}>
          <h3 className="font-bold mb-1 leading-tight hover:text-primary transition-colors line-clamp-2"
            style={{ fontFamily: 'Rajdhani, sans-serif', fontSize: 18, color: text }}>
            {product.name}
          </h3>
        </Link>

        {/* Fixed-height price block — keeps ADD TO CART button aligned across all cards */}
        <div className="flex flex-col items-center justify-end mb-3" style={{ minHeight: 52 }}>
          {discountedPrice !== null ? (
            <>
              <div className="text-primary font-black" style={{ fontSize: 20 }}>
                ${discountedPrice.toFixed(2)}
              </div>
              <div className="flex items-center justify-center gap-2 flex-wrap">
                <span className="line-through font-semibold" style={{ fontSize: 13, color: dark ? '#6b7280' : '#9ca3af' }}>
                  ${Number(product.price).toFixed(2)}
                </span>
                <span className="font-black rounded-full px-1.5 py-0.5"
                  style={{ fontSize: 11, background: 'rgba(34,197,94,0.12)', color: '#22c55e', border: '1px solid rgba(34,197,94,0.3)' }}>
                  −{bestDiscount.type === 'percentage'
                    ? `${bestDiscount.value}%`
                    : `$${Number(bestDiscount.value).toFixed(2)}`}
                </span>
              </div>
            </>
          ) : (
            <div className="text-primary font-bold" style={{ fontSize: 18 }}>
              {product.price ? `$${Number(product.price).toFixed(2)}` : '$$$'}
            </div>
          )}
        </div>

        <AddToCartBtn onAdd={() => addItem(product)} dark={dark} cardHovered={hovered} />
      </div>
    </div>
  )
}
