import { useState, useEffect, useRef } from "react"
import { useTheme } from "../../context/ThemeContext"
import { useLang } from "../../context/LanguageContext"
import axios from "../../lib/axios"

export default function DeliveryProviderSelector({ zoneId, onSelect, selectedValue }) {
  const { dark } = useTheme()
  const { t, isKhmer } = useLang()
  const font = isKhmer ? "Kdam Thmor Pro, sans-serif" : "Rajdhani, sans-serif"
  const [providers, setProviders] = useState([])
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState(null)
  const [selected, setSelected] = useState(null)
  const prevZoneId = useRef(null)

  useEffect(() => {
    if (!zoneId) return
    if (prevZoneId.current === zoneId) return // avoid duplicate fetches
    prevZoneId.current = zoneId

    setLoading(true); setError(null); setSelected(null); setProviders([])

    axios.get(`/api/delivery-providers?zone_id=${zoneId}`)
      .then((res) => {
        const list = res.data?.data ?? []
        setProviders(list)
      })
      .catch(() => setError(isKhmer ? t("common.networkError") : "Network error."))
      .finally(() => setLoading(false))
  }, [zoneId])

  useEffect(() => {
    if (selectedValue && providers.length) {
      const m = providers.find((p) => p.id === selectedValue)
      if (m) setSelected(m)
    }
  }, [selectedValue, providers])

  const handleSelect = (prov) => {
    setSelected(prov)
    onSelect?.(prov)
  }

  const c = {
    text:       dark ? "#f9fafb" : "#1f2937",
    muted:      dark ? "#9ca3af" : "#6b7280",
    cardBorder: dark ? "#374151" : "#e5e7eb",
    cardSelBg:  dark ? "rgba(249,115,22,0.10)" : "#fff7ed",
    emptyBg:    dark ? "#111827" : "#f9fafb",
    emptyBorder: dark ? "#374151" : "#e5e7eb",
  }

  // Don't render anything if no zone is selected
  if (!zoneId) return null

  // Loading state
  if (loading) return (
    <div>
      <label className="block font-bold mb-2" style={{ fontSize: isKhmer ? 13 : 15, color: c.muted, fontFamily: font }}>
        {isKhmer ? t("checkout.deliveryProvider") : "Delivery Provider"}
      </label>
      <div style={{ padding: "14px 16px", borderRadius: 10, background: c.emptyBg, border: `1px solid ${c.emptyBorder}`, color: c.muted, fontFamily: font, fontSize: isKhmer ? 13 : 15 }}>
        ⏳ {isKhmer ? t("locations.loading") : "Loading providers..."}
      </div>
    </div>
  )

  // Error state
  if (error) return (
    <div>
      <label className="block font-bold mb-2" style={{ fontSize: isKhmer ? 13 : 15, color: c.muted, fontFamily: font }}>
        {isKhmer ? t("checkout.deliveryProvider") : "Delivery Provider"}
      </label>
      <div style={{ padding: "14px 16px", borderRadius: 10, background: "rgba(239,68,68,0.08)", border: "1px solid rgba(239,68,68,0.25)", color: "#ef4444", fontFamily: font, fontSize: isKhmer ? 13 : 15 }}>
        ⚠ {error}
      </div>
    </div>
  )

  // Empty state — zone has no active providers
  if (!providers.length) return (
    <div>
      <label className="block font-bold mb-2" style={{ fontSize: isKhmer ? 13 : 15, color: c.muted, fontFamily: font }}>
        {isKhmer ? t("checkout.deliveryProvider") : "Delivery Provider"}
      </label>
      <div style={{ padding: "14px 16px", borderRadius: 10, background: c.emptyBg, border: `1px solid ${c.emptyBorder}`, color: c.muted, fontFamily: font, fontSize: isKhmer ? 13 : 15 }}>
        <p>{isKhmer ? t("checkout.noProviders") : "No delivery providers available for this province."}</p>
      </div>
    </div>
  )

  return (
    <div>
      <label className="block font-bold mb-2" style={{ fontSize: isKhmer ? 13 : 15, color: c.muted, fontFamily: font }}>
        {isKhmer ? t("checkout.deliveryProvider") : "Delivery Provider"}
      </label>
      <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
        {providers.map((p) => {
          const sel = selected?.id === p.id
          return (
            <label
              key={p.id}
              style={{
                display: "flex", alignItems: "center", gap: 12,
                padding: "12px 16px", borderRadius: 12, cursor: "pointer",
                border: `2px solid ${sel ? "#F97316" : c.cardBorder}`,
                background: sel ? c.cardSelBg : "transparent",
                transition: "all 0.15s",
              }}
            >
              <input
                type="radio" name="delivery_provider_id" value={p.id}
                checked={sel} onChange={() => handleSelect(p)}
                style={{ width: 18, height: 18, accentColor: "#F97316", flexShrink: 0 }}
              />
              {p.logo ? (
                <img
                  src={p.logo} alt={p.name}
                  style={{ width: 36, height: 36, borderRadius: 8, objectFit: "contain", background: dark ? "#1a1a1a" : "#f3f4f6", flexShrink: 0 }}
                  onError={(e) => { e.currentTarget.style.display = "none"; e.currentTarget.nextElementSibling.style.display = "flex" }}
                />
              ) : null}
              <div
                style={{ display: !p.logo ? "flex" : "none", width: 36, height: 36, borderRadius: 8, background: "rgba(249,115,22,0.1)", border: "1px solid rgba(249,115,22,0.2)", alignItems: "center", justifyContent: "center", fontSize: 18, flexShrink: 0 }}
              >🚚</div>
              <div style={{ flex: 1, minWidth: 0 }}>
                <p style={{ fontSize: 15, fontWeight: sel ? 700 : 600, color: c.text, fontFamily: font, margin: 0 }}>
                  {p.name}
                </p>
                <div style={{ display: "flex", alignItems: "center", gap: 10, marginTop: 2 }}>
                  {p.fee !== null ? (
                    <span style={{ fontSize: 14, fontWeight: 800, color: "#F97316", fontFamily: font }}>
                      ${Number(p.fee).toFixed(2)}
                    </span>
                  ) : (
                    <span style={{ fontSize: 14, color: c.muted, fontFamily: font }}>
                      {isKhmer ? t("checkout.feeVaries") : "Fee varies"}
                    </span>
                  )}
                  {p.estimated_time && (
                    <span style={{ fontSize: 14, color: c.muted, fontFamily: font }}>
                      ⏱ {p.estimated_time}
                    </span>
                  )}
                </div>
              </div>
            </label>
          )
        })}
      </div>
      {selected && <input type="hidden" name="delivery_provider_id" value={selected.id} />}
    </div>
  )
}
