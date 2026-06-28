// src/components/profile/MapPickerModal.jsx
import { useState, useEffect, useRef, useCallback } from "react";
import { useLang } from "../../context/LanguageContext";
import { useTheme } from "../../context/ThemeContext";
import {
  MapContainer,
  TileLayer,
  Marker,
  Popup,
  Polyline,
  useMap,
  useMapEvents,
} from "react-leaflet";
import "leaflet/dist/leaflet.css";
import L from "leaflet";

import icon from "../../assets/leaflet/marker-icon.png";
import iconShadow from "../../assets/leaflet/marker-shadow.png";

let DefaultIcon = L.icon({
  iconUrl: icon,
  shadowUrl: iconShadow,
  iconSize: [25, 41],
  iconAnchor: [12, 41],
});
L.Marker.prototype.options.icon = DefaultIcon;

const STORE_LAT = 11.5629735;
const STORE_LNG = 104.8995165;

// ── Decode OSRM polyline ──────────────────────────────────────────────────────
function decodePolyline(str, precision = 5) {
  let index = 0,
    lat = 0,
    lng = 0,
    coordinates = [],
    shift = 0,
    result = 0,
    byte = null,
    lat_change,
    lng_change,
    factor = Math.pow(10, precision);
  while (index < str.length) {
    byte = null;
    shift = 0;
    result = 0;
    do {
      byte = str.charCodeAt(index++) - 63;
      result |= (byte & 0x1f) << shift;
      shift += 5;
    } while (byte >= 0x20);
    lat_change = result & 1 ? ~(result >> 1) : result >> 1;
    byte = null;
    shift = 0;
    result = 0;
    do {
      byte = str.charCodeAt(index++) - 63;
      result |= (byte & 0x1f) << shift;
      shift += 5;
    } while (byte >= 0x20);
    lng_change = result & 1 ? ~(result >> 1) : result >> 1;
    lat += lat_change;
    lng += lng_change;
    coordinates.push([lat / factor, lng / factor]);
  }
  return coordinates;
}

// ── Road route fetcher ────────────────────────────────────────────────────────
function RoadRouting({ start, end, setRoute }) {
  useEffect(() => {
    if (!start || !end) return;
    const url = `https://router.project-osrm.org/route/v1/driving/${start[1]},${start[0]};${end[1]},${end[0]}?overview=full`;
    fetch(url)
      .then((res) => res.json())
      .then((data) => {
        if (data.routes && data.routes.length > 0) {
          setRoute(decodePolyline(data.routes[0].geometry));
        } else {
          setRoute([
            [start[0], start[1]],
            [end[0], end[1]],
          ]);
        }
      })
      .catch(() =>
        setRoute([
          [start[0], start[1]],
          [end[0], end[1]],
        ]),
      );
  }, [start, end, setRoute]);
  return null;
}

// ── Fly map to new position ───────────────────────────────────────────────────
function MapUpdater({ position }) {
  const map = useMap();
  useEffect(() => {
    map.flyTo([position.lat, position.lng], 15, { duration: 0.8 });
  }, [position, map]);
  return null;
}

// ── Click to drop pin ─────────────────────────────────────────────────────────
function MapClickHandler({ setPosition }) {
  useMapEvents({
    click(e) {
      setPosition({ lat: e.latlng.lat, lng: e.latlng.lng });
    },
  });
  return null;
}

// ── Main component ────────────────────────────────────────────────────────────
export default function MapPickerModal({
  onClose,
  onConfirm,
  initialLat,
  initialLng,
}) {
  const { t, isKhmer } = useLang();
  const { dark } = useTheme();
  const mapFont = isKhmer
    ? "Kdam Thmor Pro, sans-serif"
    : "Rajdhani, sans-serif";

  const [position, setPosition] = useState({
    lat: initialLat || STORE_LAT,
    lng: initialLng || STORE_LNG,
  });
  const [address, setAddress] = useState("");
  const [loading, setLoading] = useState(false);
  const [route, setRoute] = useState([]);

  // ── Search state ────────────────────────────────────────────────────────────
  const [searchQuery, setSearchQuery] = useState("");
  const [searchResults, setSearchResults] = useState([]);
  const [searchLoading, setSearchLoading] = useState(false);
  const [showDropdown, setShowDropdown] = useState(false);
  const searchRef = useRef(null);
  const debounceRef = useRef(null);

  // ── Colors ──────────────────────────────────────────────────────────────────
  const colors = {
    modalBg: dark ? "#1f2937" : "#ffffff",
    headerBorder: dark ? "#374151" : "#f3f4f6",
    titleText: dark ? "#f9fafb" : "#111111",
    subText: dark ? "#9ca3af" : "#9ca3af",
    closeBtn: dark ? "#6b7280" : "#9ca3af",
    inputBg: dark ? "#111827" : "#f9fafb",
    inputBorder: dark ? "#374151" : "#e5e7eb",
    inputText: dark ? "#f9fafb" : "#111827",
    dropdownBg: dark ? "#1f2937" : "#ffffff",
    dropdownHover: dark ? "#374151" : "#f3f4f6",
    dropdownBorder: dark ? "#374151" : "#e5e7eb",
  };

  // ── Reverse geocode when pin moves ──────────────────────────────────────────
  const fetchAddress = async (lat, lng) => {
    setLoading(true);
    try {
      const res = await fetch(
        `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`,
      );
      const data = await res.json();
      setAddress(data.display_name || t("map.addressNotFound"));
    } catch {
      setAddress(t("map.error"));
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchAddress(position.lat, position.lng);
  }, [position]); // eslint-disable-line

  // ── Search with debounce ─────────────────────────────────────────────────────
  const handleSearchInput = (e) => {
    const q = e.target.value;
    setSearchQuery(q);
    setShowDropdown(true);

    if (debounceRef.current) clearTimeout(debounceRef.current);

    if (!q.trim() || q.trim().length < 2) {
      setSearchResults([]);
      setShowDropdown(false);
      return;
    }

    debounceRef.current = setTimeout(() => {
      doSearch(q.trim());
    }, 400);
  };

  const doSearch = async (q) => {
    setSearchLoading(true);
    try {
      // Bias results toward Cambodia (Phnom Penh viewbox)
      const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(q)}&limit=6&countrycodes=kh&viewbox=102.3,10.4,107.7,14.7&bounded=0`;
      const res = await fetch(url, {
        headers: { "Accept-Language": isKhmer ? "km,en" : "en,km" },
      });
      const data = await res.json();
      setSearchResults(data);
      setShowDropdown(true);
    } catch {
      setSearchResults([]);
    } finally {
      setSearchLoading(false);
    }
  };

  // ── Pick a search result ─────────────────────────────────────────────────────
  const handleSelectResult = (result) => {
    const lat = parseFloat(result.lat);
    const lng = parseFloat(result.lon);
    setPosition({ lat, lng });
    setAddress(result.display_name);
    setSearchQuery(result.display_name);
    setShowDropdown(false);
    setSearchResults([]);
  };

  // ── Close dropdown on outside click ─────────────────────────────────────────
  useEffect(() => {
    const handleClick = (e) => {
      if (searchRef.current && !searchRef.current.contains(e.target)) {
        setShowDropdown(false);
      }
    };
    document.addEventListener("mousedown", handleClick);
    return () => document.removeEventListener("mousedown", handleClick);
  }, []);

  // ── Clear search ─────────────────────────────────────────────────────────────
  const clearSearch = () => {
    setSearchQuery("");
    setSearchResults([]);
    setShowDropdown(false);
  };

  return (
    <div
      style={{
        position: "fixed",
        inset: 0,
        zIndex: 9999,
        background: "rgba(0,0,0,0.8)",
        backdropFilter: "blur(4px)",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        padding: 16,
      }}
    >
      <style>{`
        .leaflet-container { height: 300px !important; width: 100% !important; z-index: 10000; }
        .marker-tag { background: #F97316; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; white-space: nowrap; }
        .loc-search-input:focus { outline: none; border-color: #F97316 !important; box-shadow: 0 0 0 3px rgba(249,115,22,0.15); }
        .loc-result-item:hover { background: ${colors.dropdownHover} !important; }
      `}</style>

      <div
        style={{
          background: colors.modalBg,
          width: "100%",
          maxWidth: 480,
          borderRadius: 20,
          overflow: "hidden",
          boxShadow: "0 32px 80px rgba(0,0,0,0.7)",
          display: "flex",
          flexDirection: "column",
          maxHeight: "92vh",
          fontFamily: mapFont,
        }}
      >
        {/* ── Header ─────────────────────────────────────────────────────────── */}
        <div
          style={{
            padding: "16px 20px",
            borderBottom: `1px solid ${colors.headerBorder}`,
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
          }}
        >
          <div
            style={{
              fontSize: 20,
              fontWeight: 800,
              letterSpacing: 1,
              color: colors.titleText,
            }}
          >
            {t("map.title")}
          </div>
          <button
            onClick={onClose}
            style={{
              background: "none",
              border: "none",
              fontSize: 22,
              cursor: "pointer",
              color: colors.closeBtn,
            }}
          >
            ✕
          </button>
        </div>

        {/* ── Search bar ─────────────────────────────────────────────────────── */}
        <div
          ref={searchRef}
          style={{
            padding: "12px 16px",
            borderBottom: `1px solid ${colors.headerBorder}`,
            position: "relative",
          }}
        >
          {/* Input row */}
          <div
            style={{
              position: "relative",
              display: "flex",
              alignItems: "center",
            }}
          >
            {/* Search icon */}
            <span
              style={{
                position: "absolute",
                left: 11,
                top: "50%",
                transform: "translateY(-50%)",
                fontSize: 15,
                color: colors.subText,
                pointerEvents: "none",
              }}
            >
              🔍
            </span>

            <input
              className="loc-search-input"
              type="text"
              value={searchQuery}
              onChange={handleSearchInput}
              placeholder={isKhmer ? "ស្វែងរកទីតាំង..." : "Search location..."}
              style={{
                width: "100%",
                padding: "10px 36px 10px 36px",
                borderRadius: 10,
                border: `1.5px solid ${colors.inputBorder}`,
                background: colors.inputBg,
                color: colors.inputText,
                fontSize: 14,
                fontFamily: mapFont,
                transition: "border-color 0.2s, box-shadow 0.2s",
              }}
            />

            {/* Clear button or spinner */}
            {searchLoading ? (
              <span
                style={{
                  position: "absolute",
                  right: 10,
                  top: "50%",
                  transform: "translateY(-50%)",
                  width: 16,
                  height: 16,
                  borderRadius: "50%",
                  border: "2px solid #F97316",
                  borderTopColor: "transparent",
                  animation: "spin 0.7s linear infinite",
                  display: "inline-block",
                }}
              />
            ) : (
              searchQuery && (
                <button
                  onClick={clearSearch}
                  style={{
                    position: "absolute",
                    right: 8,
                    top: "50%",
                    transform: "translateY(-50%)",
                    background: "none",
                    border: "none",
                    cursor: "pointer",
                    color: colors.subText,
                    fontSize: 16,
                    lineHeight: 1,
                    padding: "2px 4px",
                  }}
                >
                  ✕
                </button>
              )
            )}
          </div>

          {/* ── Dropdown results ──────────────────────────────────────────────── */}
          {showDropdown && (searchResults.length > 0 || searchLoading) && (
            <div
              style={{
                position: "absolute",
                top: "calc(100% - 4px)",
                left: 16,
                right: 16,
                background: colors.dropdownBg,
                border: `1px solid ${colors.dropdownBorder}`,
                borderRadius: 12,
                zIndex: 99999,
                boxShadow: "0 8px 32px rgba(0,0,0,0.18)",
                overflow: "hidden",
                maxHeight: 240,
                overflowY: "auto",
              }}
            >
              {searchLoading && searchResults.length === 0 ? (
                <div
                  style={{
                    padding: "14px 16px",
                    color: colors.subText,
                    fontSize: 13,
                    textAlign: "center",
                  }}
                >
                  {isKhmer ? "កំពុងស្វែងរក..." : "Searching..."}
                </div>
              ) : searchResults.length === 0 ? (
                <div
                  style={{
                    padding: "14px 16px",
                    color: colors.subText,
                    fontSize: 13,
                    textAlign: "center",
                  }}
                >
                  {isKhmer ? "រកមិនឃើញ" : "No results found"}
                </div>
              ) : (
                searchResults.map((result) => (
                  <button
                    key={result.place_id}
                    className="loc-result-item"
                    onClick={() => handleSelectResult(result)}
                    style={{
                      display: "flex",
                      alignItems: "flex-start",
                      gap: 10,
                      width: "100%",
                      padding: "11px 14px",
                      background: "none",
                      border: "none",
                      borderBottom: `1px solid ${colors.dropdownBorder}`,
                      cursor: "pointer",
                      textAlign: "left",
                      transition: "background 0.15s",
                    }}
                  >
                    {/* Place type icon */}
                    <span style={{ fontSize: 16, flexShrink: 0, marginTop: 1 }}>
                      {result.type === "restaurant"
                        ? "🍽️"
                        : result.class === "highway"
                          ? "🛣️"
                          : result.class === "place"
                            ? "🏙️"
                            : result.class === "amenity"
                              ? "🏢"
                              : "📍"}
                    </span>
                    <div style={{ flex: 1, minWidth: 0 }}>
                      {/* Short name (first segment before comma) */}
                      <div
                        style={{
                          fontSize: 13,
                          fontWeight: 700,
                          color: colors.titleText,
                          overflow: "hidden",
                          textOverflow: "ellipsis",
                          whiteSpace: "nowrap",
                        }}
                      >
                        {result.display_name.split(",")[0]}
                      </div>
                      {/* Full address smaller */}
                      <div
                        style={{
                          fontSize: 11,
                          color: colors.subText,
                          marginTop: 2,
                          overflow: "hidden",
                          textOverflow: "ellipsis",
                          whiteSpace: "nowrap",
                        }}
                      >
                        {result.display_name
                          .split(",")
                          .slice(1)
                          .join(",")
                          .trim()}
                      </div>
                    </div>
                  </button>
                ))
              )}
            </div>
          )}

          {/* Hint text */}
          <p
            style={{
              fontSize: 11,
              color: colors.subText,
              marginTop: 7,
              marginBottom: 0,
            }}
          >
            {isKhmer
              ? "ឬចុចលើផែនទី ឬអូសពិន្ទុដើម្បីជ្រើសរើស"
              : "Or click on the map / drag the pin to select"}
          </p>
        </div>

        {/* ── Map ────────────────────────────────────────────────────────────── */}
        <div style={{ flex: 1, minHeight: 260, position: "relative" }}>
          <MapContainer
            center={[position.lat, position.lng]}
            zoom={14}
            style={{ height: "260px", width: "100%" }}
          >
            <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
            <MapUpdater position={position} />

            {/* Store marker */}
            <Marker position={[STORE_LAT, STORE_LNG]}>
              <Popup>
                <div className="marker-tag">{t("map.storeLabel")}</div>
              </Popup>
            </Marker>

            {/* User pin — draggable */}
            <Marker
              position={[position.lat, position.lng]}
              draggable={true}
              eventHandlers={{
                dragend: (e) => {
                  const ll = e.target.getLatLng();
                  setPosition({ lat: ll.lat, lng: ll.lng });
                  setSearchQuery(""); // clear search text when dragging
                },
              }}
            >
              <Popup>
                <div className="marker-tag" style={{ background: "#3b82f6" }}>
                  {t("map.userLabel")}
                </div>
                {loading ? t("map.loading") : address}
              </Popup>
            </Marker>

            <MapClickHandler
              setPosition={(pos) => {
                setPosition(pos);
                setSearchQuery("");
              }}
            />
            <Polyline
              positions={route}
              color="#F97316"
              weight={4}
              dashArray="10, 10"
            />
            <RoadRouting
              start={[STORE_LAT, STORE_LNG]}
              end={[position.lat, position.lng]}
              setRoute={setRoute}
            />
          </MapContainer>
        </div>

        {/* ── Detected address ───────────────────────────────────────────────── */}
        <div
          style={{
            padding: "13px 20px",
            fontSize: 13,
            color: colors.titleText,
            borderTop: `1px solid ${colors.headerBorder}`,
          }}
        >
          <div
            style={{
              fontWeight: 700,
              marginBottom: 4,
              fontSize: 12,
              letterSpacing: 0.5,
              color: colors.subText,
              textTransform: "uppercase",
            }}
          >
            {t("map.address")}
          </div>
          <div style={{ lineHeight: 1.5 }}>
            {loading ? (
              <span style={{ color: colors.subText }}>{t("map.loading")}</span>
            ) : (
              <span>{address}</span>
            )}
          </div>
        </div>

        {/* ── Confirm button ─────────────────────────────────────────────────── */}
        <div
          style={{
            padding: "12px 20px 16px",
            borderTop: `1px solid ${colors.headerBorder}`,
          }}
        >
          <button
            onClick={() => {
              onConfirm({ lat: position.lat, lng: position.lng, address });
              onClose();
            }}
            disabled={loading}
            style={{
              width: "100%",
              padding: 14,
              borderRadius: 12,
              border: "none",
              background: loading ? "#d1d5db" : "#F97316",
              color: "#fff",
              fontWeight: 800,
              fontSize: 15,
              cursor: loading ? "not-allowed" : "pointer",
              fontFamily: mapFont,
              letterSpacing: 0.5,
              transition: "background 0.2s",
            }}
          >
            ✓ {t("map.confirm")}
          </button>
        </div>
      </div>

      <style>{`
        @keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }
      `}</style>
    </div>
  );
}
