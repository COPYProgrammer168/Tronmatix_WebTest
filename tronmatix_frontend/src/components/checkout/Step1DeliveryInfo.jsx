// src/components/checkout/Step1DeliveryInfo.jsx
import { useState, useRef } from "react"
import { useTheme } from "../../context/ThemeContext"
import { useLang } from "../../context/LanguageContext"
import { signInWithPhoneNumber, RecaptchaVerifier } from "firebase/auth"
import { auth, isConfigured as firebaseConfigured } from "../../lib/firebase"
import { toE164Phone, phoneValidationMessage } from "../../lib/phone"
import DeliverySchedulePicker from "./DeliverySchedulePicker"
import ProvinceSelect from "./ProvinceSelect"
import DeliveryProviderSelector from "./DeliveryProviderSelector"
import MapPickerModal from "../profile/MapPickerModal"

export default function Step1DeliveryInfo({
  location, onChange, delivery, onDeliveryChange,
  saveAddr, onSaveAddr, savedLocations, onPickLocation,
  onSaveToProfile, onNext, mapPin, onMapPin,
  isPickup,
  // NEW: province / provider props
  onProvinceSelect, selectedProvince, onProviderSelect, selectedProviderId,
  // NEW: phone verification callback
  onPhoneVerified,
}) {
  const { dark } = useTheme()
  const { t, isKhmer } = useLang()
  const step1Font = isKhmer
    ? "KantumruyPro, sans-serif"
    : "Rajdhani, sans-serif";
  const [showMapPicker, setShowMapPicker] = useState(false)

  // ── Standalone phone verification (Firebase OTP) — not tied to account ──
  const [phoneStep, setPhoneStep] = useState("phone")
  const [phoneBusy, setPhoneBusy] = useState(false)
  const [phoneError, setPhoneError] = useState("")
  const [otpCode, setOtpCode] = useState("")
  const [phoneVerified, setPhoneVerified] = useState(false)
  const confirmationRef = useRef(null)
  const verifierRef = useRef(null)

  // ── Delivery requires name + phone + address + a map pin; pickup only name + phone ──
  // The map pin lives outside `location` (separate state), so handle it specially.
  const requiredFields = isPickup
    ? [
      { key: "name", label: "Full Name" },
      { key: "phone", label: "Phone" },
    ]
    : [
      { key: "name", label: "Full Name" },
      { key: "phone", label: "Phone" },
      { key: "address", label: "Address" },
      { key: "mapPin", label: "Pin Location on Map" },
    ]
  const getValue = (key) => (key === "mapPin" ? mapPin?.lat : location[key])
  const missingFields = requiredFields.filter((f) => !getValue(f.key))
  const missingMapPin = !isPickup && !mapPin?.lat
  const canProceed = missingFields.length === 0
  const [saving, setSaving] = useState(false)
  const [saved, setSaved] = useState(false)
  const [saveErr, setSaveErr] = useState(null)

  // Theme tokens
  const c = {
    heading: dark ? '#f9fafb' : '#1f2937',
    label: dark ? '#9ca3af' : '#4b5563',
    inputBg: dark ? '#111827' : '#ffffff',
    inputBorder: dark ? '#374151' : '#d1d5db',
    inputText: dark ? '#f9fafb' : '#1f2937',
    inputPh: dark ? '#6b7280' : '#9ca3af',
    scheduleBg: dark ? '#111827' : '#f9fafb',
    scheduleBor: dark ? '#374151' : '#e5e7eb',
    saveBg: dark ? 'rgba(249,115,22,0.08)' : '#fff7ed',
    saveBorder: dark ? 'rgba(249,115,22,0.25)' : '#fed7aa',
    saveText: dark ? '#d1d5db' : '#374151',
    textSub: dark ? '#6b7280' : '#9ca3af',
    pickupBg: dark ? 'rgba(34,197,94,0.06)' : '#f0fdf4',
    pickupBorder: dark ? 'rgba(34,197,94,0.25)' : '#bbf7d0',
  }

  const inputStyle = {
    fontSize: isKhmer ? 16 : 20,
    background: c.inputBg,
    border: `1px solid ${c.inputBorder}`,
    color: c.inputText,
  }

  const focusHandlers = {
    onFocus: (e) => { e.target.style.borderColor = '#F97316' },
    onBlur: (e) => { e.target.style.borderColor = c.inputBorder },
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

  // ── Firebase phone OTP helpers (reuses AuthModal / PhoneVerify pattern) ──
  const makeVerifier = () => {
    if (!firebaseConfigured || !auth) return null
    if (verifierRef.current) return verifierRef.current
    if (!window.tronmatixRecaptchaContainer) {
      const div = document.createElement("div")
      div.id = "checkout-recaptcha"
      document.body.appendChild(div)
      window.tronmatixRecaptchaContainer = div
    }
    verifierRef.current = new RecaptchaVerifier(auth, "checkout-recaptcha", { size: "invisible" })
    return verifierRef.current
  }

  const handleSendCode = async () => {
    setPhoneError("")
    if (!firebaseConfigured || !auth) {
      setPhoneError(isKhmer ? "សេវាផ្ទៀងផ្ទាត់លេខទូរស័ព្ទមិនអាចប្រើបានទេ" : "Phone verification is not configured.")
      return
    }
    // Show the raw input as-is in the field; only normalize here on submit.
    const raw = location.phone.trim()
    const e164 = toE164Phone(raw)
    const invalidMsg = phoneValidationMessage(raw, isKhmer)
    if (invalidMsg || !e164) {
      setPhoneError(invalidMsg || (isKhmer ? "សូមបញ្ចូលលេខទូរស័ព្ទឱ្យបានត្រឹមត្រូវ" : "Please enter a valid phone number."))
      return
    }
    setPhoneBusy(true)
    try {
      const verifier = makeVerifier()
      // Strict E.164: +855XXXXXXXXX (no spaces)
      const confirmation = await signInWithPhoneNumber(auth, e164, verifier)
      confirmationRef.current = confirmation
      setPhoneStep("code")
    } catch (e) {
      console.error("sendCode error", e)
      setPhoneError(e?.message || (isKhmer ? "មិនអាចផ្ញើលេខកូដបានទេ" : "Failed to send verification code."))
    } finally {
      setPhoneBusy(false)
    }
  }

  const handleVerifyCode = async () => {
    setPhoneError("")
    if (!confirmationRef.current) {
      setPhoneError(isKhmer ? "សូមផ្ញើលេខកូដមុន" : "Please send a code first.")
      return
    }
    if (!/^\d{6}$/.test(otpCode.trim())) {
      setPhoneError(isKhmer ? "សូមបញ្ចូលកូដ ៦ ខ្ទង់" : "Enter the 6-digit code.")
      return
    }
    setPhoneBusy(true)
    try {
      await confirmationRef.current.confirm(otpCode.trim())
      setPhoneVerified(true)
      setPhoneStep("phone")
      onPhoneVerified?.(true)
    } catch (e) {
      console.error("verifyCode error", e)
      setPhoneError(e?.message || (isKhmer ? "កូដមិនត្រឹមត្រូវ" : "Invalid code. Please try again."))
    } finally {
      setPhoneBusy(false)
    }
  }

  const resetPhoneFlow = () => {
    setPhoneStep("phone")
    setOtpCode("")
    setPhoneError("")
    setPhoneVerified(false)
    confirmationRef.current = null
    verifierRef.current = null
    if (window.tronmatixRecaptchaContainer) {
      window.tronmatixRecaptchaContainer.remove?.()
      delete window.tronmatixRecaptchaContainer
    }
    onPhoneVerified?.(false)
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
        <h2 className="font-black" style={{ fontSize: 25, color: c.heading, }}>
          {isPickup
            ? (isKhmer ? t("checkout.yourInfo") : "Your Contact Info")
            : (isKhmer ? t("checkout.deliveryInfo") : "Delivery Information")}
        </h2>
        {!isPickup && savedLocations.length > 0 && (
          <button
            onClick={onPickLocation}
            className="flex items-center gap-1.5 text-sm font-bold text-white bg-primary hover:bg-orange-600 px-3 py-1.5 rounded-lg transition-colors"
            style={{ fontFamily: step1Font }}
          >
            📍 {isKhmer ? t("checkout.myLocations") : `My Locations (${savedLocations.length})`}
          </button>
        )}
      </div>

      {/* Pickup notice banner */}
      {isPickup && (
        <div className="flex items-start gap-3 rounded-xl p-4"
          style={{ background: c.pickupBg, border: `1px solid ${c.pickupBorder}` }}>
          <span style={{ fontSize: 22 }}>🏪</span>
          <div>
            <p className="font-bold" style={{ fontSize: 14, color: '#22c55e' }}>{t("checkout.pickupSelectedTitle")}</p>
            <p style={{ fontSize: isKhmer ? 13 : 14, color: c.textSub, marginTop: 2 }}>{t("checkout.pickupSelectedHint")}</p>
          </div>
        </div>
      )}

      {/* Name + Phone */}
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label className="block font-bold mb-1" style={{ fontSize: isKhmer ? 13 : 18, color: c.label }}>Full Name *</label>
          <input
            name="name" value={location.name} onChange={onChange} placeholder="Your name"
            className="checkout-input font-semibold w-full rounded-lg px-4 py-2.5 focus:outline-none transition-colors"
            style={inputStyle} {...focusHandlers}
          />
        </div>
        <div>
          <label className="block font-bold mb-1" style={{ fontSize: isKhmer ? 13 : 15, color: c.label }}>Phone *</label>
          <div className="flex gap-2">
            <input
              name="phone" value={location.phone} onChange={onChange} placeholder="Phone number"
              className="checkout-input flex-1 rounded-lg px-4 py-2.5 focus:outline-none transition-colors"
              style={{ ...inputStyle, opacity: phoneVerified ? 0.7 : 1 }}
              readOnly={phoneVerified}
              {...focusHandlers}
            />
          </div>

          {/* OTP input */}
          {phoneStep === "code" && !phoneVerified && (
            <div className="mt-2">
              <input
                type="text"
                inputMode="numeric"
                maxLength={6}
                value={otpCode}
                onChange={(e) => setOtpCode(e.target.value.replace(/[^\d]/g, ""))}
                placeholder="123456"
                className="checkout-input w-full rounded-lg px-4 py-2.5 focus:outline-none"
                style={{ ...inputStyle, textAlign: "center", letterSpacing: 4, fontSize: 16 }}
              />
              {phoneError && <p style={{ fontSize: 12, color: "#EF4444", marginTop: 4 }}>{phoneError}</p>}
              <div className="flex gap-2 mt-2">
                <button
                  type="button"
                  onClick={handleVerifyCode}
                  disabled={phoneBusy}
                  className="flex-1 rounded-lg px-3 py-2 font-bold"
                  style={{ fontSize: 13, background: "#0088cc", color: "#fff", border: "none" }}
                >
                  {phoneBusy ? "..." : (isKhmer ? "ផ្ទៀងផ្ទាត់" : "VERIFY")}
                </button>
                <button
                  type="button"
                  onClick={resetPhoneFlow}
                  className="rounded-lg px-3 py-2 text-xs"
                  style={{ background: "none", border: "none", color: c.textSub, textDecoration: "underline", cursor: "pointer" }}
                >
                  {isKhmer ? "ប្តូរលេខ" : "Change"}
                </button>
              </div>
            </div>
          )}

          {/* Verified badge */}
          {phoneVerified && (
            <div className="flex items-center gap-2 mt-1.5" style={{ color: "#22c55e", fontSize: 12, fontWeight: 700 }}>
              <span>✓</span> {isKhmer ? "បានផ្ទៀងផ្ទាត់" : "Phone Verified"}
              <button
                type="button"
                onClick={resetPhoneFlow}
                style={{ background: "none", border: "none", color: c.textSub, cursor: "pointer", fontSize: 11, textDecoration: "underline" }}
              >
                {isKhmer ? "ផ្លាស់" : "Change"}
              </button>
            </div>
          )}
        </div>
      </div>

      {/* Address fields — hidden for pickup */}
      {!isPickup && (
        <>
          {/* Address */}
          <div>
            <label className="block font-bold mb-1" style={{ fontSize: isKhmer ? 13 : 18, color: c.label }}>{isKhmer ? t("checkout.address") : "Address *"}</label>
            <input
              name="address" value={location.address} onChange={onChange}
              placeholder="Street / Village / Commune"
              className="checkout-input w-full rounded-lg px-4 py-2.5 focus:outline-none transition-colors"
              style={inputStyle} {...focusHandlers}
            />
            {missingFields.length > 0 && (
              <p style={{ fontSize: 12, color: "#EF4444", marginTop: 5, fontWeight: 600 }}>
                ⚠ {isKhmer ? "សូមបំពេញ: " : "Please fill in: "}
                {missingFields.map((f) => f.label).join(", ")}
              </p>
            )}
          </div>

          {/* City / Province — replaced with ProvinceSelect */}
          <ProvinceSelect onSelect={onProvinceSelect} selectedValue={selectedProvince?.id} />

          {/* Delivery Provider — shown after province is selected */}
          {selectedProvince && (
            <DeliveryProviderSelector
              zoneId={selectedProvince.delivery_zone_id}
              onSelect={onProviderSelect}
              selectedValue={selectedProviderId}
            />
          )}

          {/* Map pin picker — required for delivery */}
          <div>
            <label className="block font-bold mb-1" style={{ fontSize: isKhmer ? 13 : 18, color: c.label }}>
              {isKhmer ? t("locations.mapPin") : "PIN LOCATION ON MAP *"}
            </label>
            <button type="button" onClick={() => setShowMapPicker(true)}
              className="w-full rounded-lg px-4 py-2.5 text-left transition-colors"
              style={{
                border: mapPin?.lat ? '1.5px solid #22c55e' : (missingMapPin ? '1.5px solid #EF4444' : `1px dashed ${c.inputBorder}`),
                background: mapPin?.lat ? 'rgba(34,197,94,0.06)' : c.inputBg,
                color: mapPin?.lat ? '#22c55e' : c.textSub,
                fontFamily: 'Rajdhani, sans-serif', fontSize: 18, fontWeight: 700, cursor: 'pointer',
              }}>
              {mapPin?.lat
                ? `✅ Pinned: ${mapPin.address ? mapPin.address.slice(0, 40) + '...' : `${Number(mapPin.lat).toFixed(5)}, ${Number(mapPin.lng).toFixed(5)}`}`
                : (isKhmer ? '📍 ចុចដើម្បីកំណត់ទីតាំង (ឬស្វែងរក/បិទភ្ជាប់តំណ) *' : '📍 Pin location (or search/paste link) *')
              }
            </button>
            {mapPin?.lat && (
              <button type="button" onClick={() => onMapPin?.(null)}
                style={{ fontSize: 12, color: '#EF4444', background: 'none', border: 'none', cursor: 'pointer', marginTop: 4 }}>
                ✕ {isKhmer ? t('checkout.removePin') : 'Remove pin'}
              </button>
            )}
          </div>

          {showMapPicker && (
            <MapPickerModal
              initialLat={mapPin?.lat}
              initialLng={mapPin?.lng}
              onClose={() => setShowMapPicker(false)}
              onConfirm={(pin) => {
                onMapPin?.(pin)
                setShowMapPicker(false)
              }}
            />
          )}
        </>
      )}

      {/* Note */}
      <div>
        <label className="block font-bold mb-1" style={{ fontSize: 18, color: c.label }}>
          {isKhmer ? t("checkout.note") : (isPickup ? "Note (optional)" : "Delivery Note (optional)")}
        </label>
        <textarea
          name="note" value={location.note} onChange={onChange} rows={2}
          placeholder={isPickup ? "Any special note for pickup…" : "Delivery instructions…"}
          className="checkout-input w-full rounded-lg px-4 py-2.5 focus:outline-none resize-none transition-colors"
          style={inputStyle} {...focusHandlers}
        />
      </div>

      {/* Save to Profile button — only for delivery */}
      {!isPickup && onSaveToProfile && (
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
                  <path strokeLinecap="round" strokeLinejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                {isKhmer ? t("profile.saving") : "Saving…"}
              </>
            ) : saved ? (
              <>
                <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth={2.5} viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                {isKhmer ? t("checkout.savedToProfile") : "Saved to Profile!"}
              </>
            ) : (
              <>
                <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path strokeLinecap="round" strokeLinejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {isKhmer ? t("checkout.saveToProfile") : "Save Address to My Profile"}
              </>
            )}
          </button>
          {saveErr && (
            <p className="text-red-500 font-semibold mt-1" style={{ fontSize: 12 }}>⚠ {saveErr}</p>
          )}
        </div>
      )}

      {/* Delivery schedule — only for delivery */}
      {!isPickup && (
        <div className="rounded-xl p-4" style={{ background: c.scheduleBg, border: `1px solid ${c.scheduleBor}` }}>
          <DeliverySchedulePicker value={delivery} onChange={onDeliveryChange} />
        </div>
      )}

      {/* Pickup schedule */}
      {isPickup && (
        <div className="rounded-xl p-4" style={{ background: c.scheduleBg, border: `1px solid ${c.scheduleBor}` }}>
          <p className="font-bold mb-2" style={{ fontSize: isKhmer ? 13 : 15, letterSpacing: isKhmer ? 0 : 1, color: c.label }}>{t("checkout.preferredPickupDate")}</p>
          <DeliverySchedulePicker value={delivery} onChange={onDeliveryChange} />
        </div>
      )}

      {/* Save address checkbox — only for delivery */}
      {!isPickup && (
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
              💾 {isKhmer ? t("checkout.saveAddress") : "Save this address when I place the order"}
            </span>
            <p style={{ fontSize: isKhmer ? 12 : 13, color: dark ? '#6b7280' : '#9ca3af', marginTop: 2 }}>
              {isKhmer ? t("checkout.saveAddressHint") : "Automatically saved to your profile when you checkout"}
            </p>
          </div>
        </label>
      )}

      {/* Continue */}
      <button
        onClick={onNext}
        disabled={!canProceed}
        className="w-full bg-primary text-white font-bold py-3.5 rounded-lg hover:bg-orange-600 transition-colors disabled:opacity-50"
        style={{ fontFamily: step1Font, fontSize: 18, letterSpacing: isKhmer ? 0 : undefined }}
      >
        {isKhmer ? t("checkout.continueToPayment") : "CONTINUE TO PAYMENT →"}
      </button>
    </div>
  )
}