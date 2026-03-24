// src/components/profile/MapPickerModal.jsx
import { useEffect, useRef, useState } from 'react'

const STORE_LAT = 11.5629735
const STORE_LNG = 104.8995165
const GOOGLE_KEY = import.meta.env.VITE_GOOGLE_MAPS_KEY

// Load Google Maps script once and return a promise that resolves when ready
let _mapsPromise = null
function loadGoogleMapsOnce() {
  if (_mapsPromise) return _mapsPromise
  _mapsPromise = new Promise((resolve, reject) => {
    if (window.google?.maps?.marker?.AdvancedMarkerElement) {
      resolve(window.google)
      return
    }
    // Remove any stale script tag to avoid duplicate-load issues on Render
    const existing = document.getElementById('gmaps-sdk')
    if (existing) existing.remove()

    const script = document.createElement('script')
    script.id    = 'gmaps-sdk'
    // loading=async is required for the new Maps JS API on production
    script.src   = `https://maps.googleapis.com/maps/api/js?key=${GOOGLE_KEY}&libraries=marker&v=weekly&loading=async`
    script.async = true
    script.defer = true
    script.onload  = () => resolve(window.google)
    script.onerror = (e) => { _mapsPromise = null; reject(e) }
    document.head.appendChild(script)
  })
  return _mapsPromise
}

export default function MapPickerModal({ onClose, onConfirm, initialLat, initialLng }) {
  const mapContainer = useRef(null)
  const mapRef       = useRef(null)
  const markerRef    = useRef(null)
  const geocoderRef  = useRef(null)

  const [lat,     setLat]     = useState(initialLat || STORE_LAT)
  const [lng,     setLng]     = useState(initialLng || STORE_LNG)
  const [address, setAddress] = useState('')
  const [loading, setLoading] = useState(false)
  const [mapError, setMapError] = useState('')

  useEffect(() => {
    let cancelled = false
    loadGoogleMapsOnce()
      .then((google) => {
        if (cancelled || !mapContainer.current || mapRef.current) return

        const map = new google.maps.Map(mapContainer.current, {
          center: { lat: initialLat || STORE_LAT, lng: initialLng || STORE_LNG },
          zoom: 14,
          mapId: 'tronmatix_picker',
          disableDefaultUI: false,
          zoomControl: true,
          mapTypeControl: false,
          streetViewControl: false,
          fullscreenControl: false,
        })
        mapRef.current   = map
        geocoderRef.current = new google.maps.Geocoder()

        // Store pin (orange dot)
        const storeEl = document.createElement('div')
        storeEl.style.cssText = 'width:16px;height:16px;border-radius:50%;background:#F97316;border:2px solid #fff;box-shadow:0 2px 8px rgba(249,115,22,0.6);cursor:default;'
        new google.maps.marker.AdvancedMarkerElement({
          position: { lat: STORE_LAT, lng: STORE_LNG },
          map,
          title: '🏪 Tronmatix Computer',
          content: storeEl,
        })

        // User pin (blue dot, draggable)
        const userEl = document.createElement('div')
        userEl.style.cssText = 'width:18px;height:18px;border-radius:50%;background:#3b82f6;border:2px solid #fff;box-shadow:0 2px 8px rgba(59,130,246,0.6);cursor:grab;'
        const userMarker = new google.maps.marker.AdvancedMarkerElement({
          position: { lat: initialLat || STORE_LAT, lng: initialLng || STORE_LNG },
          map,
          title: 'Your Location',
          content: userEl,
          gmpDraggable: true,
        })
        markerRef.current = userMarker

        // Dashed line store → user
        const line = new google.maps.Polyline({
          path: [
            { lat: STORE_LAT, lng: STORE_LNG },
            { lat: initialLat || STORE_LAT, lng: initialLng || STORE_LNG },
          ],
          geodesic: true,
          strokeColor: '#F97316',
          strokeOpacity: 0,
          icons: [{ icon: { path: 'M 0,-1 0,1', strokeOpacity: 1, scale: 3 }, offset: '0', repeat: '12px' }],
          map,
        })

        const updateAll = (newLat, newLng) => {
          setLat(newLat); setLng(newLng)
          line.setPath([{ lat: STORE_LAT, lng: STORE_LNG }, { lat: newLat, lng: newLng }])
          reverseGeocode(newLat, newLng)
        }

        userMarker.addListener('dragend', () => {
          const pos = userMarker.position
          if (pos) {
            updateAll(pos.lat, pos.lng)
          }
        })
        map.addListener('click', (e) => {
          if (userMarker) {
            userMarker.position = e.latLng
            updateAll(e.latLng.lat(), e.latLng.lng())
          }
        })

        reverseGeocode(initialLat || STORE_LAT, initialLng || STORE_LNG)
      })
      .catch(() => {
        if (!cancelled) setMapError('Failed to load Google Maps. Please check your API key and billing.')
      })

    return () => { cancelled = true; mapRef.current = null }
  }, []) // eslint-disable-line

  // Use the already-loaded Geocoder (same key, no extra fetch, no CORS issue on Render)
  const reverseGeocode = (lat, lng) => {
    if (!geocoderRef.current) return
    setLoading(true)
    geocoderRef.current.geocode({ location: { lat, lng }, language: 'en' }, (results, status) => {
      setLoading(false)
      if (status === 'OK' && results?.[0]) {
        setAddress(results[0].formatted_address)
      } else {
        setAddress('')
      }
    })
  }

  const handleConfirm = () => {
    onConfirm({ lat, lng, address })
    onClose()
  }

  return (
    <div style={{
      position: 'fixed', inset: 0, zIndex: 9999,
      background: 'rgba(0,0,0,0.8)', backdropFilter: 'blur(4px)',
      display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 16,
    }}>
      <div style={{
        width: '100%', maxWidth: 600, borderRadius: 20, overflow: 'hidden',
        background: '#111827', border: '1px solid rgba(255,255,255,0.1)',
        boxShadow: '0 32px 80px rgba(0,0,0,0.7)',
        display: 'flex', flexDirection: 'column', maxHeight: '90vh',
      }}>
        {/* Header */}
        <div style={{
          display: 'flex', alignItems: 'center', justifyContent: 'space-between',
          padding: '16px 20px', borderBottom: '1px solid rgba(255,255,255,0.08)', flexShrink: 0,
        }}>
          <div>
            <div style={{ fontSize: 16, fontWeight: 800, color: '#fff', letterSpacing: 1 }}>
              📍 PIN YOUR LOCATION
            </div>
            <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.4)', marginTop: 2 }}>
              Tap the map or drag the blue pin to your location
            </div>
          </div>
          <button onClick={onClose} style={{
            width: 32, height: 32, borderRadius: 8, border: '1px solid rgba(255,255,255,0.1)',
            background: 'rgba(255,255,255,0.05)', color: 'rgba(255,255,255,0.5)',
            fontSize: 16, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center',
          }}>✕</button>
        </div>

        {/* Map */}
        <div ref={mapContainer} style={{ flex: 1, minHeight: 340, position: 'relative' }}>
          {mapError && (
            <div style={{
              position: 'absolute', inset: 0, display: 'flex', alignItems: 'center',
              justifyContent: 'center', background: '#0f172a', flexDirection: 'column', gap: 12, padding: 24,
            }}>
              <span style={{ fontSize: 32 }}>⚠️</span>
              <p style={{ color: '#ef4444', fontSize: 14, textAlign: 'center', margin: 0 }}>{mapError}</p>
            </div>
          )}
        </div>

        {/* Legend */}
        <div style={{
          padding: '8px 20px', display: 'flex', gap: 20, flexShrink: 0,
          borderTop: '1px solid rgba(255,255,255,0.06)', background: 'rgba(0,0,0,0.2)',
        }}>
          <span style={{ fontSize: 12, color: 'rgba(255,255,255,0.4)', display: 'flex', alignItems: 'center', gap: 6 }}>
            <span style={{ width: 10, height: 10, borderRadius: '50%', background: '#F97316', display: 'inline-block' }} />
            Tronmatix Store
          </span>
          <span style={{ fontSize: 12, color: 'rgba(255,255,255,0.4)', display: 'flex', alignItems: 'center', gap: 6 }}>
            <span style={{ width: 10, height: 10, borderRadius: '50%', background: '#3b82f6', display: 'inline-block' }} />
            Your Location (drag me)
          </span>
        </div>

        {/* Address + confirm */}
        <div style={{ padding: '14px 20px', borderTop: '1px solid rgba(255,255,255,0.08)', flexShrink: 0 }}>
          <div style={{
            background: 'rgba(255,255,255,0.04)', border: '1px solid rgba(255,255,255,0.1)',
            borderRadius: 10, padding: '10px 14px', marginBottom: 10, minHeight: 42,
          }}>
            {loading
              ? <span style={{ color: 'rgba(255,255,255,0.3)', fontSize: 13 }}>📍 Getting address...</span>
              : <span style={{ color: address ? '#fff' : 'rgba(255,255,255,0.3)', fontSize: 13 }}>
                  {address || 'Tap the map to select your location'}
                </span>
            }
          </div>
          <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.25)', marginBottom: 12 }}>
            Lat: {typeof lat === 'number' ? lat.toFixed(6) : lat} | Lng: {typeof lng === 'number' ? lng.toFixed(6) : lng}
          </div>
          <button onClick={handleConfirm} style={{
            width: '100%', padding: '11px 0', borderRadius: 10, border: 'none',
            background: 'linear-gradient(135deg,#F97316,#ea580c)',
            color: '#fff', fontFamily: 'Rajdhani, sans-serif',
            fontSize: 15, fontWeight: 800, letterSpacing: 1, cursor: 'pointer',
          }}>
            ✅ CONFIRM THIS LOCATION
          </button>
        </div>
      </div>
    </div>
  )
}
