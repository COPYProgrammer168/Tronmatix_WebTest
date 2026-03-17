import { useState } from 'react'
import { useDiscount } from '../context/DiscountContext'
import { useTheme } from '../context/ThemeContext'

export default function DiscountInput({ subtotal }) {
  const { discount, loading, error, success, applyDiscount, removeDiscount } = useDiscount()
  const { dark } = useTheme()
  const [code, setCode] = useState('')

  const bg     = dark ? '#1f2937' : '#fff'
  const border = dark ? '#374151' : '#d1d5db'
  const text   = dark ? '#f9fafb' : '#1f2937'
  const label  = dark ? '#9ca3af' : '#4b5563'
  const btnBg  = dark ? '#374151' : '#1f2937'

  async function handleApply(e) {
    e.preventDefault()
    if (!code.trim()) return
    const result = await applyDiscount(code, subtotal)
    if (result) setCode('')
  }

  if (discount) {
    return (
      <div className="flex items-center justify-between rounded-xl px-4 py-3"
        style={{ background: dark ? 'rgba(22,163,74,0.1)' : '#f0fdf4', border: '1px solid rgba(22,163,74,0.3)' }}>
        <div className="flex items-center gap-2">
          <span className="text-green-500 font-black" style={{ fontSize: 16 }}>🏷 {discount.code}</span>
          <span className="text-green-600 font-bold" style={{ fontSize: 14 }}>
            {discount.type === 'percentage' ? `−${discount.value}%` : `−$${Number(discount.value).toFixed(2)}`}
          </span>
          <span className="text-green-500" style={{ fontSize: 12 }}>applied!</span>
        </div>
        <button onClick={removeDiscount} className="text-red-400 hover:text-red-600 font-bold text-lg leading-none" title="Remove discount">✕</button>
      </div>
    )
  }

  return (
    <div>
      <label className="block font-bold mb-1" style={{ fontSize: 13, letterSpacing: 1, color: label }}>
        DISCOUNT CODE
      </label>
      <form onSubmit={handleApply} className="flex gap-2">
        <input
          value={code}
          onChange={e => setCode(e.target.value.toUpperCase())}
          placeholder="Enter coupon code"
          className="flex-1 rounded-lg px-4 py-2.5 font-bold uppercase focus:outline-none transition-colors"
          style={{
            fontSize: 14, letterSpacing: 1,
            background: bg, color: text,
            border: `1px solid ${error ? '#ef4444' : success ? '#22c55e' : border}`,
          }}
        />
        <button type="submit" disabled={loading || !code.trim()}
          className="px-5 py-2.5 text-white font-bold rounded-lg hover:bg-primary transition-colors disabled:opacity-50"
          style={{ fontSize: 14, background: btnBg }}>
          {loading ? '…' : 'APPLY'}
        </button>
      </form>
      {error   && <p className="text-red-500 font-semibold mt-1" style={{ fontSize: 13 }}>⚠ {error}</p>}
      {success && <p className="text-green-500 font-semibold mt-1" style={{ fontSize: 13 }}>✓ {success}</p>}
    </div>
  )
}
