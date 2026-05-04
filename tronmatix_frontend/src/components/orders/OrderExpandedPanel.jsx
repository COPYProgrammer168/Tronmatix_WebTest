// src/components/orders/OrderExpandedPanel.jsx
import { useTheme } from "../../context/ThemeContext";
import { useLang } from "../../context/LanguageContext";
import DeliveryTracker from "./DeliveryTracker";
import OrderMapView from "./OrderMapView";

const LARAVEL_URL = import.meta.env.VITE_API_URL || "http://localhost:8000";

function resolveImage(path) {
  if (!path) return null;
  if (path.startsWith("http://") || path.startsWith("https://")) return path;
  return LARAVEL_URL + (path.startsWith("/") ? path : "/" + path);
}

// ✅ Fix 9: lat/lng are stored inside order.shipping JSON, not as top-level fields.
// Also falls back to top-level delivery_lat/lng (from older API responses).
function resolveMapCoords(order) {
  const lat =
    order.shipping?.lat        ??
    order.delivery_lat         ??
    order.location?.lat        ??
    null;
  const lng =
    order.shipping?.lng        ??
    order.delivery_lng         ??
    order.location?.lng        ??
    null;
  const address =
    order.shipping?.map_address    ??
    order.delivery_map_address     ??
    order.location?.map_address    ??
    null;
  return { lat: lat ? parseFloat(lat) : null, lng: lng ? parseFloat(lng) : null, address };
}

export default function OrderExpandedPanel({ order, onShowQR, onPrint }) {
  const { dark } = useTheme();
  const { t, isKhmer } = useLang();
  const border   = dark ? "#374151" : "#e5e7eb";
  const textMain = dark ? "#f9fafb" : "#111827";
  const textSub  = dark ? "#9ca3af" : "#6b7280";
  const panelBg  = dark ? "#111827" : "#f8fafc";

  // ✅ Derive once — used in multiple places below
  const isPickup     = (order.fulfillment_type ?? "delivery") === "pickup";
  const shippingData = order.shipping || order.location || {};
  const mapCoords    = resolveMapCoords(order);
  const hasMapPin    = !isPickup && mapCoords.lat && mapCoords.lng;

  // ✅ Fix 7: store map for pickup — always show Tronmatix store location
  const STORE_LAT     = 11.56298
  const STORE_LNG     = 104.899518
  const STORE_MAPS_URL = "https://goo.gl/maps/8q7eeNwZH5uz1YwZ8"


  return (
    <div className="p-4" style={{ borderTop: `1px solid ${border}` }}>

      {/* ✅ Fix 5: section title changes for pickup */}
      <div className="mb-5">
        <h4 className="font-black mb-2" style={{ fontSize: 13, letterSpacing: 1, color: textSub }}>
          {isPickup
            ? (isKhmer ? "ស្ថានភាពការបញ្ជាទិញ" : "ORDER STATUS")
            : (isKhmer ? t("orders.deliveryStatus") : "DELIVERY STATUS")}
        </h4>
        {/* ✅ Fix 8: pass fulfillmentType down to DeliveryTracker */}
        <DeliveryTracker
          status={order.status || "confirmed"}
          order={order}
          fulfillmentType={order.fulfillment_type}
        />
      </div>

      <div className="grid md:grid-cols-2 gap-4">

        {/* ✅ Fix 5 + 6: section shows correct content for pickup vs delivery */}
        <div className="rounded-xl p-4" style={{ background: panelBg }}>
          <h4 className="font-black mb-3" style={{ fontSize: 13, letterSpacing: 1, color: textSub }}>
            {isPickup
              ? (isKhmer ? "ព័ត៌មានអ្នកមកយក" : "PICKUP CONTACT")
              : (isKhmer ? t("orders.deliveryTo") : "DELIVERY TO")}
          </h4>
          <div className="space-y-1" style={{ fontSize: 14 }}>
            {/* Name + Phone — always shown */}
            {[
              [isKhmer ? t("locations.name")  : "Name",  shippingData.name],
              [isKhmer ? t("locations.phone") : "Phone", shippingData.phone],
            ].map(([k, v]) => (
              <div key={k}>
                <span style={{ color: textSub }}>{k}: </span>
                <span className="font-bold" style={{ color: textMain }}>{v || "—"}</span>
              </div>
            ))}

            {/* ✅ Fix 6: address row — for pickup show store address, for delivery show customer address */}
            {isPickup ? (
              <div>
                <span style={{ color: textSub }}>
                  {isKhmer ? "ទីតាំងហាង" : "Store"}: </span>
                <span className="font-bold" style={{ color: "#F97316" }}>
                  🏪 Tronmatix Computer · Street 160, Khan Tuol Kouk, Phnom Penh
                </span>
              </div>
            ) : (
              <div>
                <span style={{ color: textSub }}>
                  {isKhmer ? t("locations.address") : "Address"}: </span>
                <span className="font-bold" style={{ color: textMain }}>
                  {`${shippingData.address || ""}${shippingData.city ? ", " + shippingData.city : ""}`|| "—"}
                </span>
              </div>
            )}

            {/* Note */}
            {shippingData.note && (
              <div>
                <span style={{ color: textSub }}>Note: </span>
                <span className="font-bold" style={{ color: textMain }}>{shippingData.note}</span>
              </div>
            )}

            {/* Scheduled date — label changes for pickup */}
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
          </div>
        </div>

        {/* Map section */}
        {isPickup ? (
          /* pickup → show Tronmatix store location as static iframe */
          <div>
            <h4 className="font-black mb-2" style={{ fontSize: 13, letterSpacing: 1, color: textSub }}>
              🏪 {isKhmer ? "ទីតាំងហាង" : "STORE LOCATION"}
            </h4>
            <div style={{ borderRadius: 12, overflow: "hidden", border: `1px solid ${border}` }}>
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
                padding: "8px 12px",
                background: dark ? "rgba(255,255,255,0.03)" : "#f9fafb",
                borderTop: `1px solid ${border}`,
                display: "flex", justifyContent: "space-between", alignItems: "center",
                flexWrap: "wrap", gap: 8,
              }}>
                <span style={{ fontSize: 12, color: textSub }}>
                  📍 "Near Sovannphumi School, Stop Tep Phan, 14 St 160, Phnom Penh, Cambodia"
                </span>
                <a
                  href={STORE_MAPS_URL}
                  target="_blank"
                  rel="noopener noreferrer"
                  style={{
                    fontSize: 12, fontWeight: 700, color: "#F97316",
                    textDecoration: "none", display: "inline-flex", alignItems: "center", gap: 4,
                  }}
                >
                  🗺️ {isKhmer ? "បើក Google Maps" : "Open in Google Maps"} →
                </a>
              </div>
            </div>
          </div>
        ) : hasMapPin ? (
          /* delivery → show customer pin using coords from shipping snapshot */
          <div>
            <h4 className="font-black mb-2" style={{ fontSize: 13, letterSpacing: 1, color: textSub }}>
              📍 {isKhmer ? t("orders.deliveryLocation") : "DELIVERY LOCATION"}
            </h4>
            <OrderMapView
              lat={mapCoords.lat}
              lng={mapCoords.lng}
              address={mapCoords.address}
            />
          </div>
        ) : null}

        {/* Items + totals */}
        <div
          className={`rounded-xl p-4 ${(!isPickup && !hasMapPin) ? "md:col-span-1" : "col-span-full md:col-span-1"}`}
          style={{ background: panelBg }}
        >
          <h4 className="font-black mb-3" style={{ fontSize: 13, letterSpacing: 1, color: textSub }}>
            {isKhmer ? t("orders.items") : "ITEMS"}
          </h4>
          <div className="space-y-2">
            {(order.items || order.order_items || []).map((item, i) => {
              const imgUrl = resolveImage(item.image || item.product?.image);
              return (
                <div key={i} className="flex items-center gap-3" style={{ fontSize: 14 }}>
                  <div
                    className="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 flex items-center justify-center"
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
                    <span style={{ display: imgUrl ? "none" : "flex", fontSize: 18 }}>📦</span>
                  </div>
                  <span className="flex-1" style={{ color: dark ? "#d1d5db" : "#374151" }}>
                    {item.name || item.product?.name}{" "}
                    <span style={{ color: textSub }}>×{item.qty}</span>
                  </span>
                  <span className="font-bold" style={{ color: textMain }}>
                    ${((item.price || item.unit_price) * item.qty).toFixed(2)}
                  </span>
                </div>
              );
            })}
          </div>

          {/* Totals */}
          <div className="mt-3 pt-2 space-y-1" style={{ borderTop: `1px solid ${border}` }}>
            {order.subtotal && order.subtotal !== order.total && (
              <div className="flex justify-between" style={{ fontSize: 14, color: textSub }}>
                <span>Subtotal</span>
                <span>${Number(order.subtotal).toFixed(2)}</span>
              </div>
            )}
            {order.discount_amount > 0 && (
              <div className="flex justify-between text-green-500 font-bold" style={{ fontSize: 14 }}>
                <span>🏷 {order.discount_code || "Discount"}</span>
                <span>−${Number(order.discount_amount).toFixed(2)}</span>
              </div>
            )}
            <div className="flex justify-between font-black pt-1" style={{ fontSize: 16 }}>
              <span style={{ color: textMain }}>Total</span>
              <span className="text-primary">${Number(order.total).toFixed(2)}</span>
            </div>
          </div>
        </div>

      </div>

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