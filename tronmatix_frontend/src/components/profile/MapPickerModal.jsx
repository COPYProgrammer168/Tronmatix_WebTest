// src/components/profile/MapPickerModal.jsx
import { useState, useEffect } from 'react'
import { useLang } from '../../context/LanguageContext'
import { useTheme } from '../../context/ThemeContext'
import { MapContainer, TileLayer, Marker, Popup, Polyline, useMap, useMapEvents } from 'react-leaflet'
import 'leaflet/dist/leaflet.css'
import L from 'leaflet'

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

// Helper to decode OSRM polyline
function decodePolyline(str, precision = 5) {
    let index = 0, lat = 0, lng = 0, coordinates = [], shift = 0, result = 0, byte = null, lat_change, lng_change, factor = Math.pow(10, precision);
    while (index < str.length) {
        byte = null; shift = 0; result = 0;
        do { byte = str.charCodeAt(index++) - 63; result |= (byte & 0x1f) << shift; shift += 5; } while (byte >= 0x20);
        lat_change = ((result & 1) ? ~(result >> 1) : (result >> 1));
        byte = null; shift = 0; result = 0;
        do { byte = str.charCodeAt(index++) - 63; result |= (byte & 0x1f) << shift; shift += 5; } while (byte >= 0x20);
        lng_change = ((result & 1) ? ~(result >> 1) : (result >> 1));
        lat += lat_change; lng += lng_change;
        coordinates.push([lat / factor, lng / factor]);
    }
    return coordinates;
}

function RoadRouting({ start, end, setRoute }) {
  useEffect(() => {
    if (!start || !end) return
    const url = `https://router.project-osrm.org/route/v1/driving/${start[1]},${start[0]};${end[1]},${end[0]}?overview=full`
    fetch(url)
      .then(res => res.json())
      .then(data => {
        if (data.routes && data.routes.length > 0) {
          setRoute(decodePolyline(data.routes[0].geometry))
        } else {
          setRoute([[start[0], start[1]], [end[0], end[1]]])
        }
      })
      .catch(e => { console.error(e); setRoute([[start[0], start[1]], [end[0], end[1]]]) })
  }, [start, end, setRoute])
  return null
}

function MapUpdater({ position }) {
    const map = useMap();
    useEffect(() => { map.setView([position.lat, position.lng], 14); }, [position, map]);
    return null;
}

function MapClickHandler({ setPosition }) {
  useMapEvents({
    click(e) {
      setPosition({ lat: e.latlng.lat, lng: e.latlng.lng })
    },
  })
  return null
}

export default function MapPickerModal({ onClose, onConfirm, initialLat, initialLng }) {
  const { t, isKhmer } = useLang()
  const { dark } = useTheme()
  const mapFont = isKhmer ? 'Kdam Thmor Pro, sans-serif' : 'Rajdhani, sans-serif'
  const [position, setPosition] = useState({ 
    lat: initialLat || STORE_LAT, 
    lng: initialLng || STORE_LNG 
  })
  const [address, setAddress] = useState('')
  const [loading, setLoading] = useState(false)
  const [route, setRoute] = useState([])

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

  const colors = {
    modalBg:       dark ? "#1f2937" : "#ffffff",
    headerBorder:  dark ? "#374151" : "#f3f4f6",
    titleText:     dark ? "#f9fafb" : "#111111",
    subtitleText:  dark ? "#9ca3af" : "#9ca3af",
    closeBtn:      dark ? "#6b7280" : "#9ca3af",
  }

  return (
    <div style={{
      position: 'fixed', inset: 0, zIndex: 9999,
      background: 'rgba(0,0,0,0.8)', backdropFilter: 'blur(4px)',
      display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 16,
    }}>
      <style>{`
        .leaflet-container { height: 300px !important; width: 100% !important; z-index: 10000; }
        .marker-tag { background: #F97316; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; white-space: nowrap; }
      `}</style>
      <div style={{
        background: colors.modalBg,
        width: '100%', maxWidth: 480, borderRadius: 20, overflow: 'hidden',
        boxShadow: '0 32px 80px rgba(0,0,0,0.7)',
        display: 'flex', flexDirection: 'column', maxHeight: '90vh',
        fontFamily: mapFont,
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
          </div>
          <button onClick={onClose} style={{ background: "none", border: "none", fontSize: 22, cursor: 'pointer', color: colors.closeBtn }}>✕</button>
        </div>

        {/* Map */}
        <div style={{ flex: 1, minHeight: 300, position: 'relative' }}>
          <MapContainer center={[position.lat, position.lng]} zoom={14} style={{ height: '300px', width: '100%' }}>
            <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
            <MapUpdater position={position} />
            <Marker position={[STORE_LAT, STORE_LNG]}>
              <Popup><div className="marker-tag">{t("map.storeLabel")}</div></Popup>
            </Marker>
            <Marker position={[position.lat, position.lng]} draggable={true} eventHandlers={{
                dragend: (e) => setPosition({ lat: e.target.getLatLng().lat, lng: e.target.getLatLng().lng })
            }}>
              <Popup><div className="marker-tag" style={{ background: '#3b82f6' }}>{t("map.userLabel")}</div>{loading ? t("map.loading") : address}</Popup>
            </Marker>
            <MapClickHandler setPosition={setPosition} />
            <Polyline positions={route} color="#F97316" weight={4} dashArray="10, 10" />
            <RoadRouting start={[STORE_LAT, STORE_LNG]} end={[position.lat, position.lng]} setRoute={setRoute} />
          </MapContainer>
        </div>

        {/* Address Display */}
        <div style={{ padding: "15px 20px", fontSize: 14, color: colors.titleText }}>
            <div style={{ fontWeight: 700, marginBottom: 5 }}>{t("map.address")}:</div>
            {loading ? t("map.loading") : address}
        </div>

        {/* Footer */}
        <div style={{ padding: "12px 20px", borderTop: `1px solid ${colors.headerBorder}`, textAlign: "center" }}>
          <button onClick={() => { onConfirm({ lat: position.lat, lng: position.lng, address: address }); onClose() }} 
            style={{
              width: "100%", padding: 14, borderRadius: 12, border: 'none',
              background: "#F97316", color: "#fff", fontWeight: 800, cursor: "pointer",
              fontFamily: mapFont, letterSpacing: 0.5
            }}>
          ✓ {t("map.confirm")}
          </button>
        </div>
      </div>
    </div>
  )
}
