// src/components/orders/OrderMapView.jsx
// Read-only Google Map — store pin, customer pin, dashed delivery line

import { useEffect, useRef } from 'react'

// ✅ FIXED: Real Tronmatix Computer Store coordinates (was 11.5625 / 104.9019)
const STORE_LAT = 11.5629735
const STORE_LNG = 104.8995165
const GOOGLE_KEY = import.meta.env.VITE_GOOGLE_MAPS_KEY

export default function OrderMapView({ lat, lng, address }) {
  const mapContainer = useRef(null)
  const mapRef       = useRef(null)

  useEffect(() => {
    if (!lat || !lng || !GOOGLE_KEY) return

    const userLat = parseFloat(lat)
    const userLng = parseFloat(lng)

    const init = () => {
      if (!mapContainer.current || mapRef.current) return
      const google = window.google

      const map = new google.maps.Map(mapContainer.current, {
        center: {
          lat: (STORE_LAT + userLat) / 2,
          lng: (STORE_LNG + userLng) / 2,
        },
        zoom: 12,
        styles: [
          { elementType: 'geometry',         stylers: [{ color: '#1a1a2e' }] },
          { elementType: 'labels.text.fill',  stylers: [{ color: '#8ec3b9' }] },
          { featureType: 'road',              elementType: 'geometry', stylers: [{ color: '#2d3561' }] },
          { featureType: 'water',             elementType: 'geometry', stylers: [{ color: '#0f3460' }] },
          { featureType: 'poi',               stylers: [{ visibility: 'off' }] },
        ],
        disableDefaultUI: true,
        zoomControl: true,
      })

      mapRef.current = map

      // Store pin (orange)
      new google.maps.Marker({
        position: { lat: STORE_LAT, lng: STORE_LNG },
        map,
        title: '🏪 Tronmatix Computer',
        icon: {
          path: google.maps.SymbolPath.CIRCLE,
          scale: 9,
          fillColor: '#F97316',
          fillOpacity: 1,
          strokeColor: '#fff',
          strokeWeight: 2,
        },
      })

      // Customer pin (blue)
      new google.maps.Marker({
        position: { lat: userLat, lng: userLng },
        map,
        title: address || 'Customer Location',
        icon: {
          path: google.maps.SymbolPath.CIRCLE,
          scale: 9,
          fillColor: '#3b82f6',
          fillOpacity: 1,
          strokeColor: '#fff',
          strokeWeight: 2,
        },
      })

      // Dashed delivery line
      new google.maps.Polyline({
        path: [
          { lat: STORE_LAT, lng: STORE_LNG },
          { lat: userLat,   lng: userLng   },
        ],
        geodesic: true,
        strokeColor: '#F97316',
        strokeOpacity: 0,
        icons: [{
          icon: { path: 'M 0,-1 0,1', strokeOpacity: 1, scale: 3, strokeColor: '#F97316' },
          offset: '0',
          repeat: '12px',
        }],
        map,
      })

      // Fit bounds to show both pins
      const bounds = new google.maps.LatLngBounds()
      bounds.extend({ lat: STORE_LAT, lng: STORE_LNG })
      bounds.extend({ lat: userLat,   lng: userLng   })
      map.fitBounds(bounds, 60)
    }

    if (window.google?.maps) { init(); return }

    const existing = document.getElementById('google-maps-script')
    if (!existing) {
      const script = document.createElement('script')
      script.id    = 'google-maps-script'
      script.src   = `https://maps.googleapis.com/maps/api/js?key=${GOOGLE_KEY}`
      script.async = true
      script.onload = init
      document.head.appendChild(script)
    } else {
      existing.addEventListener('load', init)
      if (window.google?.maps) init()
    }

    return () => { mapRef.current = null }
  }, [lat, lng])

  if (!lat || !lng) return null

  return (
    <div style={{ borderRadius: 12, overflow: 'hidden', border: '1px solid rgba(255,255,255,0.08)' }}>
      <div ref={mapContainer} style={{ height: 220 }} />
      {address && (
        <div style={{
          padding: '8px 12px',
          background: 'rgba(255,255,255,0.03)',
          fontSize: 12,
          color: 'rgba(255,255,255,0.5)',
          borderTop: '1px solid rgba(255,255,255,0.06)',
        }}>
          📍 {address}
        </div>
      )}
    </div>
  )
}
