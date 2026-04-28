// src/pages/CheckoutPage.jsx
import { useState, useEffect, useRef } from "react"
import { useNavigate } from "react-router-dom"
import { useCart }      from "../context/CartContext"
import { useAuth }      from "../context/AuthContext"
import { useLocation2 } from "../context/LocationContext"
import { useDiscount }  from "../context/DiscountContext"
import { useTheme }     from "../context/ThemeContext"
import { useLang }      from "../context/LanguageContext"
import axios from "../lib/axios"
import Swal  from "sweetalert2"
import AuthModal from "../components/AuthModal"

import Step1DeliveryInfo    from "../components/checkout/Step1DeliveryInfo"
import Step2Payment         from "../components/checkout/Step2Payment"
import OrderReceipt         from "../components/checkout/OrderReceipt"
import LocationPickerModal  from "../components/checkout/LocationPickerModal"
import BakongQRPanel        from "../components/orders/BakongQRPanel"

const STEPS = ["Delivery Info", "Payment"]

// ── Tronmatix Computer store — real coordinates from Google Maps ──────────
const STORE_LAT     = 11.56298
const STORE_LNG     = 104.899518
const STORE_ADDRESS = "Near Sovannphumi School, Stop Tep Phan, 14 St 160, Phnom Penh, Cambodia"
const STORE_MAPS_URL = "https://goo.gl/maps/8q7eeNwZH5uz1YwZ8"

export default function CheckoutPage() {
  const { items, total, subtotal, clearCart } = useCart()
  const { user }                              = useAuth()
  const { savedLocation, saveLocation }       = useLocation2()
  const { discount, calcDiscount, removeDiscount } = useDiscount()
  const { dark } = useTheme()
  const { t, isKhmer } = useLang()
  const navigate = useNavigate()

  const [step,              setStep]           = useState(1)
  // ── NEW: fulfillment type ────────────────────────────────────────────────
  const [fulfillment,       setFulfillment]    = useState("delivery") // "delivery" | "pickup"

  const [location,       setLocation]       = useState({
    name: savedLocation?.name || user?.username || "", phone: savedLocation?.phone || "",
    address: savedLocation?.address || "", city: savedLocation?.city || "", note: savedLocation?.note || "",
  })
  const [saveAddr,       setSaveAddr]       = useState(false)
  const [payMethod,      setPayMethod]      = useState("cash")
  const [order,          setOrder]          = useState(null)
  const [loading,        setLoading]        = useState(false)
  const [deliveryStatus, setDeliveryStatus] = useState(0)
  const [showAuthModal,  setShowAuthModal]  = useState(false)
  const [authMode,       setAuthMode]       = useState("login")
  const [delivery,       setDelivery]       = useState({ date: "", timeSlot: "" })
  const [showQrModal,    setShowQrModal]    = useState(false)
  const [savedLocations, setSavedLocations] = useState([])
  const [showLocPicker,  setShowLocPicker]  = useState(false)
  const [mapPin,         setMapPin]         = useState(null)
  const [locationId,     setLocationId]     = useState(null)
  const pendingOrderAfterLogin              = useRef(false)

  const isPickup = fulfillment === "pickup"

  useEffect(() => {
    if (user && pendingOrderAfterLogin.current) {
      pendingOrderAfterLogin.current = false
      placeOrder()
    }
  }, [user]) // eslint-disable-line

  useEffect(() => {
    if (!user) return
    axios.get("/api/user/locations")
      .then((res) => {
        const list = Array.isArray(res.data?.data) ? res.data.data : Array.isArray(res.data) ? res.data : []
        setSavedLocations(list)
        if (!location.address) {
          const def = list.find((l) => l.is_default) || list[0]
          if (def) {
            setLocation({ name: def.name || "", phone: def.phone || "", address: def.address || "", city: def.city || "", note: def.note || "" })
            setLocationId(def.id)
            if (def.lat && def.lng) setMapPin({ lat: def.lat, lng: def.lng, address: def.map_address || def.address })
          }
        }
      }).catch(() => {})
  }, [user]) // eslint-disable-line

  const handleSaveToProfile = async (loc, isDefault = false) => {
    if (!user) throw new Error("Not logged in")
    await axios.post("/api/user/locations", {
      name: loc.name, phone: loc.phone, address: loc.address,
      city: loc.city || null, note: loc.note || null,
      is_default: isDefault,
    })
    const res = await axios.get("/api/user/locations")
    const list = Array.isArray(res.data?.data) ? res.data.data : Array.isArray(res.data) ? res.data : []
    setSavedLocations(list)
  }

  const discountAmount = calcDiscount(subtotal, items)
  const finalTotal     = Math.max(0, subtotal - discountAmount)

  const handleLocation = (e) => {
    setLocation((p) => ({ ...p, [e.target.name]: e.target.value }))
    setLocationId(null)
  }
  const handleSelectSavedLocation = (loc) => {
    setLocation({ name: loc.name || "", phone: loc.phone || "", address: loc.address || "", city: loc.city || "", note: loc.note || "" })
    setLocationId(loc.id)
    if (loc.lat && loc.lng) setMapPin({ lat: loc.lat, lng: loc.lng, address: loc.map_address || loc.address })
    else setMapPin(null)
  }

  const placeOrder = async () => {
    // For pickup: use store address in summary, skip delivery address validation
    const deliverTo = isPickup
      ? `🏪 Store Pickup`
      : `${location.name} · ${location.phone}`
    const deliverAddr = isPickup
      ? STORE_ADDRESS
      : `${location.address}${location.city ? `, ${location.city}` : ""}`

    const summaryHtml = `<div style="text-align:left;line-height:1.8;font-size:1rem;color:#1f2937;padding:0 12px;">
      <strong>Fulfillment:</strong> ${isPickup ? "🏪 Store Pickup" : "🚚 Delivery"}<br>
      <strong>Total:</strong> $${finalTotal.toFixed(2)}${discountAmount > 0 ? ` <span style="color:#16a34a">(−$${discountAmount.toFixed(2)} discount)</span>` : ""}<br>
      <strong>Payment:</strong> ${payMethod === "cash" ? "Cash on Delivery" : "ABA BAKONG KHQR"}<br>
      <strong>${isPickup ? "Pickup by" : "Deliver to"}:</strong> ${deliverTo}<br>
      <span style="color:#6b7280">${deliverAddr}</span>
      ${delivery.date ? `<br><span style="color:#F97316">📅 ${delivery.date}${delivery.timeSlot ? " · " + delivery.timeSlot : ""}</span>` : ""}
    </div>`

    if (!user) {
      const result = await Swal.fire({ title: "Login Required", html: summaryHtml + `<div style="margin-top:1rem;color:#4b5563;">Login to place this order.</div>`, icon: "info", showCancelButton: true, confirmButtonColor: "#F97316", confirmButtonText: "Log in / Register →", cancelButtonText: "Cancel" })
      if (result.isConfirmed) {
        pendingOrderAfterLogin.current = true
        setAuthMode("login")
        setShowAuthModal(true)
      }
      return
    }

    const confirmed = await Swal.fire({ title: "Confirm Order", html: summaryHtml, icon: "question", showCancelButton: true, confirmButtonColor: "#F97316", confirmButtonText: "Yes – Place Order", cancelButtonText: "Review again", reverseButtons: true })
    if (!confirmed.isConfirmed) return

    setLoading(true)
    if (saveAddr && !isPickup) saveLocation(location)

    try {
      const res = await axios.post("/api/orders", {
        items: items.map((i) => ({ product_id: i.id, qty: i.qty })),
        // For pickup: send minimal location (just name+phone), no address needed
        location: isPickup
          ? { name: location.name || user?.username || "", phone: location.phone || "", address: STORE_ADDRESS, city: "", note: "" }
          : location,
        location_id: isPickup ? null : (locationId || null),
        payment_method: payMethod,
        subtotal,
        discount_code: discount?.code || null,
        discount_amount: discountAmount > 0 ? discountAmount : null,
        delivery_date: delivery.date || null,
        delivery_time_slot: delivery.timeSlot || null,
        delivery_lat: isPickup ? null : (mapPin?.lat || null),
        delivery_lng: isPickup ? null : (mapPin?.lng || null),
        delivery_map_address: isPickup ? null : (mapPin?.address || null),
        // ── NEW ──
        fulfillment_type: fulfillment,
      })

      const orderData = {
        ...res.data, items, location, payment_method: payMethod, total: finalTotal, subtotal,
        _discountAmount: discountAmount, _discountCode: discount?.code || null,
        _discountType: discount?.type || null, _discountValue: discount?.value || null,
        delivery_date: delivery.date || null, delivery_time_slot: delivery.timeSlot || null,
        fulfillment_type: fulfillment,
      }

      clearCart(); removeDiscount(); setOrder(orderData); setDeliveryStatus(0)

      if (payMethod === "bakong") {
        setShowQrModal(true)
      } else {
        setStep(3)
        Swal.fire({
          title: isPickup ? "Order Placed! 🏪" : "Order Placed! 🎉",
          text: isPickup
            ? `Order #${res.data.order_id} received. Please come to our store to pick up your order.`
            : `Order #${res.data.order_id} received. We'll contact you before delivery.`,
          icon: "success",
          confirmButtonColor: "#F97316",
        })
      }
    } catch (e) {
      let msg = "Order failed. Please try again."
      if (e.response?.data?.errors) msg = Object.values(e.response.data.errors).flat().join(" • ")
      else if (e.response?.data?.message) msg = e.response.data.message
      Swal.fire({ title: "Error", html: `<div style="color:#dc2626;">${msg}</div>`, icon: "error", confirmButtonColor: "#F97316" })
    } finally { setLoading(false) }
  }

  if (step === 3 && order) return <OrderReceipt order={order} deliveryStatus={deliveryStatus} />

  const bg               = dark ? '#111827' : '#fff'
  const text             = dark ? '#f9fafb' : '#1f2937'
  const subText          = dark ? '#9ca3af' : '#6b7280'
  const borderCol        = dark ? '#374151' : '#e5e7eb'
  const stepInactive     = dark ? '#1f2937' : '#f3f4f6'
  const stepInactiveText = dark ? '#6b7280' : '#9ca3af'
  const pillBg           = dark ? '#1f2937' : '#f3f4f6'

  return (
    <div className="max-w-[800px] mx-auto px-4 py-8" style={{ background: bg, minHeight: '60vh' }}>
      <h1 className="font-black mb-2" style={{ fontSize: 30, color: text }}>{t("checkout.title")}</h1>

      {/* ── Fulfillment type pill toggle — only on step 1 ─────────────────── */}
      {step === 1 && (
      <div className="mb-6">
        <p className="font-bold mb-3" style={{ fontSize: 13, letterSpacing: isKhmer ? 0 : 2, color: subText }}>
          {t("checkout.fulfillmentType")}
        </p>
        <div className="flex gap-3 flex-wrap">
          {[
            { value: "delivery", label: t("checkout.fulfillDelivery"),  desc: t("checkout.fulfillDeliveryDesc") },
            { value: "pickup",   label: t("checkout.fulfillPickup"),    desc: t("checkout.fulfillPickupDesc") },
          ].map(({ value, label, desc }) => {
            const active = fulfillment === value
            return (
              <button
                key={value}
                onClick={() => setFulfillment(value)}
                style={{
                  display: "flex", flexDirection: "column", alignItems: "flex-start",
                  gap: 2, padding: "12px 20px", borderRadius: 999,
                  border: `2px solid ${active ? '#F97316' : borderCol}`,
                  background: active ? (dark ? 'rgba(249,115,22,0.12)' : 'rgba(249,115,22,0.06)') : pillBg,
                  color: active ? '#F97316' : subText,
                  fontWeight: 700, fontSize: 15,
                  fontFamily: isKhmer ? "KantumruyPro, Khmer OS, sans-serif" : "Rajdhani, sans-serif",
                  cursor: "pointer", transition: "all 0.2s",
                  boxShadow: active ? '0 0 0 3px rgba(249,115,22,0.15)' : 'none',
                }}
              >
                <span>{label}</span>
                <span style={{ fontSize: 11, fontWeight: 500, color: active ? 'rgba(249,115,22,0.7)' : subText, letterSpacing: 0 }}>{desc}</span>
              </button>
            )
          })}
        </div>

        {/* ── Pickup info banner + store map ──────────────────────────────── */}
        {isPickup && (
          <div className="mt-4 rounded-2xl overflow-hidden"
            style={{ border: `1px solid ${dark ? 'rgba(249,115,22,0.3)' : 'rgba(249,115,22,0.25)'}`, background: dark ? '#111827' : '#fff' }}>

            {/* Info row */}
            <div className="flex items-start gap-3 p-4"
              style={{ background: dark ? 'rgba(249,115,22,0.08)' : 'rgba(249,115,22,0.05)' }}>
              <span style={{ fontSize: 26, flexShrink: 0 }}>🏪</span>
              <div className="flex-1 min-w-0">
                <p className="font-black" style={{ fontSize: 15, color: '#F97316', letterSpacing: isKhmer ? 0 : 0.5 }}>
                  {t("checkout.storePickupTitle")}
                </p>
                <p style={{ fontSize: 13, color: subText, marginTop: 3, lineHeight: 1.5 }}>
                  📍 {STORE_ADDRESS}
                </p>
                <p style={{ fontSize: 12, color: subText, marginTop: 4 }}>
                  {t("checkout.storePickupHint")}
                </p>
                {/* Hours */}
                <p style={{ fontSize: 12, color: dark ? '#4ade80' : '#16a34a', marginTop: 6, fontWeight: 700 }}>
                  🕗 Mon–Sun · 08:30 – 18:00
                </p>
              </div>
            </div>

            {/* Static Google Map embed */}
            <div style={{ position: 'relative', width: '100%', height: 220 }}>
              <iframe
                title="Tronmatix Store Location"
                width="100%"
                height="220"
                style={{ border: 0, display: 'block' }}
                loading="lazy"
                allowFullScreen
                referrerPolicy="no-referrer-when-downgrade"
                src={`https://www.google.com/maps?q=${STORE_LAT},${STORE_LNG}&z=17&output=embed`}
              />
              {dark && (
                <div style={{
                  position: 'absolute', inset: 0, pointerEvents: 'none',
                  background: 'rgba(0,0,0,0.15)',
                }} />
              )}
            </div>

            {/* Open in Maps button */}
            <div className="px-4 py-3 flex items-center justify-between flex-wrap gap-2"
              style={{ borderTop: `1px solid ${dark ? 'rgba(255,255,255,0.07)' : '#f3f4f6'}` }}>
              <span style={{ fontSize: 12, color: subText }}>
                📞 096 733 3725 / 077 711 126
              </span>
              <a
                href={STORE_MAPS_URL}
                target="_blank"
                rel="noopener noreferrer"
                style={{
                  display: 'inline-flex', alignItems: 'center', gap: 6,
                  padding: '7px 16px', borderRadius: 999,
                  background: '#F97316', color: '#fff',
                  fontSize: 13, fontWeight: 700,
                  fontFamily: isKhmer ? "KantumruyPro, Khmer OS, sans-serif" : "Rajdhani, sans-serif",
                  textDecoration: 'none', letterSpacing: isKhmer ? 0 : 0.5,
                  boxShadow: '0 2px 10px rgba(249,115,22,0.35)',
                  flexShrink: 0,
                }}
              >
                🗺️ {isKhmer ? "បើក Google Maps" : "Open in Google Maps"}
              </a>
            </div>
          </div>
        )}
      </div>
      )}

      {/* ── Step 2: compact fulfillment summary (no map, no iframe) ─────────
           Shows a read-only badge + "← Change" button to go back to step 1.
           The full map/banner is intentionally hidden here so the iframe
           cannot block pointer events on the Payment step UI.
      ── */}
      {step === 2 && (
        <div className="mb-5 flex items-center gap-3 px-4 py-3 rounded-xl"
          style={{
            background: isPickup
              ? (dark ? 'rgba(34,197,94,0.07)' : '#f0fdf4')
              : (dark ? 'rgba(167,139,250,0.07)' : '#faf5ff'),
            border: `1px solid ${isPickup
              ? (dark ? 'rgba(34,197,94,0.25)' : '#bbf7d0')
              : (dark ? 'rgba(167,139,250,0.25)' : '#e9d5ff')}`,
          }}>
          <span style={{ fontSize: 20 }}>{isPickup ? '🏪' : '🚚'}</span>
          <div className="flex-1">
            <span className="font-bold" style={{
              fontSize: 14,
              color: isPickup ? '#22c55e' : '#a78bfa',
            }}>
              {isPickup ? t("checkout.fulfillPickup") : t("checkout.fulfillDelivery")}
            </span>
            {isPickup && (
              <span style={{ fontSize: 12, color: subText, marginLeft: 8 }}>
                {STORE_ADDRESS.split(',')[0]}
              </span>
            )}
          </div>
          {/* ← Change button — resets to step 1 so user can switch */}
          <button
            onClick={() => setStep(1)}
            style={{
              fontSize: 12, fontWeight: 700, color: '#F97316',
              background: 'none', border: '1.5px solid rgba(249,115,22,0.4)',
              borderRadius: 999, padding: '4px 14px', cursor: 'pointer',
              fontFamily: isKhmer ? "KantumruyPro, Khmer OS, sans-serif" : "Rajdhani, sans-serif",
              letterSpacing: isKhmer ? 0 : 0.5,
              transition: 'all 0.15s',
            }}
            onMouseEnter={(e) => { e.currentTarget.style.background = 'rgba(249,115,22,0.1)' }}
            onMouseLeave={(e) => { e.currentTarget.style.background = 'none' }}
          >
            ✏️ {isKhmer ? "ផ្លាស់ប្តូរ" : "Change"}
          </button>
        </div>
      )}

      {/* Step indicators */}
      <div className="flex items-center gap-0 mb-8">
        {STEPS.map((s, i) => (
          <div key={s} className="flex items-center">
            <div className="flex items-center gap-2 px-4 py-2 rounded-full font-bold transition-all"
              style={{
                fontSize: 14,
                background: step === i+1 ? '#F97316' : step > i+1 ? '#22c55e' : stepInactive,
                color: step === i+1 ? '#fff' : step > i+1 ? '#fff' : stepInactiveText,
              }}>
              <div className="w-6 h-6 rounded-full flex items-center justify-center font-black" style={{ fontSize: 12 }}>
                {step > i+1 ? '✓' : i+1}
              </div>
              {s}
            </div>
            {i < STEPS.length - 1 && (
              <div className="w-8 h-1 mx-1" style={{ background: step > i+1 ? '#F97316' : dark ? '#374151' : '#e5e7eb' }} />
            )}
          </div>
        ))}
      </div>

      {step === 1 && (
        <Step1DeliveryInfo
          location={location} onChange={handleLocation}
          delivery={delivery} onDeliveryChange={setDelivery}
          saveAddr={saveAddr} onSaveAddr={setSaveAddr}
          savedLocations={savedLocations} onPickLocation={() => setShowLocPicker(true)}
          mapPin={mapPin} onMapPin={setMapPin}
          onSaveToProfile={user ? handleSaveToProfile : undefined}
          onNext={() => setStep(2)}
          // Pass isPickup so Step1 can hide address fields
          isPickup={isPickup}
        />
      )}

      {showLocPicker && !isPickup && (
        <LocationPickerModal locations={savedLocations} onSelect={handleSelectSavedLocation} onClose={() => setShowLocPicker(false)} />
      )}

      {step === 2 && (
        <Step2Payment
          payMethod={payMethod} onPayMethod={setPayMethod}
          items={items} subtotal={subtotal} discountAmount={discountAmount}
          discount={discount} finalTotal={finalTotal} loading={loading}
          onBack={() => setStep(1)} onPlace={placeOrder}
          isPickup={isPickup}
        />
      )}

      {/* KHQR modal */}
      {showQrModal && order && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4"
          style={{ background: "rgba(0,0,0,0.7)", backdropFilter: "blur(4px)" }}>
          <div className="bg-white rounded-2xl shadow-2xl w-full overflow-hidden"
            style={{ maxWidth: 380, animation: "fadeInScale .2s ease", maxHeight: "92vh", overflowY: "auto" }}>
            <div className="flex justify-end px-4 pt-3 pb-0">
              <button onClick={() => { setShowQrModal(false); setStep(3) }}
                className="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 font-bold transition-colors"
                title="Close">✕</button>
            </div>
            <BakongQRPanel orderId={order.id ?? order.data?.id} total={order.total}
              onPaid={() => { setTimeout(() => { setShowQrModal(false); setDeliveryStatus(1); setStep(3) }, 1800) }} />
          </div>
          <style>{`@keyframes fadeInScale { from { opacity:0; transform:scale(.93) translateY(20px) } to { opacity:1; transform:scale(1) translateY(0) } }`}</style>
        </div>
      )}

      {showAuthModal && (
        <AuthModal
          mode={authMode}
          onClose={() => {
            setShowAuthModal(false)
            if (!user) pendingOrderAfterLogin.current = false
          }}
          onSwitch={setAuthMode}
        />
      )}
    </div>
  )
}