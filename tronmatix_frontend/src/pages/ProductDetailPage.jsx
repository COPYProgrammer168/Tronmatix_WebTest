import { useState, useEffect } from 'react'
import { useParams } from 'react-router-dom'
import { useCart } from '../context/CartContext'
import { useDiscount } from '../context/DiscountContext'
import { useFavorites } from '../context/FavoritesContext'
import { useTheme } from '../context/ThemeContext';
import ProductCard from '../components/ProductCard'
import axios from '../lib/axios'

const LARAVEL_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

function resolveImage(path) {
  if (!path) return null
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  return LARAVEL_URL + (path.startsWith('/') ? path : '/' + path)
}

const mockRelated = Array(5).fill(null).map((_, i) => ({
  id: 200 + i, name: 'PC BUILD', price: 3199 + i * 50, image: null,
}))

function Stars({ rating = 0 }) {
  return (
    <div className="flex items-center gap-1">
      {[1,2,3,4,5].map(s => (
        <svg key={s} className="w-4 h-4" viewBox="0 0 20 20"
          fill={s <= Math.round(rating) ? '#F97316' : 'none'}
          stroke="#F97316" strokeWidth={1.5}>
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
        </svg>
      ))}
      {rating > 0 && <span style={{ fontSize: 13, color: '#aaa', marginLeft: 4 }}>({Number(rating).toFixed(1)})</span>}
    </div>
  )
}

export default function ProductDetailPage() {
  const { id } = useParams()
  const { addItem } = useCart()
  const { dark } = useTheme()
  const { toggleFavorite, isFavorite } = useFavorites()
  const { discount, getItemDiscounts, bestDiscountForItem, calcDiscount } = useDiscount()
  const [product, setProduct] = useState(null)
  const [related, setRelated] = useState([])
  const [imgIdx, setImgIdx]   = useState(0)
  const [qty, setQty]         = useState(1)
  const [added, setAdded]     = useState(false)

  useEffect(() => {
    setImgIdx(0); setQty(1); setAdded(false)
    axios.get(`/api/products/${id}`)
      .then(res => {
        const p       = res.data?.data ?? res.data
        const relData = res.data?.related ?? []

        if (Array.isArray(p.all_images) && p.all_images.length > 0) {
          p.images = p.all_images
        }

        setProduct(p)
        setRelated(
          relData.length > 0
            ? relData.map(r => ({
                ...r,
                // use all_images if present, else fall back to image string
                image: (r.all_images?.[0]) ?? r.image
              }))
            : mockRelated
        )
      })
      .catch(() => {
        setProduct({ id, name: 'PC BUILD BUDGET 3K FOR GAMING', price: 3000, description: 'AMD RYZEN 9950X3D\nRTX5080', stock: 10, rating: 4.5, brand: 'AMD', category: 'PC BUILD', image: null })
        setRelated(mockRelated)
      })
  }, [id])

  if (!product) return (
    <div className="flex justify-center py-20">
      <div className="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin"/>
    </div>
  )

  // Use all_images (synced from model accessor above), fall back to single image
  const rawImages = (product.images?.length ? product.images : (product.image ? [product.image] : [null]))
  const images    = rawImages.map(img => resolveImage(img))
  const inStock   = (product.stock ?? 99) > 0
  const maxQty    = product.stock ?? 99

  // ── Discount calculation for this product ──────────────────────────────────
  const itemDiscounts     = product ? getItemDiscounts(product) : []
  const productDiscounted = itemDiscounts.length > 0
  const bestDiscount      = product ? bestDiscountForItem(product) : null
  const singleDiscount    = bestDiscount && product
    ? (bestDiscount.type === 'percentage'
        ? product.price * bestDiscount.value / 100
        : Math.min(bestDiscount.value, product.price))
    : 0
  const discountedPrice   = Math.max(0, (product?.price ?? 0) - singleDiscount)

  function handleAddToCart() {
    for (let i = 0; i < qty; i++) addItem(product)
    setAdded(true)
    setTimeout(() => setAdded(false), 2000)
  }

  return (
    <div className="max-w-[1280px] mx-auto px-4 py-8 bg-white dark:bg-gray-900" style={{ background: dark ? '#111827' : '#fff', minHeight: '60vh' }}>

      {/* Breadcrumb */}
      <div className="flex items-center gap-2 flex-wrap mb-6 text-gray-400" style={{ fontSize: 13 }}>
        <a href="/" className="hover:text-primary">Home</a>
        {product.category && <>
          <span>›</span>
          <a href={`/category/${product.category?.toLowerCase()}`} className="hover:text-primary capitalize">{product.category}</a>
        </>}
        <span>›</span>
        <span className="text-gray-700 font-semibold">{product.name}</span>
      </div>

      {/* Main card */}
      <div className="flex flex-col md:flex-row gap-8 mb-12 rounded-2xl p-6 shadow-sm" style={{ background: dark ? '#1f2937' : '#f9fafb' }}>

        {/* Image gallery */}
        <div className="flex-1 flex flex-col gap-3">
          <div className="relative flex items-center justify-center rounded-xl overflow-hidden" style={{ height: 320, background: dark ? '#111827' : '#fff' }}>
            {images[imgIdx] ? (
              <img src={images[imgIdx]} alt={product.name}
                className="max-h-72 max-w-full object-contain transition-opacity duration-300"
                onError={e => { e.target.style.display = 'none' }} />
            ) : (
              <div className="flex flex-col items-center text-gray-600">
                <svg className="w-16 h-16 mb-2" fill="none" stroke="currentColor" strokeWidth={1} viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 19.5h18M3 4.5h18"/>
                </svg>
                <span style={{ fontSize: 13 }}>No image</span>
              </div>
            )}

            {/* Discount badges on image — one per active discount, respects badge_config */}
            {productDiscounted && (
              <div className="absolute top-3 left-3 flex flex-col gap-1.5" style={{ zIndex: 2 }}>
                {itemDiscounts.map((d, idx) => {
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
                  const shadowStyle = bc ? {} : d.source === 'public'
                    ? { boxShadow: '0 3px 12px rgba(124,58,237,0.55)' }
                    : { boxShadow: '0 3px 12px rgba(249,115,22,0.55)' }

                  return (
                    <div key={idx}>
                      <div className="flex items-center gap-1.5 font-black rounded-full shadow-lg"
                        style={{
                          fontSize: 13, letterSpacing: 0.5, padding: '5px 14px',
                          color: badgeColor, ...bgStyle, ...shadowStyle,
                        }}>
                        {badgeIcon} {badgeText}
                        {d.source === 'code' && !bc && (
                          <span style={{ fontSize: 10, opacity: 0.8 }}> ({d.code})</span>
                        )}
                      </div>
                      {/* Savings sub-badge — skip when admin set a custom badge text */}
                      {!bc && (
                        <div className="font-black rounded-full mt-1"
                          style={{
                            fontSize: 11, padding: '3px 12px',
                            background: 'rgba(34,197,94,0.18)', color: '#22c55e',
                            border: '1px solid rgba(34,197,94,0.4)', width: 'fit-content',
                          }}>
                          save ${(d.type === 'percentage'
                            ? product.price * d.value / 100
                            : Math.min(d.value, product.price)).toFixed(2)} each
                        </div>
                      )}
                    </div>
                  )
                })}
              </div>
            )}

            {images.length > 1 && (<>
              <button onClick={() => setImgIdx(i => Math.max(0, i-1))}
                className="absolute left-2 top-1/2 -translate-y-1/2 bg-white rounded-full w-9 h-9 flex items-center justify-center shadow hover:text-primary text-xl z-10">‹</button>
              <button onClick={() => setImgIdx(i => Math.min(images.length-1, i+1))}
                className="absolute right-2 top-1/2 -translate-y-1/2 bg-white rounded-full w-9 h-9 flex items-center justify-center shadow hover:text-primary text-xl z-10">›</button>
            </>)}
          </div>
          {images.length > 1 && (
            <div className="flex gap-2 overflow-x-auto pb-1">
              {images.map((img, i) => (
                <button key={i} onClick={() => setImgIdx(i)}
                  className={`flex-shrink-0 w-16 h-16 rounded-lg border-2 overflow-hidden bg-white transition-all ${imgIdx===i ? 'border-primary' : 'border-gray-200 opacity-60 hover:opacity-100'}`}>
                  {img ? <img src={img} alt="" className="w-full h-full object-contain"/>
                       : <div className="w-full h-full flex items-center justify-center text-gray-300 text-xl">📦</div>}
                </button>
              ))}
            </div>
          )}
        </div>

        {/* Info */}
        <div className="flex-1 flex flex-col">
          {product.brand && (
            <div className="font-semibold mb-1 tracking-widest uppercase" style={{ fontSize: 15, color: dark ? '#9ca3af' : '#6b7280' }}>{product.brand}</div>
          )}
          <h1 className="text-primary font-black text-gray-900 mb-2 leading-tight"
            style={{ fontFamily: 'HurstBagod, Rajdhani, sans-serif', fontSize: 'clamp(20px,3vw,30px)' }}>
            {product.name}
          </h1>
          {product.rating > 0 && <div className="mb-3"><Stars rating={product.rating}/></div>}

          {/* Price with discount */}
          <div className="mb-4">
            {productDiscounted ? (
              <div className="flex items-end gap-3 flex-wrap">
                <div className="text-primary font-black" style={{ fontSize: 32 }}>
                  ${discountedPrice.toFixed(2)}
                </div>
                <div className="text-gray-400 line-through" style={{ fontSize: 20 }}>
                  ${Number(product.price).toFixed(2)}
                </div>
              </div>
            ) : (
              <div className="text-primary font-black" style={{ fontSize: 32 }}>
                ${Number(product.price).toFixed(2)}
              </div>
            )}

            {/* Active discount info banners — one per matching discount */}
            {productDiscounted && itemDiscounts.map((d, idx) => (
              <div key={idx} className="mt-3 flex items-center gap-3 rounded-xl px-4 py-3 flex-wrap"
                style={{
                  background: d.source === 'public'
                    ? 'linear-gradient(135deg, rgba(124,58,237,0.1) 0%, rgba(109,40,217,0.06) 100%)'
                    : 'linear-gradient(135deg, rgba(249,115,22,0.1) 0%, rgba(234,88,12,0.06) 100%)',
                  border: `1px solid ${d.source === 'public' ? 'rgba(124,58,237,0.35)' : 'rgba(249,115,22,0.35)'}`,
                }}>
                <span className="text-3xl" style={{ lineHeight: 1 }}>🏷</span>
                <div>
                  <div className="font-black" style={{
                    fontSize: 14,
                    color: d.source === 'public' ? '#a78bfa' : '#F97316',
                    letterSpacing: 0.5,
                  }}>
                    {d.source === 'code'
                      ? <>Code <span style={{ fontFamily: 'monospace', letterSpacing: 2, fontSize: 15 }}>{d.code}</span> — </>
                      : <>Sale — </>}
                    {d.type === 'percentage' ? `${d.value}% OFF` : `$${Number(d.value).toFixed(2)} OFF`}
                  </div>
                  <div className="font-semibold" style={{ fontSize: 12, color: '#22c55e' }}>
                    You save ${(d.type === 'percentage'
                      ? product.price * d.value / 100
                      : Math.min(d.value, product.price)).toFixed(2)} on this item
                    {d.categories?.length > 0 && (
                      <span style={{ color: 'rgba(249,115,22,0.7)', marginLeft: 6 }}>
                        · applies to {d.categories.join(', ')}
                      </span>
                    )}
                  </div>
                </div>
              </div>
            ))}
            {discount && !productDiscounted && (
              <div className="mt-2 inline-flex items-center gap-2 rounded-full px-4 py-1.5"
                style={{ background: dark ? 'rgba(255,255,255,0.04)' : '#f9fafb', border: `1px solid ${dark ? '#374151' : '#e5e7eb'}` }}>
                <span style={{ fontSize: 12, color: dark ? '#6b7280' : '#9ca3af' }}>
                  Code <strong>{discount.code}</strong> doesn't apply to this category
                </span>
              </div>
            )}
          </div>

          {product.description && (
            <p className="whitespace-pre-line mb-5 leading-relaxed" style={{ fontSize: 15, color: dark ? '#d1d5db' : '#4b5563' }}>
              {product.description}
            </p>
          )}
          <div className="flex items-center gap-2 mb-5">
            <span className={`w-2.5 h-2.5 rounded-full ${inStock ? 'bg-green-500' : 'bg-red-400'}`}/>
            <span className={`font-bold text-sm ${inStock ? 'text-green-600' : 'text-red-500'}`}>
              {inStock ? `In Stock${product.stock ? ` (${product.stock} left)` : ''}` : 'Out of Stock'}
            </span>
          </div>
          
          <div className="flex items-center gap-3 flex-wrap mt-auto">
            <div className="flex items-center rounded-lg overflow-hidden" style={{ border: `1px solid ${dark ? '#4b5563' : '#d1d5db'}` }}>
              <button onClick={() => setQty(q => Math.max(1,q-1))}
                className="w-10 h-10 flex items-center justify-center font-bold text-lg transition-colors"
                style={{ color: dark ? '#9ca3af' : '#4b5563', background: dark ? '#374151' : '#f3f4f6' }}>−</button>
              <span className="w-10 text-center font-bold" style={{ fontSize: 16, color: dark ? '#f9fafb' : '#6b7280', background: dark ? '#1f2937' : '#fff' }}>{qty}</span>
              <button onClick={() => setQty(q => Math.min(maxQty,q+1))}
                className="w-10 h-10 flex items-center justify-center font-bold text-lg transition-colors"
                style={{ color: dark ? '#9ca3af' : '#4b5563', background: dark ? '#374151' : '#f3f4f6' }}>+</button>
            </div>
            <button onClick={handleAddToCart} disabled={!inStock}
              className={`flex-1 font-bold py-3 px-8 rounded-lg transition-all text-white flex items-center justify-center gap-2
                ${inStock ? added ? 'bg-green-500' : 'bg-primary hover:bg-orange-600 hover:scale-[1.02]' : 'bg-gray-300 cursor-not-allowed'}`}
              style={{ fontFamily: 'Rajdhani, sans-serif', fontSize: 16, letterSpacing: 1 }}>
              {added ? '✓ ADDED!' : '🛒 ADD TO CART'}
            </button>

            {/* Favorite button */}
            <button
              onClick={() => toggleFavorite(product)}
              title={isFavorite(product.id) ? 'Remove from favorites' : 'Add to favorites'}
              className={`w-12 h-12 flex-shrink-0 flex items-center justify-center rounded-lg border-2 transition-all hover:scale-110 active:scale-95
                ${isFavorite(product.id)
                  ? 'border-primary bg-orange-50'
                  : 'border-gray-300 bg-white hover:border-primary'}`} style={{ background: dark ? '#111827' : '#fff' }}
            >
              <svg className="w-6 h-6 transition-all" viewBox="0 0 24 24"
                fill={isFavorite(product.id) ? '#F97316' : 'none'}
                stroke={isFavorite(product.id) ? '#F97316' : '#9ca3af'}
                strokeWidth={2}>
                <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
              </svg>
            </button>
          </div>

          {/* Qty total with discount */}
          {productDiscounted && qty > 1 && (
            <div className="mt-3 p-3 rounded-xl flex justify-between items-center"
              style={{ background: dark ? 'rgba(22,163,74,0.1)' : '#f0fdf4', border: `1px solid ${dark ? 'rgba(22,163,74,0.3)' : '#bbf7d0'}` }}>
              <span className="font-semibold" style={{ fontSize: 14, color: dark ? '#86efac' : '#15803d' }}>Total for {qty} item{qty > 1 ? 's' : ''}</span>
              <span className="font-black" style={{ fontSize: 18, color: dark ? '#4ade80' : '#15803d' }}>${(discountedPrice * qty).toFixed(2)}</span>
            </div>
          )}
        </div>
      </div>

      {/* Related */}
      {related.length > 0 && (
        <div>
          <div className="flex items-center gap-4 mb-6">
            <div className="flex-1 h-px" style={{ background: dark ? '#374151' : '#e5e7eb' }}/>
            <h2 className="font-black tracking-widest whitespace-nowrap"
              style={{ fontFamily: 'Rajdhani, sans-serif', fontSize: 18, color: dark ? '#f9fafb' : '#374151' }}>
              MORE {product.category ? product.category.toUpperCase() : 'RELATED PRODUCTS'}
            </h2>
            <div className="flex-1 h-px" style={{ background: dark ? '#374151' : '#e5e7eb' }}/>
          </div>
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            {related.map((r, i) => <ProductCard key={r.id || i} product={r}/>)}
          </div>
          {product.category && (
            <div className="flex justify-end mt-4">
              <a href={`/category/${product.category.toLowerCase().replace(' ', '-')}`}
                className="text-primary font-bold hover:underline" style={{ fontSize: 15 }}>
                View all {product.category} →
              </a>
            </div>
          )}
        </div>
      )}
    </div>
  )
}
