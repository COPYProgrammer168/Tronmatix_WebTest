import { Link } from 'react-router-dom'
import { useFavorites } from '../context/FavoritesContext'
import { useCart } from '../context/CartContext'
import { useTheme } from '../context/ThemeContext'
import { useLang } from '../context/LanguageContext'
import { resolveImage } from '../lib/resolveImage'

export default function FavoritesPage() {
  const { favorites, toggleFavorite } = useFavorites()
  const { addItem } = useCart()
  const { dark } = useTheme()
  const { t, isKhmer } = useLang()
  const headingFont = isKhmer ? 'Kh_Jrung_Thom, Khmer OS, sans-serif' : 'Rajdhani, sans-serif'
  const bodyFont    = isKhmer ? 'KantumruyPro, Khmer OS, sans-serif' : 'Rajdhani, sans-serif'

  const bg      = dark ? '#111827' : '#fff'
  const cardBg  = dark ? '#1f2937' : '#fff'
  const border  = dark ? '#374151' : '#e5e7eb'
  const text    = dark ? '#f9fafb' : '#1f2937'
  const textSub = dark ? '#9ca3af' : '#9ca3af'
  const imgBg   = dark ? '#111827' : '#f9fafb'
  const btnBg   = dark ? '#374151' : '#1f2937'

  return (
    <div className="max-w-[1280px] mx-auto px-4 py-8" style={{ background: bg, minHeight: '60vh' }}>
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center gap-3">
          <svg className="w-8 h-8 text-primary" fill="#F97316" viewBox="0 0 24 24">
            <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
          </svg>
          <h1 className="font-black"
            style={{ fontFamily: headingFont, fontSize: 22, color: text }}>
            {t('favorites.title')}
          </h1>
          <span className="bg-primary text-white rounded-full px-3 py-0.5 font-bold" style={{ fontSize: 13 }}>
            {favorites.length}
          </span>
        </div>
        <Link to="/" className="text-primary font-bold hover:underline" style={{ fontFamily: bodyFont, fontSize: 16 }}>
          {t('favorites.continueShopping')}
        </Link>
      </div>

      {favorites.length === 0 ? (
        <div className="text-center py-24">
          <svg className="w-20 h-20 mx-auto mb-4" fill="none"
            stroke={dark ? '#374151' : '#e5e7eb'} strokeWidth={1.5} viewBox="0 0 24 24">
            <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
          </svg>
          <h3 className="font-black mb-2"
            style={{ fontFamily: bodyFont, fontSize: 22, color: textSub }}>
            {t('favorites.empty')}
          </h3>
          <p className="mb-6" style={{ fontFamily: bodyFont, fontSize: 15, color: textSub }}>
            {t('favorites.emptyHint')}
          </p>
          <Link to="/"
            className="bg-primary text-white font-bold px-8 py-3 rounded-xl hover:bg-orange-600 transition-colors"
            style={{ fontFamily: bodyFont, fontSize: 16 }}>
            {t('favorites.browse')}
          </Link>
        </div>
      ) : (
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
          {favorites.map(product => (
            <div key={product.id}
              className="rounded-lg overflow-hidden hover:shadow-xl transition-shadow relative flex flex-col group"
              style={{ border: `1px solid ${border}`, background: cardBg }}>
              <button onClick={() => toggleFavorite(product)}
                className="absolute top-2 right-2 z-10 w-8 h-8 flex items-center justify-center rounded-full shadow-md hover:scale-110 transition-transform"
                style={{ background: cardBg }}>
                <svg className="w-5 h-5" fill="#F97316" stroke="#F97316" strokeWidth={2} viewBox="0 0 24 24">
                  <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                </svg>
              </button>
              <Link to={`/product/${product.id}`} className="block">
                <div className="flex items-center justify-center" style={{ height: 150, background: imgBg }}>
                  <img
                    src={resolveImage(Array.isArray(product.images) ? product.images[0] : product.image) || '/placeholder.png'}
                    alt={product.name}
                    className="h-28 object-contain group-hover:scale-105 transition-transform duration-300"
                    onError={e => { e.target.src = '/placeholder.png' }}
                  />
                </div>
              </Link>
              <div className="p-3 text-center flex flex-col flex-1">
                <Link to={`/product/${product.id}`}>
                  <h3 className="font-bold mb-1 hover:text-primary transition-colors"
                    style={{ fontFamily: headingFont, fontSize: 14, color: text }}>
                    {product.name}
                  </h3>
                </Link>
                <div className="text-primary font-bold mb-3" style={{ fontSize: 16 }}>
                  {product.price ? `$${Number(product.price).toFixed(2)}` : '$$$'}
                </div>
                <button onClick={() => addItem(product)}
                  className="mt-auto w-full text-white py-2 rounded font-bold hover:bg-primary transition-colors"
                  style={{ fontFamily: bodyFont, fontSize: 13, letterSpacing: 1, background: btnBg }}>
                  {t('favorites.addToCart')}
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}