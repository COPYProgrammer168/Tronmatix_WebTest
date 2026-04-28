// src/components/profile/MapPickerModal.jsx
import { useEffect, useRef, useState } from 'react'
import { useLang } from '../../context/LanguageContext'

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
    const existing = document.getElementById('gmaps-sdk')
    if (existing) existing.remove()

    const script = document.createElement('script')
    script.id    = 'gmaps-sdk'
    script.src   = `https://maps.googleapis.com/maps/api/js?key=${GOOGLE_KEY}&libraries=marker&v=weekly&loading=async`
    script.async = true
    script.defer = true
    script.onload  = () => resolve(window.google)
    script.onerror = (e) => { _mapsPromise = null; reject(e) }
    document.head.appendChild(script)
  })
  return _mapsPromise
}

// Regex to extract lat/lng from various Google Maps URL formats
function extractCoordsFromGoogleMapsUrl(url) {
  if (!url) return null

  // Format 1: @lat,lng,zoom
  const atMatch = url.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/)
  if (atMatch) return { lat: parseFloat(atMatch[1]), lng: parseFloat(atMatch[2]) }

  // Format 2: /maps?q=lat,lng
  const qMatch = url.match(/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/)
  if (qMatch) return { lat: parseFloat(qMatch[1]), lng: parseFloat(qMatch[2]) }

  // Format 3: /maps?daddr=lat,lng
  const daddrMatch = url.match(/[?&]daddr=(-?\d+\.\d+),(-?\d+\.\d+)/)
  if (daddrMatch) return { lat: parseFloat(daddrMatch[1]), lng: parseFloat(daddrMatch[2]) }

  // Format 4: /maps/place/... with data params
  const placeMatch = url.match(/\/maps\/place\//)
  if (placeMatch) {
    const dataMatch = url.match(/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/)
    if (dataMatch) return { lat: parseFloat(dataMatch[1]), lng: parseFloat(dataMatch[2]) }
  }

  return null
}

function isValidGoogleMapsUrl(url) {
  try {
    const urlObj = new URL(url)
    return urlObj.hostname.includes('google.com') && urlObj.pathname.includes('/maps')
  } catch {
    return false
  }
}

export default function MapPickerModal({ onClose, onConfirm, initialLat, initialLng }) {
  const mapContainer = useRef(null)
  const mapRef       = useRef(null)
  const markerRef    = useRef(null)
  const geocoderRef  = useRef(null)

  const { t, isKhmer } = useLang()
  const mapFont = isKhmer ? 'KantumruyPro, Khmer OS, sans-serif' : 'Rajdhani, sans-serif'
  const [lat,     setLat]     = useState(initialLat || STORE_LAT)
  const [lng,     setLng]     = useState(initialLng || STORE_LNG)
  const [address, setAddress] = useState('')
  const [loading, setLoading] = useState(false)
  const [mapError, setMapError] = useState('')

  // Search & paste link state
  const [googleMapsLink, setGoogleMapsLink] = useState('')
  const [linkError, setLinkError] = useState('')
  const [searchQuery, setSearchQuery] = useState('')
  const [isSearching, setIsSearching] = useState(false)
  const [searchResults, setSearchResults] = useState([])
  const [showSearchPanel, setShowSearchPanel] = useState(false)

  // Handle Google Maps link paste
  const handlePasteGoogleMapsLink = async () => {
    setLinkError('')
    if (!googleMapsLink.trim()) {
      setLinkError(isKhmer ? 'សូមបិទភ្ជាប់តំណ Google Maps ជាមុនសិន' : 'Please paste a Google Maps link first')
      return
    }
    if (!isValidGoogleMapsUrl(googleMapsLink)) {
      setLinkError(isKhmer ? 'URL Google Maps មិនត្រឹមត្រូវ' : 'Invalid Google Maps URL')
      return
    }
    const coords = extractCoordsFromGoogleMapsUrl(googleMapsLink)
    if (coords) {
      // Validate coordinates are within Cambodia bounds
      if (coords.lat < 10 || coords.lat > 14.5 || coords.lng < 102 || coords.lng > 108) {
        setLinkError(isKhmer ? 'ទីតាំងហាក់ដូចជានៅក្រៅប្រទេសកម្ពុជា។ សូមពិនិត្យម្តងទៀត។' : 'Location appears to be outside Cambodia. Please verify.')
        return
      }
      setLat(coords.lat)
      setLng(coords.lng)
      setGoogleMapsLink('')
      setShowSearchPanel(false)

      // Reverse geocode to get address
      setLoading(true)
      try {
        const response = await fetch(
          `https://maps.googleapis.com/maps/api/geocode/json?latlng=${coords.lat},${coords.lng}&key=${GOOGLE_KEY}`
        )
        const data = await response.json()
        if (data.status === 'OK' && data.results?.[0]) {
          setAddress(data.results[0].formatted_address)
        } else {
          setAddress('')
        }
      } catch (err) {
        console.error('Reverse geocoding failed:', err)
      } finally {
        setLoading(false)
      }
    } else {
      setLinkError(isKhmer ? 'មិនអាចស្រង់កូអរដោនេពី URL នេះបានទេ។ សូមចម្លងតំណដែលមានកូអរដោនេច្បាស់លាស់។' : 'Could not extract coordinates from this URL. Try copying a link with visible coordinates.')
    }
  }

  // Handle location search
  const handleSearchLocation = async () => {
    if (!searchQuery.trim()) return
    setIsSearching(true)
    setSearchResults([])
    try {
      const response = await fetch(
        `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(searchQuery)}&key=${GOOGLE_KEY}`
      )
      const data = await response.json()
      if (data.status === 'OK' && data.results?.length > 0) {
        setSearchResults(data.results.slice(0, 5))
      }
    } catch (err) {
      console.error('Search failed:', err)
    } finally {
      setIsSearching(false)
    }
  }

  const handleSelectSearchResult = (result) => {
    const { lat, lng } = result.geometry.location
    setLat(lat)
    setLng(lng)
    setAddress(result.formatted_address)
    setSearchResults([])
    setSearchQuery('')
    setShowSearchPanel(false)
  }

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

  // Use the already-loaded Geocoder
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
              {isKhmer ? t('map.title') : '📍 PIN YOUR LOCATION'}
            </div>
            <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.4)', marginTop: 2 }}>
              {isKhmer ? t('map.subtitle') : 'Tap the map, drag the pin, paste a link, or search'}
            </div>
          </div>
          <button onClick={onClose} style={{
            width: 32, height: 32, borderRadius: 8, border: '1px solid rgba(255,255,255,0.1)',
            background: 'rgba(255,255,255,0.05)', color: 'rgba(255,255,255,0.5)',
            fontSize: 16, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center',
          }}>✕</button>
        </div>

        {/* Search & Paste Panel */}
        <div style={{
          padding: '12px 20px',
          borderBottom: '1px solid rgba(255,255,255,0.08)',
          background: 'rgba(0,0,0,0.2)',
        }}>
          <button
            onClick={() => setShowSearchPanel(!showSearchPanel)}
            style={{
              width: '100%', padding: '10px 14px', borderRadius: 10,
              border: '1px solid rgba(59,130,246,0.3)',
              background: showSearchPanel ? 'rgba(59,130,246,0.15)' : 'transparent',
              color: '#60a5fa', fontFamily: mapFont, fontSize: 14, fontWeight: 700,
              cursor: 'pointer', display: 'flex', alignItems: 'center', gap: 8,
            }}
          >
            <span>🔍</span>
            {showSearchPanel
              ? (isKhmer ? 'បិទផ្ទាំងស្វែងរក' : 'Hide Search Options')
              : (isKhmer ? '🔎 ស្វែងរក ឬ បិទភ្ជាប់តំណ Google Maps' : '🔎 Search or Paste Google Maps Link')}
          </button>

          {showSearchPanel && (
            <div style={{ marginTop: 12, display: 'flex', flexDirection: 'column', gap: 10 }}>
              {/* Paste Google Maps Link */}
              <div>
                <label style={{ fontSize: 11, color: 'rgba(255,255,255,0.5)', fontWeight: 700, marginBottom: 4, display: 'block' }}>
                  {isKhmer ? '🔗 បិទភ្ជាប់តំណ Google Maps' : '🔗 Paste Google Maps Link'}
                </label>
                <div style={{ display: 'flex', gap: 6 }}>
                  <input
                    type="url"
                    value={googleMapsLink}
                    onChange={(e) => setGoogleMapsLink(e.target.value)}
                    onKeyDown={(e) => e.key === 'Enter' && handlePasteGoogleMapsLink()}
                    placeholder={isKhmer ? 'បិទភ្ជាប់ URL Google Maps...' : 'Paste Google Maps URL...'}
                    style={{
                      flex: 1, padding: '8px 10px', borderRadius: 8, border: '1px solid rgba(255,255,255,0.1)',
                      background: 'rgba(255,255,255,0.05)', color: '#fff', fontSize: 13,
                      fontFamily: mapFont, outline: 'none',
                    }}
                  />
                  <button
                    onClick={handlePasteGoogleMapsLink}
                    disabled={isSearching}
                    style={{
                      padding: '8px 14px', borderRadius: 8, border: 'none',
                      background: '#3b82f6', color: '#fff', fontSize: 12, fontWeight: 700,
                      cursor: isSearching ? 'not-allowed' : 'pointer',
                      opacity: isSearching ? 0.5 : 1,
                    }}
                  >
                    {isSearching ? '...' : (isKhmer ? 'ស្រង់' : 'EXTRACT')}
                  </button>
                </div>
                {linkError && (
                  <div style={{ fontSize: 11, color: '#ef4444', marginTop: 4 }}>⚠️ {linkError}</div>
                )}
              </div>

              {/* Search Address */}
              <div>
                <label style={{ fontSize: 11, color: 'rgba(255,255,255,0.5)', fontWeight: 700, marginBottom: 4, display: 'block' }}>
                  {isKhmer ? '🔎 ស្វែងរកអាសយដ្ឋាន' : '🔎 Search Address'}
                </label>
                <div style={{ display: 'flex', gap: 6, position: 'relative' }}>
                  <input
                    type="text"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    onKeyDown={(e) => e.key === 'Enter' && handleSearchLocation()}
                    placeholder={isKhmer ? 'បញ្ចូលអាសយដ្ឋាន ឬឈ្មោះទីតាំង...' : 'Enter address or place name...'}
                    style={{
                      flex: 1, padding: '8px 10px', borderRadius: 8, border: '1px solid rgba(255,255,255,0.1)',
                      background: 'rgba(255,255,255,0.05)', color: '#fff', fontSize: 13,
                      fontFamily: mapFont, outline: 'none',
                    }}
                  />
                  <button
                    onClick={handleSearchLocation}
                    disabled={isSearching}
                    style={{
                      padding: '8px 14px', borderRadius: 8, border: 'none',
                      background: '#10b981', color: '#fff', fontSize: 12, fontWeight: 700,
                      cursor: isSearching ? 'not-allowed' : 'pointer',
                      opacity: isSearching ? 0.5 : 1,
                    }}
                  >
                    {isSearching ? '...' : (isKhmer ? 'ស្វែងរក' : 'SEARCH')}
                  </button>
                </div>

                {/* Search Results */}
                {searchResults.length > 0 && (
                  <div style={{
                    marginTop: 6, maxHeight: 150, overflowY: 'auto',
                    borderRadius: 8, border: '1px solid rgba(255,255,255,0.1)',
                    background: 'rgba(255,255,255,0.05)',
                  }}>
                    {searchResults.map((result, idx) => (
                      <button
                        key={idx}
                        onClick={() => handleSelectSearchResult(result)}
                        style={{
                          width: '100%', padding: '8px 10px', textAlign: 'left',
                          background: 'transparent', border: 'none',
                          borderBottom: '1px solid rgba(255,255,255,0.05)',
                          color: '#e5e7eb', fontSize: 12, cursor: 'pointer',
                          fontFamily: mapFont,
                        }}
                        onMouseEnter={(e) => e.target.style.background = 'rgba(59,130,246,0.1)'}
                        onMouseLeave={(e) => e.target.style.background = 'transparent'}
                      >
                        📍 {result.formatted_address}
                      </button>
                    ))}
                  </div>
                )}
              </div>
            </div>
          )}
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
              ? <span style={{ color: 'rgba(255,255,255,0.3)', fontSize: 13, fontFamily: mapFont }}>{isKhmer ? t('map.gettingAddress') : '📍 Getting address...'}</span>
              : <span style={{ color: address ? '#fff' : 'rgba(255,255,255,0.3)', fontSize: 13 }}>
                  {address || (isKhmer ? t('map.tapToSelect') : 'Tap the map to select your location')}
                </span>
            }
          </div>
          <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.25)', marginBottom: 12 }}>
            Lat: {typeof lat === 'number' ? lat.toFixed(6) : lat} | Lng: {typeof lng === 'number' ? lng.toFixed(6) : lng}
          </div>
          <button onClick={handleConfirm} style={{
            width: '100%', padding: '11px 0', borderRadius: 10, border: 'none',
            background: 'linear-gradient(135deg,#F97316,#ea580c)',
            color: '#fff', fontFamily: mapFont,
            fontSize: 15, fontWeight: 800, letterSpacing: isKhmer ? 0 : 1, cursor: 'pointer',
          }}>
            {isKhmer ? t('map.confirm') : '✅ CONFIRM THIS LOCATION'}
          </button>
        </div>
      </div>
    </div>
  )
}
