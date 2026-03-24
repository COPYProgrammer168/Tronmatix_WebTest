// src/components/checkout/Step1DeliveryInfo.jsx
import { useState } from "react"
import { useTheme } from "../../context/ThemeContext"
import DeliverySchedulePicker from "./DeliverySchedulePicker"

export default function Step1DeliveryInfo({ location, onChange, delivery, onDeliveryChange, saveAddr, onSaveAddr, savedLocations, onPickLocation, onSaveToProfile, onNext, mapPin, onMapPin }) {
  const { dark } = useTheme()
  const [showMapPicker, setShowMapPicker] = useState(false)
  const canProceed = location.name && location.phone && location.address
  const [saving,   setSaving]   = useState(false)   // saving to profile
  const [saved,    setSaved]    = useState(false)    // saved success flash
  const [saveErr,  setSaveErr]  = useState(null)

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

  const handleSaveToProfile = async () => {
    if (!onSaveToProfile || !location.name || !location.phone || !location.address) return
    setSaving(true); setSaved(false); setSaveErr(null)
    try {
      await onSaveToProfile(location, false)
      setSaved(true)
      setTimeout(() => setSaved(false), 3000)
    } catch {
      setSaveErr('Failed to save. Try again.')
    } finally {
      setSaving(false)
    }
  }

  return (
    <div className="space-y-4">
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

      {/* ── Save to Profile button ──────────────────────────────────────────── */}
      {onSaveToProfile && (
        <div>
          <button
            type="button"
            onClick={handleSaveToProfile}
            disabled={saving || !location.name || !location.phone || !location.address}
            className="flex items-center gap-2 px-4 py-2.5 rounded-lg font-bold transition-all disabled:opacity-40"
            style={{
              fontSize: 14, letterSpacing: 0.5,
              background: saved
                ? 'rgba(34,197,94,0.12)'
                : dark ? 'rgba(249,115,22,0.10)' : '#fff7ed',
              border: `1.5px solid ${saved ? 'rgba(34,197,94,0.4)' : 'rgba(249,115,22,0.35)'}`,
              color: saved ? '#22c55e' : '#F97316',
              cursor: saving ? 'wait' : 'pointer',
            }}
          >
            {saving ? (
              <>
                <svg className="w-4 h-4 animate-spin" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Saving…
              </>
            ) : saved ? (
              <>
                <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth={2.5} viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Saved to Profile!
              </>
            ) : (
              <>
                <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path strokeLinecap="round" strokeLinejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Save Address to My Profile
              </>
            )}
          </button>
          {saveErr && (
            <p className="text-red-500 font-semibold mt-1" style={{ fontSize: 12 }}>⚠ {saveErr}</p>
          )}
        </div>
      )}

      {/* Delivery schedule */}
      <div className="rounded-xl p-4" style={{ background: c.scheduleBg, border: `1px solid ${c.scheduleBor}` }}>
        <DeliverySchedulePicker value={delivery} onChange={onDeliveryChange} />
      </div>

      {/* Save address on order toggle */}
      <label
        className="flex items-center gap-3 cursor-pointer p-3 rounded-lg"
        style={{ background: c.saveBg, border: `1px solid ${c.saveBorder}` }}
      >
        <input
          type="checkbox" checked={saveAddr} onChange={(e) => onSaveAddr(e.target.checked)}
          className="w-4 h-4 accent-primary"
        />
        <div>
          <span className="font-bold" style={{ fontSize: 15, color: c.saveText }}>
            💾 Save this address when I place the order
          </span>
          <p style={{ fontSize: 12, color: dark ? '#6b7280' : '#9ca3af', marginTop: 2 }}>
            Automatically saved to your profile when you checkout
          </p>
        </div>
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
