// src/components/checkout/Step2Payment.jsx
import { useEffect } from "react"
import { useTheme } from "../../context/ThemeContext"
import { useLang } from "../../context/LanguageContext"
import DiscountInput from "../DiscountInput"

export default function Step2Payment({
  payMethod, onPayMethod, items, subtotal, discountAmount,
  discount, finalTotal, loading, onBack, onPlace,
  isPickup, selectedProvince, deliveryFee, selectedProvider,
}) {
  const { dark } = useTheme()
  const { t, isKhmer } = useLang()
  const btnFont = isKhmer ? "Kh-Koulen, sans-serif" : "Rajdhani, sans-serif"

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

  // Delivery outside Phnom Penh requires KHQR only (no cash on delivery)
  const provinceName = selectedProvince?.name_en || selectedProvince?.name || ''
  const isOutOfPhnomPenh = !isPickup && !!selectedProvince &&
    !/phnom penh|phnompenh/i.test(provinceName)

  // Auto-switch to bakong when a non-PhnomPenh province is selected
  const effectivePayMethod = isOutOfPhnomPenh ? 'bakong' : payMethod

  // Keep the parent (CheckoutPage) payMethod in sync so the order is actually
  // submitted as KHQR for inter-province delivery — not just shown as selected.
  useEffect(() => {
    if (isOutOfPhnomPenh && payMethod !== 'bakong') {
      onPayMethod('bakong')
    }
  }, [isOutOfPhnomPenh, payMethod, onPayMethod])

  const TELEGRAM_URL = "https://t.me/+VZScFi_U95PsFk0M"

  return (
    <div>
      <h2 className="font-black mb-5" style={{ fontSize: 20, color: c.heading }}>
        {isKhmer ? t("checkout.selectPayment") : "Select Payment Method"}
      </h2>

      {/* Out-of-PhnomPenh notice — KHQR only */}
      {isOutOfPhnomPenh && (
        <div className="mb-4 rounded-xl p-4 flex items-start gap-3"
          style={{ background: dark ? 'rgba(245,158,11,0.10)' : '#fffbeb', border: `1px solid ${dark ? 'rgba(245,158,11,0.30)' : '#fde68a'}` }}>
          <span style={{ fontSize: 20 }}>🚚</span>
          <div>
            <p className="font-bold" style={{ fontSize: 14, color: '#d97706' }}>
              {isKhmer ? 'ការដឹកជញ្ជូនក្រៅក្រុងភ្នំពេញ' : 'Inter-province delivery'}
            </p>
            <p style={{ fontSize: isKhmer ? 12 : 13, color: c.textMuted, marginTop: 3, lineHeight: 1.5 }}>
              {isKhmer
                ? 'សូមបង់តាមរយះ ABA KHQR (QR Code) ប៉ុណ្ណោះ។ ទូទៅយើងមិនទទួលខាតប៉ាក់បន្ទាប់បន្ទាប់ទេ។'
                : 'For orders outside Phnom Penh, payment is KHQR only. We do not accept cash-on-delivery for inter-province shipments.'}
            </p>
          </div>
        </div>
      )}

      {/* Pickup reminder banner */}
      {isPickup && (
        <div className="mb-5 rounded-xl p-4 flex items-start gap-3"
          style={{ background: c.pickupBg, border: `1px solid ${c.pickupBorder}` }}>
          <span style={{ fontSize: 20 }}>🏪</span>
          <div>
            <p className="font-bold" style={{ fontSize: 14, color: '#22c55e' }}>{t("checkout.pickupReminderTitle")}</p>
            <p style={{ fontSize: isKhmer ? 13 : 14, color: c.textMuted, marginTop: 2 }}>{t("checkout.pickupReminderHint")}</p>
          </div>
        </div>
      )}

      {/* Payment options */}
      <div className="space-y-3 mb-2">
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
          const selected = effectivePayMethod === m.val
          const disabled = isOutOfPhnomPenh && m.val === 'cash'
          return (
            <label
              key={m.val}
              className="flex items-center gap-4 p-4 border-2 rounded-xl cursor-pointer transition-all"
              style={{
                borderColor: selected ? '#F97316' : (disabled ? 'transparent' : c.cardBorder),
                background:  selected ? c.cardSelBg : (disabled ? (dark ? 'rgba(255,255,255,0.02)' : '#fafafa') : 'transparent'),
                opacity: disabled ? 0.5 : 1,
              }}
            >
              <input
                type="radio" name="pay" value={m.val}
                checked={selected} onChange={() => !disabled && onPayMethod(m.val)}
                className="accent-primary w-4 h-4"
                disabled={disabled}
              />
              <span style={{ fontSize: 26 }}>{m.emoji}</span>
              <div className="flex-1">
                <p className="font-black" style={{ fontSize: 16, color: c.text }}>{m.title}</p>
                <p style={{ fontSize: isKhmer ? 13 : 14, color: c.textMuted }}>
                  {disabled
                    ? (isKhmer ? 'មិនអាចប្រើបានសម្រាប់ការដឹកជញ្ជូនក្រៅក្រុង' : 'Not available for inter-province delivery')
                    : m.sub}
                </p>
              </div>
              {m.val === "bakong" && !disabled && (
                <div className="bg-blue-600 text-white rounded-lg px-2 py-1 font-black" style={{ fontSize: 11 }}>ABA</div>
              )}
            </label>
          )
        })}
      </div>

      {/* Telegram chat button — always visible, highlighted when KHQR is selected */}
      {/* <a
        href={TELEGRAM_URL}
        target="_blank"
        rel="noopener noreferrer"
        className="flex items-center gap-3 rounded-xl p-4 mb-5 transition-all"
        style={{
          background: effectivePayMethod === 'bakong'
            ? (dark ? 'rgba(34,197,53,0.12)' : '#f0fdf4')
            : (dark ? 'rgba(255,255,255,0.04)' : '#f9fafb'),
          border: `2px solid ${effectivePayMethod === 'bakong' ? (dark ? 'rgba(34,197,53,0.35)' : '#bbf7d0') : c.cardBorder}`,
        }}
      >
        <div style={{
          width: 42, height: 42, borderRadius: '50%',
          background: effectivePayMethod === 'bakong' ? '#22c55e' : '#229ED9',
          display: 'flex', alignItems: 'center', justifyContent: 'center',
          flexShrink: 0,
        }}>
          <svg width="22" height="22" viewBox="0 0 24 24" fill="#fff">
            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
          </svg>
        </div>
        <div className="flex-1">
          <p className="font-black" style={{ fontSize: 15, color: c.text }}>
            {isKhmer ? 'ជជែកជាមួយយើងតាម Telegram' : 'Chat with us on Telegram'}
          </p>
          <p style={{ fontSize: 12, color: c.textMuted, marginTop: 2 }}>
            {isKhmer ? 'មានបញ្ហា? ផ្ញើសារទៅកាន់យើងហើយយើងនឹងជួយអ្នក។' : 'Questions? Send us a message and we will help you right away.'}
          </p>
        </div>
        <div style={{
          padding: '6px 14px', borderRadius: 999,
          background: effectivePayMethod === 'bakong' ? '#22c55e' : '#229ED9',
          color: '#fff', fontWeight: 800, fontSize: 12, letterSpacing: 0.5, flexShrink: 0,
          fontFamily: isKhmer ? "Kh-Koulen, sans-serif" : "Rajdhani, sans-serif",
        }}>
          {isKhmer ? 'ចូលជាមួយ' : 'OPEN CHAT'}
        </div>
      </a> */}

      {/* KHQR info banner */}
      {payMethod === "bakong" && (
        <div
          className="mb-5 rounded-xl p-4 flex items-start gap-3"
          style={{ background: c.bakongBg, border: `1px solid ${c.bakongBorder}` }}
        >
          <span style={{ fontSize: 20 }}>⚡</span>
          <p style={{ fontSize: isKhmer ? 13 : 14, color: c.bakongText }}>
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
        <h2 className="font-black mb-4" style={{ fontSize: 15, color: c.summaryHead }}>
          {isKhmer ? t("checkout.orderSummary") : "Order Summary"}
          {isPickup && <span className="ml-2 text-green-400 font-bold" style={{ fontSize: isKhmer ? 12 : 13 }}>🏪 {isKhmer ? t("checkout.fulfillPickup") : "PICKUP"}</span>}
        </h2>
        <div className="space-y-2 mb-3">
          {items.map((item) => (
            <div key={item.id} className="flex justify-between" style={{ fontSize: 14 }}>
              <span style={{ color: c.itemName }}>
                {item.name}{" "}
                {item.brand && (
                  <span style={{ color: "#F97316", fontWeight: 700, fontSize: 12 }}>· {item.brand}</span>
                )}
                <span style={{ color: c.itemQty }}>×{item.qty}</span>
                {item.warranty && (
                  <div className="text-[13px] font-bold mt-0.5" style={{ color: "#F97316" }}>
                    🛡 {t("orders.warrantyLabel")}: {item.warranty}
                  </div>
                )}
              </span>
              <span className="font-bold" style={{ color: c.itemPrice }}>
                ${(item.price * item.qty).toFixed(2)}
              </span>
            </div>
          ))}
        </div>
        <div className="pt-3 space-y-1.5" style={{ borderTop: `1px solid ${c.divider}` }}>
          <div className="flex justify-between" style={{ fontSize: isKhmer ? 13 : 14, color: c.textMuted }}>
            <span>{isKhmer ? t("cart.subtotal").replace(" :", "") : "Subtotal"}</span>
            <span>${subtotal.toFixed(2)}</span>
          </div>
          {discountAmount > 0 && (
            <div className="flex justify-between font-bold text-green-500" style={{ fontSize: isKhmer ? 13 : 14 }}>
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
          {!isPickup && selectedProvider && (
            <div className="flex justify-between" style={{ fontSize: isKhmer ? 13 : 14, color: c.textMuted }}>
              <span>
                🚚 Delivery · {selectedProvider.name}
              </span>
              <span>
                {selectedProvider.fee != null
                  ? `$${deliveryFee.toFixed(2)}`
                  : <span style={{ color: "#d97706" }}>Fee varies</span>}
              </span>
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