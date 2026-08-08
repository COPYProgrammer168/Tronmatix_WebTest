import { useState, useEffect, useRef } from "react"
import { useTheme } from "../../context/ThemeContext"
import { useLang } from "../../context/LanguageContext"
import axios from "../../lib/axios"

export default function ProvinceSelect({ onSelect, selectedValue }) {
  const { dark } = useTheme()
  const { t, isKhmer } = useLang()
  const font = isKhmer ? "Kdam Thmor Pro, sans-serif" : "Rajdhani, sans-serif"
  const [provinces, setProvinces] = useState([])
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState(null)
  const [search, setSearch] = useState("")
  const [open, setOpen] = useState(false)
  const [selected, setSelected] = useState(null)
  const wrapperRef = useRef(null)

  // Fetch provinces on mount
  useEffect(() => {
    setLoading(true); setError(null)
    axios.get("/api/provinces")
      .then((res) => {
        const list = res.data?.data ?? []
        setProvinces(list)
        if (selectedValue && list.length) {
          const m = list.find((p) => p.id === selectedValue)
          if (m) { setSelected(m); setSearch(isKhmer ? m.name_kh : m.name_en) }
        }
      })
      .catch(() => setError("Network error."))
      .finally(() => setLoading(false))
  }, [])

  // Sync with selectedValue from parent
  useEffect(() => {
    if (selectedValue && provinces.length) {
      const m = provinces.find((p) => p.id === selectedValue)
      if (m && (!selected || selected.id !== m.id)) {
        setSelected(m)
        setSearch(isKhmer ? m.name_kh : m.name_en)
      }
    }
  }, [selectedValue, provinces])

  // Close dropdown on outside click
  useEffect(() => {
    const handler = (e) => {
      if (wrapperRef.current && !wrapperRef.current.contains(e.target)) setOpen(false)
    }
    document.addEventListener("mousedown", handler)
    return () => document.removeEventListener("mousedown", handler)
  }, [])

  const handleSelect = (prov) => {
    setSelected(prov)
    setSearch(isKhmer ? prov.name_kh : prov.name_en)
    setOpen(false)
    onSelect?.(prov)
  }

  // Filter provinces by search
  const filtered = search.trim()
    ? provinces.filter((p) => {
        const q = search.toLowerCase()
        return p.name_en.toLowerCase().includes(q) || p.name_kh.toLowerCase().includes(q)
      })
    : provinces

  const c = {
    label:    dark ? "#9ca3af" : "#6b7280",
    inputBg:  dark ? "#111827" : "#ffffff",
    inputBorder: dark ? "#374151" : "#d1d5db",
    inputText: dark ? "#f9fafb" : "#1f2937",
    dropdownBg: dark ? "#1a1a1a" : "#ffffff",
    dropdownBorder: dark ? "#374151" : "#e5e7eb",
    hoverBg:  dark ? "rgba(255,255,255,0.06)" : "rgba(0,0,0,0.04)",
    selBg:    dark ? "rgba(249,115,22,0.12)" : "#fff7ed",
    selColor: dark ? "#f9fafb" : "#1f2937",
    muted:    dark ? "#9ca3af" : "#6b7280",
    emptyBg:  dark ? "#111827" : "#f9fafb",
    emptyBorder: dark ? "#374151" : "#e5e7eb",
  }

  if (loading) return (
    <div>
      <label className="block font-bold mb-1" style={{ fontSize: isKhmer ? 13 : 15, color: c.label, fontFamily: font }}>
        {isKhmer ? t("locations.province") : "Province *"}
      </label>
      <div style={{ padding: "10px 14px", borderRadius: 8, background: c.emptyBg, border: `1px solid ${c.emptyBorder}`, color: c.muted, fontFamily: font, fontSize: isKhmer ? 13 : 15 }}>
        ⏳ {isKhmer ? t("locations.loading") : "Loading provinces..."}
      </div>
    </div>
  )

  if (error) return (
    <div>
      <label className="block font-bold mb-1" style={{ fontSize: isKhmer ? 13 : 15, color: c.label, fontFamily: font }}>
        {isKhmer ? t("locations.province") : "Province *"}
      </label>
      <div style={{ padding: "10px 14px", borderRadius: 8, background: "rgba(239,68,68,0.08)", border: "1px solid rgba(239,68,68,0.25)", color: "#ef4444", fontFamily: font, fontSize: isKhmer ? 13 : 15 }}>
        ⚠ {error}
      </div>
    </div>
  )

  return (
    <div ref={wrapperRef} style={{ position: "relative" }}>
      <label className="block font-bold mb-1" style={{ fontSize: isKhmer ? 13 : 15, color: c.label, fontFamily: font }}>
        {isKhmer ? t("locations.province") : "Province *"}
      </label>

      {/* Search input — click to show full dropdown */}
      <div style={{ position: "relative" }}>
        <input
          type="text"
          value={search}
          onChange={(e) => { setSearch(e.target.value); if (!open) setOpen(true); if (selected) { setSelected(null); onSelect?.(null) } }}
          onFocus={() => setOpen(true)}
          onClick={() => setOpen(true)}
          placeholder={isKhmer ? t("locations.searchProvince") || "ស្វែងរកខេត្ត..." : "Select or search province..."}
        style={{
          width: "100%", padding: "10px 14px", borderRadius: 8,
          border: `1px solid ${selected ? "#F97316" : c.inputBorder}`,
          background: c.inputBg, color: c.inputText,
          fontFamily: font, fontSize: 14, outline: "none",
          boxSizing: "border-box", paddingRight: 36,
        }}
        onFocus={(e) => { e.target.style.borderColor = "#F97316" }}
        onBlur={(e) => { if (!selected) e.target.style.borderColor = c.inputBorder }}
      />
      {/* Dropdown arrow */}
      <div style={{
        position: "absolute", right: 12, top: "50%", transform: open ? "translateY(-50%) rotate(180deg)" : "translateY(-50%)",
        fontSize: 12, color: c.muted, pointerEvents: "none", transition: "transform 0.2s",
      }}>
        ▼
      </div>
    </div>

      {/* Dropdown list */}
      {open && (
        <div style={{
          position: "absolute", top: "100%", left: 0, right: 0, zIndex: 100,
          maxHeight: 220, overflowY: "auto", marginTop: 4,
          background: c.dropdownBg, border: `1px solid ${c.dropdownBorder}`,
          borderRadius: 10, boxShadow: "0 8px 24px rgba(0,0,0,0.3)",
        }}>
          {filtered.length === 0 ? (
            <div style={{ padding: "14px 16px", color: c.muted, fontFamily: font, fontSize: isKhmer ? 13 : 15, textAlign: "center" }}>
              {isKhmer ? "រកមិនឃើញខេត្ត" : "No provinces found"}
            </div>
          ) : (
            filtered.map((p) => {
              const sel = selected?.id === p.id
              return (
                <div
                  key={p.id}
                  onClick={() => handleSelect(p)}
                  style={{
                    padding: "10px 16px", cursor: "pointer",
                    fontFamily: font, fontSize: 14,
                    background: sel ? c.selBg : "transparent",
                    color: sel ? "#F97316" : c.inputText,
                    fontWeight: sel ? 700 : 400,
                    borderBottom: `1px solid ${c.dropdownBorder}22`,
                    transition: "background 0.1s",
                  }}
                  onMouseEnter={(e) => { if (!sel) e.currentTarget.style.background = c.hoverBg }}
                  onMouseLeave={(e) => { if (!sel) e.currentTarget.style.background = "transparent" }}
                >
                  {isKhmer ? p.name_kh : p.name_en}
                </div>
              )
            })
          )}
        </div>
      )}

      {selected && <input type="hidden" name="province_id" value={selected.id} />}
    </div>
  )
}