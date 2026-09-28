// src/components/orders/OrderMapView.jsx
import { useEffect, useState } from 'react'
import { MapContainer, TileLayer, Marker, Popup, Polyline, useMap } from 'react-leaflet'
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

function MapUpdater({ center }) {
    const map = useMap();
    useEffect(() => { if (center) map.setView(center, 13); }, [center, map]);
    return null;
}

export default function OrderMapView({ lat, lng, address }) {
  const [route, setRoute] = useState([])

  // Ensure coordinates are valid numbers
  const validUserLat = parseFloat(lat);
  const validUserLng = parseFloat(lng);
  const isValidCoords = !isNaN(validUserLat) && !isNaN(validUserLng);

  // Add Leaflet CSS dynamically to head to bypass bundler/tracking issues
  useEffect(() => {
    if (!document.getElementById('leaflet-css')) {
        const link = document.createElement('link');
        link.id = 'leaflet-css';
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);
    }
  }, []);

  useEffect(() => {
    if (!isValidCoords) return;
    
    const start = [STORE_LAT, STORE_LNG]
    const end = [validUserLat, validUserLng]
    
    const url = `https://router.project-osrm.org/route/v1/driving/${start[1]},${start[0]};${end[1]},${end[0]}?overview=full`
    fetch(url)
      .then(res => res.json())
      .then(data => {
        if (data.routes && data.routes.length > 0) {
          setRoute(decodePolyline(data.routes[0].geometry))
        } else {
          setRoute([start, end])
        }
      })
      .catch(e => { console.error(e); setRoute([start, end]) })
  }, [validUserLat, validUserLng])

  if (!isValidCoords) {
    return <div style={{ height: 400, display: 'flex', alignItems: 'center', justifyContent: 'center', background: '#f3f4f6', borderRadius: 12 }}>Coordinates not available</div>
  }

  return (
    <div style={{ height: 400, borderRadius: 12, overflow: 'hidden', position: 'relative' }}>
        <style>{`
            .leaflet-container { height: 100% !important; width: 100% !important; z-index: 10; }
            .marker-tag { background: #F97316; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; white-space: nowrap; }
        `}</style>
        <MapContainer center={[validUserLat, validUserLng]} zoom={13} style={{ height: '100%', width: '100%' }}>
            <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
            <MapUpdater center={[validUserLat, validUserLng]} />
            <Marker position={[STORE_LAT, STORE_LNG]}>
              <Popup><div className="marker-tag">STORE</div></Popup>
            </Marker>
            <Marker position={[validUserLat, validUserLng]}>
              <Popup><div className="marker-tag" style={{ background: '#3b82f6' }}>CUSTOMER</div>{address}</Popup>
            </Marker>
            <Polyline positions={route} color="#F97316" weight={4} dashArray="10, 10" />
        </MapContainer>
    </div>
  )
}
