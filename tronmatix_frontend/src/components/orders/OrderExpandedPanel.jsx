// src/components/orders/OrderExpandedPanel.jsx
import { useTheme } from "../../context/ThemeContext"
import DeliveryTracker from "./DeliveryTracker"
import { resolveImage } from "../../lib/resolveImage"

// FIX: Removed local LARAVEL_URL + resolveImage — now using shared lib/resolveImage.js
// which correctly handles S3/R2 full URLs and local /storage/ paths

export default function OrderExpandedPanel({ order, onShowQR, onPrint }) {
  const { dark } = useTheme()
  const border   = dark ? "#374151" : "#e5e7eb"
  const textMain = dark ? "#f9fafb" : "#111827"
  const textSub  = dark ? "#9ca3af" : "#6b7280"
  const panelBg  = dark ? "#111827" : "#f8fafc"

  return (
    <div className="p-4" style={{ borderTop: `1px solid ${border}` }}>

      {/* Delivery tracker */}
      <div className="mb-5">
        <h4 className="font-black mb-2" style={{ fontSize: 13, letterSpacing: 1, color: textSub }}>DELIVERY STATUS</h4>
        <DeliveryTracker status={order.status || "confirmed"} order={order} />
      </div>

      <div className="grid md:grid-cols-2 gap-4">

        {/* Delivery info */}
        <div className="rounded-xl p-4" style={{ background: panelBg }}>
          <h4 className="font-black mb-3" style={{ fontSize: 13, letterSpacing: 1, color: textSub }}>DELIVERY TO</h4>
          <div className="space-y-1" style={{ fontSize: 14 }}>
            {[
              ["Name",    (order.shipping || order.location)?.name],
              ["Phone",   (order.shipping || order.location)?.phone],
              ["Address", `${(order.shipping || order.location)?.address || ""}${(order.shipping || order.location)?.city ? ", " + (order.shipping || order.location).city : ""}`],
            ].map(([k, v]) => (
              <div key={k}>
                <span style={{ color: textSub }}>{k}: </span>
                <span className="font-bold" style={{ color: textMain }}>{v || "—"}</span>
              </div>
            ))}
            {(order.shipping || order.location)?.note && (
              <div>
                <span style={{ color: textSub }}>Note: </span>
                <span className="font-bold" style={{ color: textMain }}>{(order.shipping || order.location).note}</span>
              </div>
            )}
            {order.delivery_date && (
              <div>
                <span style={{ color: textSub }}>Date: </span>
                <span className="font-bold text-primary">
                  {new Date(order.delivery_date).toLocaleDateString("en-GB", { weekday: "short", day: "2-digit", month: "short", year: "numeric" })}
                </span>
              </div>
            )}
            {order.delivery_time_slot && (
              <div>
                <span style={{ color: textSub }}>Slot: </span>
                <span className="font-bold text-primary">🕐 {order.delivery_time_slot}</span>
              </div>
            )}
          </div>
        </div>

        {/* Items + totals */}
        <div className="rounded-xl p-4" style={{ background: panelBg }}>
          <h4 className="font-black mb-3" style={{ fontSize: 13, letterSpacing: 1, color: textSub }}>ITEMS</h4>
          <div className="space-y-2">
            {(order.items || order.order_items || []).map((item, i) => {
              // FIX: use shared resolveImage — handles S3/R2 and local paths
              const imgUrl = resolveImage(item.image || item.product?.image)
              return (
                <div key={i} className="flex items-center gap-3" style={{ fontSize: 14 }}>
                  <div className="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 flex items-center justify-center"
                    style={{ background: dark ? "#1f2937" : "#f3f4f6", border: `1px solid ${border}` }}>
                    {imgUrl
                      ? <img src={imgUrl} alt={item.name} className="w-full h-full object-contain"
                          onError={(e) => { e.target.style.display = "none" }} />
                      : <span style={{ fontSize: 18 }}>📦</span>}
                  </div>
                  <span className="flex-1" style={{ color: dark ? "#d1d5db" : "#374151" }}>
                    {item.name || item.product?.name}{" "}
                    <span style={{ color: textSub }}>×{item.qty}</span>
                  </span>
                  <span className="font-bold" style={{ color: textMain }}>
                    ${((item.price || item.unit_price) * item.qty).toFixed(2)}
                  </span>
                </div>
              )
            })}
          </div>

          {/* Totals */}
          <div className="mt-3 pt-2 space-y-1" style={{ borderTop: `1px solid ${border}` }}>
            {order.subtotal && order.subtotal !== order.total && (
              <div className="flex justify-between" style={{ fontSize: 16, color: textSub }}>
                <span>Subtotal</span><span>${Number(order.subtotal).toFixed(2)}</span>
              </div>
            )}
            {order.discount_amount > 0 && (
              <div className="flex justify-between text-green-500 font-bold" style={{ fontSize: 16 }}>
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

      {/* Bottom actions */}
      <div className="mt-4 pt-4 flex items-center justify-end gap-3 flex-wrap" style={{ borderTop: `1px solid ${border}` }}>
        {order.payment_method === "bakong" && order.payment_status !== "paid" && (
          <button onClick={(e) => { e.stopPropagation(); onShowQR(order) }}
            className="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold border-2 border-blue-300 text-blue-500 hover:bg-blue-500 hover:text-white transition-all"
            style={{ fontSize: 15 }}>
            📱 Show QR / Pay Now
          </button>
        )}
        <button onClick={(e) => { e.stopPropagation(); onPrint(order) }}
          className="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold border-2 transition-all"
          style={{ fontSize: 15, borderColor: border, color: textSub }}>
          🖨 View Full Receipt / Print
        </button>
      </div>
    </div>
  )
}
