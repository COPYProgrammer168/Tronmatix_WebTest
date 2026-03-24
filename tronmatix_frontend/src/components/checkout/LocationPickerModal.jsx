// src/components/checkout/LocationPickerModal.jsx
import { useTheme } from "../../context/ThemeContext"

export default function LocationPickerModal({ locations, onSelect, onClose }) {
  const { dark } = useTheme()
  const [loading, setLoading] = useState(false);

  // Semantic color tokens — all theme-aware
  const colors = {
    overlay:       "rgba(0,0,0,0.60)",
    modalBg:       dark ? "#1f2937" : "#ffffff",
    headerBorder:  dark ? "#374151" : "#f3f4f6",
    footerBorder:  dark ? "#374151" : "#f3f4f6",
    titleText:     dark ? "#f9fafb" : "#111111",
    subtitleText:  dark ? "#9ca3af" : "#9ca3af",
    closeBtn:      dark ? "#6b7280" : "#9ca3af",
    cardBg:        dark ? "#111827" : "#ffffff",
    cardBorder:    dark ? "#374151" : "#e5e7eb",
    cardBorderHover: "#F97316",
    namText:       dark ? "#f9fafb" : "#111111",
    phoneText:     dark ? "#9ca3af" : "#6b7280",
    addressText:   dark ? "#d1d5db" : "#4b5563",
    noteText:      dark ? "#6b7280" : "#9ca3af",
  }

  return (
    <div
      onClick={(e) => e.target === e.currentTarget && onClose()}
      style={{
        position: "fixed", inset: 0, zIndex: 9999,
        background: colors.overlay, backdropFilter: "blur(4px)",
        display: "flex", alignItems: "center", justifyContent: "center", padding: 16,
      }}
    >
      <div style={{
        background: colors.modalBg,
        borderRadius: 20, width: "100%", maxWidth: 480,
        boxShadow: "0 24px 64px rgba(0,0,0,0.35)", overflow: "hidden",
        fontFamily: "Rajdhani, sans-serif",
      }}>

        {/* Header */}
        <div style={{
          padding: "18px 20px",
          borderBottom: `1px solid ${colors.headerBorder}`,
          display: "flex", justifyContent: "space-between", alignItems: "center",
        }}>
          <div>
            <div style={{ fontSize: 20, fontWeight: 800, letterSpacing: 1, color: colors.titleText }}>
              📍 Select Delivery Location
            </div>
            <div style={{ fontSize: 13, color: colors.subtitleText, marginTop: 2 }}>
              Choose from your saved addresses
            </div>
          </div>
          <button
            onClick={onClose}
            style={{
              background: "none", border: "none", fontSize: 22,
              cursor: "pointer", color: colors.closeBtn, lineHeight: 1,
            }}
          >✕</button>
        </div>

                {/* Location list */}
        <div style={{
          maxHeight: 380, overflowY: "auto",
          padding: "12px 14px", display: "flex", flexDirection: "column", gap: 10,
        }}>
          {(!locations || locations.length === 0) && (
            <p style={{ fontSize: 14, color: colors.subtitleText, textAlign: "center", padding: "24px 0" }}>
              No saved addresses found.
            </p>
          )}

          {Array.isArray(locations) && locations.map((loc) => (
            <button
              key={loc.id}
              onClick={() => { onSelect(loc); onClose() }}
              style={{
                width: "100%", textAlign: "left",
                background: colors.cardBg,
                border: `${loc.is_default ? "2px" : "1.5px"} solid ${loc.is_default ? "#F97316" : colors.cardBorder}`,
                borderRadius: 14, padding: "13px 15px", cursor: "pointer",
                position: "relative", transition: "border-color 0.15s",
              }}
              onMouseEnter={(e) => { e.currentTarget.style.borderColor = colors.cardBorderHover }}
              onMouseLeave={(e) => { e.currentTarget.style.borderColor = loc.is_default ? "#F97316" : colors.cardBorder }}
            >
              {/* Default badge */}
              {loc.is_default && (
                <span style={{
                  position: "absolute", top: 0, right: 14,
                  background: "#F97316", color: "#fff",
                  fontSize: 10, fontWeight: 700, letterSpacing: 1,
                  padding: "2px 10px", borderRadius: "0 0 8px 8px",
                }}>DEFAULT</span>
              )}

              <div style={{ display: "flex", gap: 12, alignItems: "flex-start" }}>
                <span style={{ fontSize: 22, marginTop: 1 }}>📍</span>
                <div style={{ flex: 1 }}>
                  <div style={{ fontWeight: 800, fontSize: 15, color: colors.namText }}>
                    {loc.name}
                  </div>
                  <div style={{ fontSize: 13, color: colors.phoneText, marginTop: 2 }}>
                    📞 {loc.phone}
                  </div>
                  <div style={{ fontSize: 13, color: colors.addressText, marginTop: 3 }}>
                    {loc.address}{loc.city ? `, ${loc.city}` : ""}
                  </div>
                  {loc.note && (
                    <div style={{ fontSize: 12, color: colors.noteText, marginTop: 3, fontStyle: "italic" }}>
                      📝 {loc.note}
                    </div>
                  )}
                </div>
                <span style={{ color: "#F97316", fontSize: 20, alignSelf: "center" }}>›</span>
              </div>
            </button>
          ))}
        </div>

        {/* Footer */}
        <div style={{
          padding: "12px 20px",
          borderTop: `1px solid ${colors.footerBorder}`,
          textAlign: "center",
        }}>
          <a
            href="/profile"
            onClick={onClose}
            style={{ fontSize: 13, color: "#F97316", fontWeight: 700, textDecoration: "none", letterSpacing: 0.5 }}
          >
            + Manage addresses in My Profile
          </a>
        </div>
      </div>
    </div>
  )
}
