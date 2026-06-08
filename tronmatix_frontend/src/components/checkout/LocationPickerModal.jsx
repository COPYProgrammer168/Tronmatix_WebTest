// src/components/checkout/LocationPickerModal.jsx
import { useState, useEffect } from "react"
import { useTheme } from "../../context/ThemeContext"
import { useLang } from "../../context/LanguageContext"
import { MapContainer, TileLayer, Marker, Popup, useMapEvents } from 'react-leaflet'
import 'leaflet/dist/leaflet.css'
import L from 'leaflet'

// Fix for default marker icon
import icon from '../../assets/leaflet/marker-icon.png';
import iconShadow from '../../assets/leaflet/marker-shadow.png';

let DefaultIcon = L.icon({
    iconUrl: icon,
    shadowUrl: iconShadow,
    iconSize: [25, 41],
    iconAnchor: [12, 41]
});
L.Marker.prototype.options.icon = DefaultIcon;

const STORE_LAT = 11.5629735
const STORE_LNG = 104.8995165

function MapClickHandler({ setPosition }) {
  useMapEvents({
    click(e) {
      setPosition({ lat: e.latlng.lat, lng: e.latlng.lng })
    },
  })
  return null
}

export default function LocationPickerModal({ locations, onSelect, onClose }) {
  const { dark } = useTheme()
  const { t, isKhmer } = useLang()
  const modalFont = isKhmer ? "Kh_Jrung_Thom, Khmer OS, sans-serif" : "Rajdhani, sans-serif"
  
  const [position, setPosition] = useState({ lat: STORE_LAT, lng: STORE_LNG })
  const [address, setAddress] = useState('')
  const [loading, setLoading] = useState(false)

  // Reverse Geocoding
  const fetchAddress = async (lat, lng) => {
    setLoading(true)
    try {
      const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
      const data = await response.json()
      setAddress(data.display_name || t("map.addressNotFound"))
    } catch (e) {
      setAddress(t("map.error"))
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    fetchAddress(position.lat, position.lng)
  }, [position])

  // Semantic color tokens
  const colors = {
    overlay:       "rgba(0,0,0,0.60)",
    modalBg:       dark ? "#1f2937" : "#ffffff",
    headerBorder:  dark ? "#374151" : "#f3f4f6",
    titleText:     dark ? "#f9fafb" : "#111111",
    subtitleText:  dark ? "#9ca3af" : "#9ca3af",
    closeBtn:      dark ? "#6b7280" : "#9ca3af",
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
      <style>{`
        .leaflet-container { height: 300px !important; width: 100% !important; z-index: 10000; }
      `}</style>
      <div style={{
        background: colors.modalBg,
        borderRadius: 20, width: "100%", maxWidth: 480,
        boxShadow: "0 24px 64px rgba(0,0,0,0.35)", overflow: "hidden",
        fontFamily: modalFont,
      }}>

        {/* Header */}
        <div style={{
          padding: "18px 20px",
          borderBottom: `1px solid ${colors.headerBorder}`,
          display: "flex", justifyContent: "space-between", alignItems: "center",
        }}>
          <div>
            <div style={{ fontSize: 20, fontWeight: 800, letterSpacing: 1, color: colors.titleText }}>
              {t("map.title")}
            </div>
            <div style={{ fontSize: 13, color: colors.subtitleText, marginTop: 2 }}>
              {t("map.subtitle")}
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

        {/* Map */}
        <div style={{ height: 300, width: '100%' }}>
          <MapContainer center={[position.lat, position.lng]} zoom={14} style={{ height: '300px', width: '100%' }}>
            <TileLayer
              attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
              url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />
            <Marker position={[position.lat, position.lng]}>
              <Popup>{loading ? t("map.loading") : address}</Popup>
            </Marker>
            <MapClickHandler setPosition={setPosition} />
          </MapContainer>
        </div>

        {/* Address Display */}
        <div style={{ padding: "15px 20px", fontSize: 14, color: colors.titleText }}>
            <div style={{ fontWeight: 700, marginBottom: 5 }}>{t("map.address")}:</div>
            {loading ? t("map.loading") : address}
        </div>

        {/* Footer */}
        <div style={{
          padding: "12px 20px",
          textAlign: "center",
        }}>
          <button 
            onClick={() => { onSelect({ lat: position.lat, lng: position.lng, address: address }); onClose() }}
            style={{
              width: "100%", padding: 14, borderRadius: 12, border: "none",
              background: "#F97316", color: "#fff", fontWeight: 800, cursor: "pointer",
              fontFamily: modalFont, letterSpacing: 0.5
            }}
          >
            ✓ {t("map.confirm")}
          </button>
        </div>
      </div>
    </div>
  )
}
