// src/components/checkout/Step1DeliveryInfo.jsx
import { useState } from "react"
import { useTheme } from "../../context/ThemeContext"
import DeliverySchedulePicker from "./DeliverySchedulePicker"
import MapPickerModal from "../profile/MapPickerModal"

export default function Step1DeliveryInfo({ location, onChange, delivery, onDeliveryChange, saveAddr, onSaveAddr, savedLocations, onPickLocation, onNext, mapPin, onMapPin }) {
  const [showMapPicker, setShowMapPicker] = useState(false)
  const { dark } = useTheme()
  const canProceed = location.name && location.phone && location.address

  // Theme tokens
  const c = {
    heading:      dark ? '#f9fafb' : '#1f2937',
    label:        dark ? '#9ca3af' : '#4b5563',
    inputBg:      dark ? '#111827' : '#ffffff',
    inputBorder:  dark ? '#374151' : '#d1d5db',
    inputText:    dark ? '#f9fafb' : '#1f2937',
    inputPh:      dark ? '#6b7280' : '#9ca3af',
    scheduleBg:   dark ? '#111827' : '#f9fafb',
    scheduleBor:  dark ? '#374151' : '#e5e7eb',
    saveBg:       dark ? 'rgba(249,115,22,0.08)' : '#fff7ed',
    saveBorder:   dark ? 'rgba(249,115,22,0.25)' : '#fed7aa',
    saveText:     dark ? '#d1d5db' : '#374151',
    backBtn:      dark ? '#374151' : '#d1d5db',
    backText:     dark ? '#f9fafb' : '#374151',
  }

  const inputStyle = {
    fontSize: 15,
    background: c.inputBg,
    border: `1px solid ${c.inputBorder}`,
    color: c.inputText,
  }

  const focusHandlers = {
    onFocus: (e) => { e.target.style.borderColor = '#F97316' },
    onBlur:  (e) => { e.target.style.borderColor = c.inputBorder },
  }

  return (
    <div className="space-y-4">
      {/* Placeholder color override for dark mode */}
      {dark && (
        <style>{`
          .checkout-input::placeholder { color: #6b7280; }
        `}</style>
      )}

      {/* Header */}
      <div className="flex items-center justify-between mb-2">
        <h2 className="font-black" style={{ fontSize: 20, color: c.heading }}>
          Delivery Information
        </h2>
        {savedLocations.length > 0 && (
          <button
            onClick={onPickLocation}
            className="flex items-center gap-1.5 text-sm font-bold text-white bg-primary hover:bg-orange-600 px-3 py-1.5 rounded-lg transition-colors"
            style={{ fontFamily: "Rajdhani, sans-serif", letterSpacing: 1 }}
          >
            📍 My Locations ({savedLocations.length})
          </button>
        )}
      </div>

      {/* Name + Phone */}
      <div className="grid grid-cols-2 gap-4">
        {[["name", "Full Name *", "Your name"], ["phone", "Phone *", "Phone number"]].map(([n, l, p]) => (
          <div key={n}>
            <label className="block font-bold mb-1" style={{ fontSize: 13, color: c.label }}>{l}</label>
            <input
              name={n} value={location[n]} onChange={onChange} placeholder={p}
              className="checkout-input w-full rounded-lg px-4 py-2.5 focus:outline-none transition-colors"
              style={inputStyle} {...focusHandlers}
            />
          </div>
        ))}
      </div>

      {/* Address */}
      <div>
        <label className="block font-bold mb-1" style={{ fontSize: 13, color: c.label }}>Address *</label>
        <input
          name="address" value={location.address} onChange={onChange}
          placeholder="Street / Village / Commune"
          className="checkout-input w-full rounded-lg px-4 py-2.5 focus:outline-none transition-colors"
          style={inputStyle} {...focusHandlers}
        />
      </div>

      {/* City */}
      <div>
        <label className="block font-bold mb-1" style={{ fontSize: 13, color: c.label }}>City / Province</label>
        <input
          name="city" value={location.city} onChange={onChange} placeholder="City or Province"
          className="checkout-input w-full rounded-lg px-4 py-2.5 focus:outline-none transition-colors"
          style={inputStyle} {...focusHandlers}
        />
      </div>

      {/* Note */}
      <div>
        <label className="block font-bold mb-1" style={{ fontSize: 13, color: c.label }}>Note (optional)</label>
        <textarea
          name="note" value={location.note} onChange={onChange} rows={2}
          placeholder="Delivery instructions…"
          className="checkout-input w-full rounded-lg px-4 py-2.5 focus:outline-none resize-none transition-colors"
          style={inputStyle} {...focusHandlers}
        />
      </div>

      {/* Map pin picker */}
      <div>
        <label className="block font-bold mb-1" style={{ fontSize: 13, color: c.label }}>
          PIN LOCATION ON MAP (optional)
        </label>
        <button type="button" onClick={() => setShowMapPicker(true)}
          className="w-full rounded-lg px-4 py-2.5 text-left transition-colors"
          style={{
            border: mapPin?.lat ? '1.5px solid #22c55e' : `1px dashed ${c.inputBorder}`,
            background: mapPin?.lat ? 'rgba(34,197,94,0.06)' : c.inputBg,
            color: mapPin?.lat ? '#22c55e' : c.textSub,
            fontFamily: 'Rajdhani, sans-serif', fontSize: 14, fontWeight: 700, cursor: 'pointer',
          }}>
          {mapPin?.lat
            ? `✅ Pinned: ${mapPin.address ? mapPin.address.slice(0,40)+'...' : `${Number(mapPin.lat).toFixed(5)}, ${Number(mapPin.lng).toFixed(5)}`}`
            : '📍 Tap to pin your exact delivery location on map'
          }
        </button>
        {mapPin?.lat && (
          <button type="button" onClick={() => onMapPin?.(null)}
            style={{ fontSize: 12, color: '#EF4444', background: 'none', border: 'none', cursor: 'pointer', marginTop: 4 }}>
            ✕ Remove pin
          </button>
        )}
      </div>

      {showMapPicker && (
        <MapPickerModal
          onClose={() => setShowMapPicker(false)}
          initialLat={mapPin?.lat}
          initialLng={mapPin?.lng}
          onConfirm={(pin) => {
            onMapPin?.(pin)
            setShowMapPicker(false)
          }}
        />
      )}

      {/* Delivery schedule */}
      <div className="rounded-xl p-4" style={{ background: c.scheduleBg, border: `1px solid ${c.scheduleBor}` }}>
        <DeliverySchedulePicker value={delivery} onChange={onDeliveryChange} />
      </div>

      {/* Save address toggle */}
      <label
        className="flex items-center gap-3 cursor-pointer p-3 rounded-lg"
        style={{ background: c.saveBg, border: `1px solid ${c.saveBorder}` }}
      >
        <input
          type="checkbox" checked={saveAddr} onChange={(e) => onSaveAddr(e.target.checked)}
          className="w-4 h-4 accent-primary"
        />
        <span className="font-bold" style={{ fontSize: 15, color: c.saveText }}>
          💾 Save this address for next time
        </span>
      </label>

      {/* Continue */}
      <button
        onClick={onNext}
        disabled={!canProceed}
        className="w-full bg-primary text-white font-bold py-3.5 rounded-lg hover:bg-orange-600 transition-colors disabled:opacity-50"
        style={{ fontFamily: "Rajdhani, sans-serif", fontSize: 18 }}
      >
        CONTINUE TO PAYMENT →
      </button>
    </div>
  )
}
