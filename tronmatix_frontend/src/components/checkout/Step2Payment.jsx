// src/components/checkout/Step2Payment.jsx
import { useTheme } from "../../context/ThemeContext"
import { useLang } from "../../context/LanguageContext"
import DiscountInput from "../DiscountInput"

export default function Step2Payment({
  payMethod, onPayMethod, items, subtotal, discountAmount,
  discount, finalTotal, loading, onBack, onPlace,
  isPickup,  // ← NEW
}) {
  const { dark } = useTheme()
  const { t, isKhmer } = useLang()
  const btnFont = isKhmer ? "KantumruyPro, Khmer OS, sans-serif" : "Rajdhani, sans-serif"

  const c = {
    heading:       dark ? '#f9fafb' : '#1f2937',
    text:          dark ? '#f9fafb' : '#1f2937',
    textMuted:     dark ? '#9ca3af' : '#6b7280',
    cardBorder:    dark ? '#374151' : '#e5e7eb',
    cardSelBg:     dark ? 'rgba(249,115,22,0.10)' : '#fff7ed',
    bakongBg:      dark ? 'rgba(37,99,235,0.12)' : '#eff6ff',
    bakongBorder:  dark ? 'rgba(37,99,235,0.30)' : '#bfdbfe',
    bakongText:    dark ? '#93c5fd' : '#1d4ed8',
    summaryBg:     dark ? '#111827' : '#f9fafb',
    summaryBorder: dark ? '#374151' : '#e5e7eb',
    summaryHead:   dark ? '#d1d5db' : '#374151',
    divider:       dark ? '#374151' : '#e5e7eb',
    itemName:      dark ? '#d1d5db' : '#4b5563',
    itemQty:       dark ? '#6b7280' : '#9ca3af',
    itemPrice:     dark ? '#f9fafb' : '#1f2937',
    backBorder:    dark ? '#374151' : '#d1d5db',
    backText:      dark ? '#d1d5db' : '#374151',
    backHoverBg:   dark ? '#374151' : '#f3f4f6',
    pickupBg:      dark ? 'rgba(34,197,94,0.08)' : '#f0fdf4',
    pickupBorder:  dark ? 'rgba(34,197,94,0.25)' : '#bbf7d0',
  }

  return (
    <div>
      <h2 className="font-black mb-5" style={{ fontSize: 20, color: c.heading }}>
        {isKhmer ? t("checkout.selectPayment") : "Select Payment Method"}
      </h2>

      {/* Pickup reminder banner */}
      {isPickup && (
        <div className="mb-5 rounded-xl p-4 flex items-start gap-3"
          style={{ background: c.pickupBg, border: `1px solid ${c.pickupBorder}` }}>
          <span style={{ fontSize: 20 }}>🏪</span>
          <div>
            <p className="font-bold" style={{ fontSize: 14, color: '#22c55e' }}>{t("checkout.pickupReminderTitle")}</p>
            <p style={{ fontSize: 13, color: c.textMuted, marginTop: 2 }}>{t("checkout.pickupReminderHint")}</p>
          </div>
        </div>
      )}

      {/* Payment options */}
      <div className="space-y-3 mb-5">
        {[
          {
            val: "cash",
            emoji: "💵",
            title: isPickup
              ? (isKhmer ? t("checkout.cashPickupTitle") : "Pay at Store")
              : (isKhmer ? t("checkout.cashTitle") : "Cash on Delivery"),
            sub: isPickup
              ? (isKhmer ? t("checkout.cashPickupSub") : "Pay in cash when you pick up at our store")
              : (isKhmer ? t("checkout.cashSub") : "Pay with cash when you receive your order"),
          },
          {
            val: "bakong",
            emoji: "📱",
            title: "ABA BAKONG KHQR",
            sub: isKhmer ? t("checkout.bakongSub") : "Scan QR — auto-detected, instant confirmation",
          },
        ].map((m) => {
          const selected = payMethod === m.val
          return (
            <label
              key={m.val}
              className="flex items-center gap-4 p-4 border-2 rounded-xl cursor-pointer transition-all"
              style={{
                borderColor: selected ? '#F97316' : c.cardBorder,
                background:  selected ? c.cardSelBg : 'transparent',
              }}
            >
              <input
                type="radio" name="pay" value={m.val}
                checked={selected} onChange={() => onPayMethod(m.val)}
                className="accent-primary w-4 h-4"
              />
              <span style={{ fontSize: 26 }}>{m.emoji}</span>
              <div className="flex-1">
                <p className="font-black" style={{ fontSize: 16, color: c.text }}>{m.title}</p>
                <p style={{ fontSize: 13, color: c.textMuted }}>{m.sub}</p>
              </div>
              {m.val === "bakong" && (
                <div className="bg-blue-600 text-white rounded-lg px-2 py-1 font-black" style={{ fontSize: 11 }}>ABA</div>
              )}
            </label>
          )
        })}
      </div>

      {/* KHQR info banner */}
      {payMethod === "bakong" && (
        <div
          className="mb-5 rounded-xl p-4 flex items-start gap-3"
          style={{ background: c.bakongBg, border: `1px solid ${c.bakongBorder}` }}
        >
          <span style={{ fontSize: 20 }}>⚡</span>
          <p style={{ fontSize: 13, color: c.bakongText }}>
            {isKhmer
              ? t("checkout.khqrBanner")
              : <>{'After placing your order, a KHQR code will appear. This page '}<strong>automatically detects</strong>{'  your payment — no button needed.'}</>}
          </p>
        </div>
      )}

      {/* Discount code */}
      <div className="mb-5">
        <DiscountInput subtotal={subtotal} />
      </div>

      {/* Order summary */}
      <div
        className="rounded-xl p-5 mb-6"
        style={{ background: c.summaryBg, border: `1px solid ${c.summaryBorder}` }}
      >
        <h3 className="font-black mb-4" style={{ fontSize: 15, color: c.summaryHead }}>
          {isKhmer ? t("checkout.orderSummary") : "Order Summary"}
          {isPickup && <span className="ml-2 text-green-400 font-bold" style={{ fontSize: 12 }}>🏪 {isKhmer ? t("checkout.fulfillPickup") : "PICKUP"}</span>}
        </h3>
        <div className="space-y-2 mb-3">
          {items.map((item) => (
            <div key={item.id} className="flex justify-between" style={{ fontSize: 14 }}>
              <span style={{ color: c.itemName }}>
                {item.name}{" "}
                <span style={{ color: c.itemQty }}>×{item.qty}</span>
              </span>
              <span className="font-bold" style={{ color: c.itemPrice }}>
                ${(item.price * item.qty).toFixed(2)}
              </span>
            </div>
          ))}
        </div>
        <div className="pt-3 space-y-1.5" style={{ borderTop: `1px solid ${c.divider}` }}>
          <div className="flex justify-between" style={{ fontSize: 13, color: c.textMuted }}>
            <span>{isKhmer ? t("cart.subtotal").replace(" :", "") : "Subtotal"}</span>
            <span>${subtotal.toFixed(2)}</span>
          </div>
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
          <div
            className="flex justify-between font-black pt-1"
            style={{ fontSize: 19, borderTop: `1px solid ${c.divider}`, color: c.text }}
          >
            <span>{isKhmer ? t("cart.total").replace(" :", "") : "Total"}</span>
            <span className="text-primary">${finalTotal.toFixed(2)}</span>
          </div>
        </div>
      </div>

      {/* Navigation */}
      <div className="flex gap-3">
        <button
          onClick={onBack}
          className="flex-1 py-3 rounded-xl font-bold border-2 transition-colors"
          style={{ borderColor: c.backBorder, color: c.backText }}
          onMouseEnter={(e) => { e.currentTarget.style.background = c.backHoverBg }}
          onMouseLeave={(e) => { e.currentTarget.style.background = 'transparent' }}
        >{isKhmer ? `← ${t("common.back")}` : "← BACK"}</button>
        <button
          onClick={onPlace}
          disabled={loading}
          className="flex-1 bg-primary text-white font-bold py-3 rounded-xl hover:bg-orange-600 transition-colors disabled:opacity-50"
          style={{ fontFamily: btnFont, fontSize: 16, letterSpacing: isKhmer ? 0 : undefined }}
        >
          {loading
            ? (isKhmer ? t("checkout.placingOrder") : "PLACING ORDER…")
            : isPickup
              ? t("checkout.placePickupOrder")
              : (isKhmer ? t("checkout.placeOrder") : "PLACE ORDER ✓")}
        </button>
      </div>
    </div>
  )
}