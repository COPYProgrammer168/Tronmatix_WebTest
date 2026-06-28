// src/components/checkout/DeliverySchedulePicker.jsx
import { useTheme } from "../../context/ThemeContext"
import { useLang } from "../../context/LanguageContext"

export default function DeliverySchedulePicker({ value, onChange }) {
  const { dark } = useTheme()
  const { t, isKhmer } = useLang()

  const c = {
    label:       dark ? '#9ca3af' : '#4b5563',
    inputBg:     dark ? '#1f2937' : '#ffffff',
    inputBorder: dark ? '#374151' : '#d1d5db',
    inputText:   dark ? '#f9fafb' : '#1f2937',
    // Date input calendar icon color is browser-controlled, but colorScheme fixes it
    slotBorder:  dark ? '#374151' : '#e5e7eb',
    slotText:    dark ? '#d1d5db' : '#4b5563',
    slotSelBg:   dark ? 'rgba(249,115,22,0.12)' : '#fff7ed',
  }

  return (
    <div>
      <label className="block font-bold mb-1" style={{ fontSize: 13, letterSpacing: 1, color: c.label }}>
        {isKhmer ? t("checkout.preferredDate") : "PREFERRED DELIVERY DATE (optional)"}
      </label>

      <input
        type="date"
        min={new Date(Date.now() + 86400000).toISOString().split("T")[0]}
        value={value?.date || ""}
        onChange={(e) => onChange?.({ date: e.target.value, timeSlot: value?.timeSlot || "" })}
        className="w-full rounded-lg px-4 py-2.5 focus:outline-none transition-colors"
        style={{
          fontSize: 15,
          background: c.inputBg,
          border: `1px solid ${c.inputBorder}`,
          color: c.inputText,
          // Tells the browser to use dark chrome for the date picker UI
          colorScheme: dark ? 'dark' : 'light',
        }}
        onFocus={(e) => { e.target.style.borderColor = '#F97316' }}
        onBlur={(e)  => { e.target.style.borderColor = c.inputBorder }}
      />

      {value?.date && (
        <div className="mt-2 flex gap-2 flex-wrap">
          {["08:00 – 12:00", "13:00 – 17:00"].map((slot) => {
            const selected = value.timeSlot === slot
            return (
              <button
                key={slot}
                type="button"
                onClick={() => onChange?.({ date: value.date, timeSlot: slot })}
                className="px-4 py-1.5 rounded-lg border-2 font-bold transition-all text-sm"
                style={{
                  borderColor: selected ? '#F97316' : c.slotBorder,
                  background:  selected ? c.slotSelBg : 'transparent',
                  color:       selected ? '#F97316' : c.slotText,
                }}
                onMouseEnter={(e) => { if (!selected) e.currentTarget.style.borderColor = '#F97316' }}
                onMouseLeave={(e) => { if (!selected) e.currentTarget.style.borderColor = c.slotBorder }}
              >
                🕐 {slot}
              </button>
            )
          })}
        </div>
      )}
    </div>
  )
}
