// src/components/orders/OrderExpandedPanel.jsx
import { useTheme } from "../../context/ThemeContext";
import { useLang } from "../../context/LanguageContext";
import DeliveryTracker from "./DeliveryTracker";
import OrderMapView from "./OrderMapView";

const LARAVEL_URL = import.meta.env.VITE_API_URL || "";

function resolveImage(path) {
  if (!path) return null;
  if (path.startsWith("http://") || path.startsWith("https://")) return path;
  return LARAVEL_URL + (path.startsWith("/") ? path : "/" + path);
}

// lat/lng are stored inside order.shipping JSON, not as top-level fields.
// Also falls back to top-level delivery_lat/lng (from older API responses).
function resolveMapCoords(order) {
  const lat =
    order.shipping?.lat ??
    order.delivery_lat ??
    order.location?.lat ??
    null;
  const lng =
    order.shipping?.lng ??
    order.delivery_lng ??
    order.location?.lng ??
    null;
  const address =
    order.shipping?.map_address ??
    order.delivery_map_address ??
    order.location?.map_address ??
    null;
  return { lat: lat ? parseFloat(lat) : null, lng: lng ? parseFloat(lng) : null, address };
}

export default function OrderExpandedPanel({ order, onShowQR, onPrint }) {
  const { dark } = useTheme();
  const { t, isKhmer } = useLang();
  const border = dark ? "#374151" : "#e5e7eb";
  const textMain = dark ? "#f9fafb" : "#111827";
  const textSub = dark ? "#9ca3af" : "#6b7280";
  const panelBg = dark ? "#111827" : "#f8fafc";

  // Derive once — used in multiple places below
  const isPickup = (order.fulfillment_type ?? "delivery") === "pickup";
  const shippingData = order.shipping || order.location || {};
  const mapCoords = resolveMapCoords(order);
  const hasMapPin = !isPickup && mapCoords.lat && mapCoords.lng;

  // store map for pickup — always show Tronmatix store location
  const STORE_LAT = 11.56298
  const STORE_LNG = 104.899518
  const STORE_MAPS_URL = "https://goo.gl/maps/8q7eeNwZH5uz1YwZ8"


  return (
    <div className="p-4" style={{ borderTop: `1px solid ${border}` }}>

      {/* section title changes for pickup */}
      <div className="mb-5">
        <h4 className="font-black mb-2" style={{ fontSize: isKhmer ? 16 : 20, letterSpacing: 1, color: textSub }}>
          {isPickup
            ? (isKhmer ? "ស្ថានភាពការបញ្ជាទិញ" : "ORDER STATUS")
            : (isKhmer ? t("orders.deliveryStatus") : "DELIVERY STATUS")}
        </h4>
        {/*pass fulfillmentType down to DeliveryTracker */}
        <DeliveryTracker
          status={order.status || "confirmed"}
          order={order}
          fulfillmentType={order.fulfillment_type}
        />
      </div>

      {/* Row 1 — Delivery / Pickup info (full width, bigger text) */}
      <div className="rounded-xl p-5 mb-4" style={{ background: panelBg }}>
        <h4 className="font-black mb-3" style={{ fontSize: isKhmer ? 16 : 20, letterSpacing: 1, color: textSub }}>
          {isPickup
            ? (isKhmer ? "ព័ត៌មានអ្នកមកយក" : "PICKUP CONTACT")
            : (isKhmer ? t("orders.deliveryTo") : "DELIVERY TO")}
        </h4>
        <div className="grid grid-cols-1 sm:grid-cols-2 font-semibold gap-x-8 gap-y-2" style={{ fontSize: isKhmer ? 16 : 20 }}>
          {/* Name + Phone — always shown */}
          {[
            [isKhmer ? t("locations.name") : "Name", shippingData.name],
            [isKhmer ? t("locations.phone") : "Phone", shippingData.phone],
          ].map(([k, v]) => (
            <div key={k}>
              <span style={{ color: textSub }}>{k}: </span>
              <span className="font-bold" style={{ color: textMain }}>{v || "—"}</span>
            </div>
          ))}

          {/* Address row — pickup: store | delivery: customer */}
          {isPickup ? (
            <div className="sm:col-span-2">
              <span style={{ color: textSub }}>
                {isKhmer ? "ទីតាំងហាង" : "Store"}: </span>
              <span className="font-bold" style={{ color: "#F97316" }}>
                🏪 Tronmatix Computer · Street 160, Khan Tuol Kouk, Phnom Penh
              </span>
            </div>
          ) : (
            <div className="sm:col-span-2">
              <span style={{ color: textSub }}>
                {isKhmer ? t("locations.address") : "Address"}: </span>
              <span className="font-bold" style={{ color: textMain }}>
                {`${shippingData.address || ""}${shippingData.city ? ", " + shippingData.city : ""}` || "—"}
              </span>
            </div>
          )}

          {/* Note */}
          {shippingData.note && (
            <div className="sm:col-span-2">
              <span style={{ color: textSub }}>Note: </span>
              <span className="font-bold" style={{ color: textMain }}>{shippingData.note}</span>
            </div>
          )}

          {/* Scheduled date */}
          {order.delivery_date && (
            <div>
              <span style={{ color: textSub }}>
                {isPickup
                  ? (isKhmer ? "ថ្ងៃចង់មកយក" : "Pickup Date")
                  : (isKhmer ? "ថ្ងៃដឹក" : "Date")}: </span>
              <span className="font-bold text-primary">
                {new Date(order.delivery_date).toLocaleDateString("en-GB", {
                  weekday: "short", day: "2-digit", month: "short", year: "numeric",
                })}
              </span>
            </div>
          )}
          {order.delivery_time_slot && (
            <div>
              <span style={{ color: textSub }}>
                {isPickup ? (isKhmer ? "ម៉ោង" : "Time") : (isKhmer ? "ម៉ោង" : "Slot")}: </span>
              <span className="font-bold text-primary">🕐 {order.delivery_time_slot}</span>
            </div>
          )}

          {/* Delivery provider — delivery only */}
          {!isPickup && order.delivery_provider_details && (
            <div className="sm:col-span-2" style={{ marginTop: 4, paddingTop: 10, borderTop: `1px dashed ${border}` }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 10, flexWrap: 'wrap' }}>
                {order.delivery_provider_details.logo_url && (
                  <img
                    src={resolveImage(order.delivery_provider_details.logo_url)}
                    alt={order.delivery_provider_details.name}
                    style={{ height: 28, width: 'auto', objectFit: 'contain', borderRadius: 6, border: `1px solid ${border}`, background: dark ? '#1f2937' : '#fff' }}
                    onError={e => { e.target.style.display = 'none' }}
                  />
                )}
                <span className="font-bold" style={{ color: textMain, fontSize: isKhmer ? 16 : 20, }}>
                  🚚 {order.delivery_provider_details.name}
                </span>
                {order.delivery_provider_details.estimated_time && (
                  <span style={{ color: textSub, fontSize: isKhmer ? 16 : 20, }}>
                    ⏱ {order.delivery_provider_details.estimated_time}
                  </span>
                )}
                {order.delivery_provider_details.fee != null && (
                  <span className="font-bold" style={{ color: '#F97316', fontSize: isKhmer ? 16 : 20, }}>
                    💰 ${Number(order.delivery_provider_details.fee).toFixed(2)}
                  </span>
                )}
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Row 2 — Items + totals (full width, bigger text) */}
      <div className="rounded-xl p-5 mb-4" style={{ background: panelBg }}>
        <h4 className="font-black mb-3" style={{ fontSize: isKhmer ? 16 : 20, letterSpacing: 1, color: textSub }}>
          {isKhmer ? t("orders.items") : "ITEMS"}
        </h4>
        <div className="space-y-3">
          {(order.items || order.order_items || []).map((item, i) => {
            const imgUrl = resolveImage(item.image || item.product?.image);
            return (
              <div key={i} className="flex items-center gap-3" style={{ fontSize: 16 }}>
                <div
                  className="w-16 h-16 rounded-lg overflow-hidden flex-shrink-0 flex items-center justify-center"
                  style={{ background: dark ? "#1f2937" : "#f3f4f6", border: `1px solid ${border}` }}
                >
                  {imgUrl ? (
                    <img
                      src={imgUrl}
                      alt={item.name}
                      className="w-full h-full object-contain"
                      onError={(e) => {
                        e.target.style.display = "none";
                        e.target.nextSibling.style.display = "flex";
                      }}
                    />
                  ) : null}
                  <span style={{ display: imgUrl ? "none" : "flex", fontSize: 20 }}>📦</span>
                </div>
                <span className="flex-1 text-lg font-semibold" style={{ color: dark ? "#d1d5db" : "#374151" }}>
                  {item.brand && (
                    <span style={{ color: '#F97316', fontWeight: 700, fontSize: 12, marginRight: 6, letterSpacing: 0.5 }}>
                      {item.brand}
                    </span>
                  )}
                  {item.name || item.product?.name}{" "}
                  <span style={{ color: textSub }}>×{item.qty}</span>
                  {item.warranty_start && item.warranty_end && (
                    <div className="text-xs font-bold mt-0.5" style={{ color: "#F97316" }}>
                      🛡 Warranty: {new Date(item.warranty_start).toLocaleDateString('en-GB')} - {new Date(item.warranty_end).toLocaleDateString('en-GB')}
                    </div>
                  )}
                </span>
                <span className="font-bold text-lg" style={{ color: textMain }}>
                  ${((item.price || item.unit_price) * item.qty).toFixed(2)}
                </span>
              </div>
            );
          })}
        </div>

        {/* Totals — hide for single-item orders (no breakdown needed) */}
        {(order.items || order.order_items || []).length > 1 && (
          <div className="mt-4 pt-3 space-y-1" style={{ borderTop: `1px solid ${border}` }}>
            {order.subtotal && order.subtotal !== order.total && (
              <div className="flex justify-between font-semibold text-lg" style={{ fontSize: isKhmer ? 18 : 20, color: textSub }}>
                <span>Subtotal</span>
                <span>${Number(order.subtotal).toFixed(2)}</span>
              </div>
            )}
            {order.discount_amount > 0 && (
              <div className="flex justify-between text-green-500 font-bold" style={{ fontSize: isKhmer ? 18 : 20, }}>
                <span>🏷 {order.discount_code || "Discount"}</span>
                <span>−${Number(order.discount_amount).toFixed(2)}</span>
              </div>
            )}
            <div className="flex justify-between font-black pt-1" style={{ fontSize: isKhmer ? 20 : 24, }}>
              <span style={{ color: textMain }}>Total</span>
              <span className="text-primary">${Number(order.total).toFixed(2)}</span>
            </div>
          </div>
        )}
      </div>

      {/* Row 3 — Map / Store location (full width) */}
      {isPickup ? (
        /* pickup → show Tronmatix store location as static iframe */
        <div className="rounded-xl overflow-hidden mb-4" style={{ border: `1px solid ${border}` }}>
          <div className="p-4" style={{ background: panelBg }}>
            <h4 className="font-black mb-2" style={{ fontSize: isKhmer ? 16 : 20, letterSpacing: 1.5, color: textSub }}>
              🏪 {isKhmer ? "ទីតាំងហាង" : "STORE LOCATION"}
            </h4>
          </div>
          <iframe
            title="Tronmatix Store Location"
            width="100%"
            height="200"
            style={{ border: 0, display: "block" }}
            loading="lazy"
            allowFullScreen
            referrerPolicy="no-referrer-when-downgrade"
            src={`https://www.google.com/maps?q=${STORE_LAT},${STORE_LNG}&z=17&output=embed`}
          />
          <div style={{
            padding: "10px 14px",
            background: dark ? "rgba(255,255,255,0.03)" : "#f9fafb",
            borderTop: `1px solid ${border}`,
            display: "flex", justifyContent: "space-between", alignItems: "center",
            flexWrap: "wrap", gap: 8,
          }}>
            <span style={{ fontSize: isKhmer ? 16 : 20, color: textSub }}>
              📍 "Near Sovannphumi School, Stop Tep Phan, 14 St 160, Phnom Penh, Cambodia"
            </span>
            <a
              href={STORE_MAPS_URL}
              target="_blank"
              rel="noopener noreferrer"
              style={{
                fontSize: 13, fontWeight: 700, color: "#F97316",
                textDecoration: "none", display: "inline-flex", alignItems: "center", gap: 4,
              }}
            >
              🗺️ {isKhmer ? "បើក Google Maps" : "Open in Google Maps"} →
            </a>
          </div>
        </div>
      ) : hasMapPin ? (
        /* delivery → show customer pin using coords from shipping snapshot */
        <div className="rounded-xl overflow-hidden mb-4" style={{ border: `1px solid ${border}` }}>
          <div className="p-4" style={{ background: panelBg }}>
            <h4 className="font-black mb-2" style={{ fontSize: isKhmer ? 16 : 20, letterSpacing: 1.5, color: textSub }}>
              📍 {isKhmer ? t("orders.deliveryLocation") : "DELIVERY LOCATION"}
            </h4>
          </div>
          <OrderMapView
            lat={mapCoords.lat}
            lng={mapCoords.lng}
            address={mapCoords.address}
          />
        </div>
      ) : null}

      {/* Bottom action buttons */}
      <div
        className="mt-4 pt-4 flex items-center justify-end gap-3 flex-wrap"
        style={{ borderTop: `1px solid ${border}` }}
      >
        {order.payment_method === "bakong" && order.payment_status !== "paid" && (
          <button
            onClick={(e) => { e.stopPropagation(); onShowQR(order); }}
            className="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold border-2 border-blue-300 text-blue-500 hover:bg-blue-500 hover:text-white transition-all"
            style={{ fontSize: 15 }}
          >
            📱 {isKhmer ? t("orders.showQRPay") : "Show QR / Pay Now"}
          </button>
        )}
        <button
          onClick={(e) => { e.stopPropagation(); onPrint(order); }}
          className="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold border-2 transition-all"
          style={{ fontSize: 15, borderColor: border, color: textSub }}
        >
          🖨 {isKhmer ? t("orders.viewReceipt") : "View Full Receipt / Print"}
        </button>
      </div>
    </div>
  );
}