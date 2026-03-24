// src/pages/CheckoutPage.jsx
import { useState, useEffect, useRef } from "react"
import { useNavigate } from "react-router-dom"
import { useCart }      from "../context/CartContext"
import { useAuth }      from "../context/AuthContext"
import { useLocation2 } from "../context/LocationContext"
import { useDiscount }  from "../context/DiscountContext"
import { useTheme }     from "../context/ThemeContext"
import axios from "../lib/axios"
import Swal  from "sweetalert2"
import AuthModal from "../components/AuthModal"

import Step1DeliveryInfo    from "../components/checkout/Step1DeliveryInfo"
import Step2Payment         from "../components/checkout/Step2Payment"
import OrderReceipt         from "../components/checkout/OrderReceipt"
import LocationPickerModal  from "../components/checkout/LocationPickerModal"
import BakongQRPanel        from "../components/orders/BakongQRPanel"

const STEPS = ["Delivery Info", "Payment"]

export default function CheckoutPage() {
  const { items, total, subtotal, clearCart } = useCart()
  const { user }                              = useAuth()
  const { savedLocation, saveLocation }       = useLocation2()
  const { discount, calcDiscount, removeDiscount } = useDiscount()
  const { dark } = useTheme()
  const navigate = useNavigate()

  const [step,           setStep]           = useState(1)
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
  const [mapPin,         setMapPin]         = useState(null)   // { lat, lng, address }
  const [locationId,     setLocationId]     = useState(null)   // ✅ FIX: FK → user_locations.id
  const pendingOrderAfterLogin              = useRef(false)    // retry placeOrder after login

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
            // ✅ FIX: pre-fill locationId and mapPin from default location
            setLocationId(def.id)
            if (def.lat && def.lng) setMapPin({ lat: def.lat, lng: def.lng, address: def.map_address || def.address })
          }
        }
      }).catch(() => {})
  }, [user]) // eslint-disable-line

  // Save a location directly to the user's profile from Step1
  const handleSaveToProfile = async (loc, isDefault = false) => {
    if (!user) throw new Error("Not logged in")
    await axios.post("/api/user/locations", {
      name: loc.name, phone: loc.phone, address: loc.address,
      city: loc.city || null, note: loc.note || null,
      is_default: isDefault,
    })
    // Refresh saved locations list after saving
    const res = await axios.get("/api/user/locations")
    const list = Array.isArray(res.data?.data) ? res.data.data : Array.isArray(res.data) ? res.data : []
    setSavedLocations(list)
  }

  const discountAmount = calcDiscount(subtotal, items)
  const finalTotal     = Math.max(0, subtotal - discountAmount)
  const handleLocation = (e) => {
    setLocation((p) => ({ ...p, [e.target.name]: e.target.value }))
    // ✅ FIX: if user edits fields manually, unlink the saved location FK
    setLocationId(null)
  }
  const handleSelectSavedLocation = (loc) => {
    setLocation({ name: loc.name || "", phone: loc.phone || "", address: loc.address || "", city: loc.city || "", note: loc.note || "" })
    // ✅ FIX: store the FK so the order links to this saved location
    setLocationId(loc.id)
    if (loc.lat && loc.lng) setMapPin({ lat: loc.lat, lng: loc.lng, address: loc.map_address || loc.address })
    else setMapPin(null)
  }

  const placeOrder = async () => {
    const summaryHtml = `<div style="text-align:left;line-height:1.8;font-size:1rem;color:#1f2937;padding:0 12px;">
      <strong>Total:</strong> $${finalTotal.toFixed(2)}${discountAmount > 0 ? ` <span style="color:#16a34a">(−$${discountAmount.toFixed(2)} discount)</span>` : ""}<br>
      <strong>Payment:</strong> ${payMethod === "cash" ? "Cash on Delivery" : "ABA BAKONG KHQR"}<br>
      <strong>Deliver to:</strong> ${location.name} · ${location.phone}<br>
      <span style="color:#6b7280">${location.address}${location.city ? `, ${location.city}` : ""}</span>
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
    if (saveAddr) saveLocation(location)

    try {
      const res = await axios.post("/api/orders", {
        items: items.map((i) => ({ product_id: i.id, qty: i.qty })),
        location,
        // ✅ FIX: send location_id so backend links order to saved address (enables map in admin)
        location_id: locationId || null,
        payment_method: payMethod, subtotal,
        discount_code: discount?.code || null, discount_amount: discountAmount > 0 ? discountAmount : null,
        delivery_date: delivery.date || null, delivery_time_slot: delivery.timeSlot || null,
        // ✅ FIX: map pin comes from the saved location or from a manual pin set in Step1
        delivery_lat: mapPin?.lat || null,
        delivery_lng: mapPin?.lng || null,
        delivery_map_address: mapPin?.address || null,
      })

      const orderData = {
        ...res.data, items, location, payment_method: payMethod, total: finalTotal, subtotal,
        _discountAmount: discountAmount, _discountCode: discount?.code || null,
        _discountType: discount?.type || null, _discountValue: discount?.value || null,
        delivery_date: delivery.date || null, delivery_time_slot: delivery.timeSlot || null,
      }

      clearCart(); removeDiscount(); setOrder(orderData); setDeliveryStatus(0)

      if (payMethod === "bakong") {
        setShowQrModal(true)
      } else {
        setStep(3)
        Swal.fire({ title: "Order Placed! 🎉", text: `Order #${res.data.order_id} received. We'll contact you before delivery.`, icon: "success", confirmButtonColor: "#F97316" })
      }
    } catch (e) {
      let msg = "Order failed. Please try again."
      if (e.response?.data?.errors) msg = Object.values(e.response.data.errors).flat().join(" • ")
      else if (e.response?.data?.message) msg = e.response.data.message
      Swal.fire({ title: "Error", html: `<div style="color:#dc2626;">${msg}</div>`, icon: "error", confirmButtonColor: "#F97316" })
    } finally { setLoading(false) }
  }

  if (step === 3 && order) return <OrderReceipt order={order} deliveryStatus={deliveryStatus} />

  const bg      = dark ? '#111827' : '#fff'
  const text    = dark ? '#f9fafb' : '#1f2937'
  const stepInactive = dark ? '#1f2937' : '#f3f4f6'
  const stepInactiveText = dark ? '#6b7280' : '#9ca3af'

  return (
    <div className="max-w-[800px] mx-auto px-4 py-8" style={{ background: bg, minHeight: '60vh' }}>
      <h1 className="font-black mb-6" style={{ fontFamily: "Rajdhani, sans-serif", fontSize: 28, color: text }}>Checkout</h1>

      {/* Step indicators */}
      <div className="flex items-center gap-0 mb-8">
        {STEPS.map((s, i) => (
          <div key={s} className="flex items-center">
            <div className={`flex items-center gap-2 px-4 py-2 rounded-full font-bold transition-all`}
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
        />
      )}

      {showLocPicker && (
        <LocationPickerModal locations={savedLocations} onSelect={handleSelectSavedLocation} onClose={() => setShowLocPicker(false)} />
      )}

      {step === 2 && (
        <Step2Payment
          payMethod={payMethod} onPayMethod={setPayMethod}
          items={items} subtotal={subtotal} discountAmount={discountAmount}
          discount={discount} finalTotal={finalTotal} loading={loading}
          onBack={() => setStep(1)} onPlace={placeOrder}
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