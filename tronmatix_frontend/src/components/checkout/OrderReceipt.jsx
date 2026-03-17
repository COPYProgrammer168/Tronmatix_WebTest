// src/components/checkout/OrderReceipt.jsx
import { useCallback } from "react"
import { useNavigate } from "react-router-dom"
import logo from "../../assets/logo.png"

const STATUS_STEPS = [
  { key: "confirmed",  label: "Confirmed",  icon: "✅" },
  { key: "processing", label: "Processing", icon: "⚙️" },
  { key: "shipped",    label: "Shipped",    icon: "🚚" },
  { key: "delivered",  label: "Delivered",  icon: "📦" },
]

export default function OrderReceipt({ order, deliveryStatus }) {
  const navigate = useNavigate()

  // Support both local snapshot fields (_discountAmount) and API response fields (discount_amount)
  const snapDiscount = Number(order._discountAmount || order.discount_amount || 0)
  const snapCode     = order._discountCode || order.discount_code || ""
  const snapType     = order._discountType || ""
  const snapValue    = order._discountValue || 0
  const discLabel    = snapCode
    ? (snapType === "percentage" ? `${snapCode} · ${snapValue}% OFF` : `${snapCode} · $${snapValue} OFF`)
    : (snapDiscount > 0 ? `$${snapDiscount.toFixed(2)} OFF` : "")

  const printPDF = useCallback(() => {
    const w = window.open("", "_blank", "width=720,height=960")
    w.document.write(`<!DOCTYPE html><html><head><meta charset="utf-8">
      <title>Receipt #${order.order_id || order.id}</title>
      <style>
        *{box-sizing:border-box}
        body{font-family:Arial,sans-serif;padding:32px;color:#111;max-width:680px;margin:0 auto}
        .brand{font-size:24px;font-weight:900;letter-spacing:3px;color:#F97316}
        .subtitle{color:#666;font-size:12px;margin:2px 0 20px;letter-spacing:2px}
        .info-row{display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid #f0f0f0;font-size:14px}
        .info-row span:first-child{color:#666}
        .info-row span:last-child{font-weight:600}
        table{width:100%;border-collapse:collapse;margin:20px 0}
        thead th{background:#f9fafb;font-size:12px;text-align:left;padding:10px 8px;border-bottom:2px solid #e5e7eb;letter-spacing:1px}
        tbody td{padding:9px 8px;border-bottom:1px solid #f5f5f5;font-size:13px}
        tfoot td{padding:8px;font-weight:700;font-size:14px}
        .discount-row td{color:#16a34a}
        .total-row td{font-size:18px;font-weight:900;color:#F97316;padding-top:12px}
        .footer{text-align:center;color:#9ca3af;font-size:11px;margin-top:28px;border-top:1px solid #eee;padding-top:16px;letter-spacing:1px}
        .badge{display:inline-block;background:#fff7ed;border:1px solid #F97316;color:#F97316;border-radius:20px;padding:2px 10px;font-size:11px;font-weight:700;letter-spacing:1px}
        @media print{body{padding:16px}}
      </style></head><body>
      <div class="brand">TRONMATIX COMPUTER</div>
      <div class="subtitle">ORDER RECEIPT</div>
      <div class="info-row"><span>Order ID</span><span style="color:#F97316;font-family:monospace">#${order.order_id || order.id}</span></div>
      <div class="info-row"><span>Date</span><span>${new Date(order.created_at || Date.now()).toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric", hour: "2-digit", minute: "2-digit" })}</span></div>
      <div class="info-row"><span>Customer</span><span>${order.location?.name || "—"}</span></div>
      <div class="info-row"><span>Phone</span><span>${order.location?.phone || "—"}</span></div>
      <div class="info-row"><span>Address</span><span>${order.location?.address || ""}${order.location?.city ? ", " + order.location.city : ""}</span></div>
      ${order.delivery_date ? `<div class="info-row"><span>Delivery</span><span style="color:#F97316">${order.delivery_date}${order.delivery_time_slot ? " · " + order.delivery_time_slot : ""}</span></div>` : ""}
      <div class="info-row"><span>Payment</span><span>${order.payment_method === "cash" ? "💵 Cash on Delivery" : "📱 ABA BAKONG KHQR"}</span></div>
      ${snapDiscount > 0 ? `<div class="info-row"><span>Discount</span><span class="badge">${snapCode ? snapCode + " — " : ""}${discLabel}</span></div>` : ""}
      <table>
        <thead><tr><th>ITEM</th><th style="text-align:center">QTY</th><th style="text-align:right">UNIT</th><th style="text-align:right">TOTAL</th></tr></thead>
        <tbody>${(order.items || []).map((i) => `
          <tr>
            <td>${i.name}</td>
            <td style="text-align:center">×${i.qty}</td>
            <td style="text-align:right">$${Number(i.price).toFixed(2)}</td>
            <td style="text-align:right">$${(i.price * i.qty).toFixed(2)}</td>
          </tr>`).join("")}
        </tbody>
        <tfoot>
          <tr><td colspan="3" style="text-align:right;color:#666">Subtotal</td><td style="text-align:right">$${Number(order.subtotal || order.total).toFixed(2)}</td></tr>
          ${snapDiscount > 0 ? `<tr class="discount-row"><td colspan="3" style="text-align:right">🏷 Discount (${snapCode})</td><td style="text-align:right">−$${snapDiscount.toFixed(2)}</td></tr>` : ""}
          <tr class="total-row"><td colspan="3" style="text-align:right">TOTAL</td><td style="text-align:right">$${Number(order.total).toFixed(2)}</td></tr>
        </tfoot>
      </table>
      <div class="footer">THANK YOU FOR SHOPPING AT TRONMATIX COMPUTER</div>
    </body></html>`)
    w.document.close()
    setTimeout(() => w.print(), 600)
  }, [order])

  return (
    <div className="max-w-[900px] mx-auto px-4 py-8">
      <div className="border border-gray-200 rounded-2xl bg-white shadow-xl overflow-hidden">
        <div className="h-1.5 bg-gradient-to-r from-orange-300 via-primary to-orange-300" />

        {/* Header */}
        <div className="flex items-center gap-4 p-6 border-b border-gray-100">
          <img src={logo} alt="" className="h-14" />
          <div>
            <h2 className="font-black tracking-widest" style={{ fontFamily: "HurstBagod, Rajdhani, sans-serif", fontSize: 19 }}>
              TRONMATIX COMPUTER
            </h2>
            <span className="inline-block mt-1 bg-orange-50 border border-primary text-primary rounded-full px-3 py-0.5" style={{ fontSize: 11, letterSpacing: 2 }}>
              ORDER RECEIPT
            </span>
          </div>
          <div className="ml-auto text-right">
            <div className="text-gray-400" style={{ fontSize: 12, letterSpacing: 1 }}>ORDER ID</div>
            <div className="font-black text-primary" style={{ fontSize: 18 }}>#{order.order_id || order.id}</div>
            <div className="text-gray-400" style={{ fontSize: 12 }}>
              {new Date(order.created_at || Date.now()).toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" })}
            </div>
          </div>
        </div>

        {/* Customer info */}
        <div className="grid grid-cols-2 border-b border-gray-100">
          {[
            ["Customer", order.location?.name],
            ["Phone",    order.location?.phone],
            ["Address",  `${order.location?.address || ""}${order.location?.city ? ", " + order.location.city : ""}`],
            ["Payment",  order.payment_method === "cash" ? "💵 Cash on Delivery" : "📱 ABA BAKONG KHQR"],
            ...(order.delivery_date ? [["Delivery Date", `${order.delivery_date}${order.delivery_time_slot ? " · " + order.delivery_time_slot : ""}`]] : []),
            ...(order.location?.note ? [["Note", order.location.note]] : []),
          ].map(([k, v]) => (
            <div key={k} className="px-6 py-3 border-b border-gray-50">
              <div className="text-gray-400 font-semibold" style={{ fontSize: 11, letterSpacing: 1 }}>{k.toUpperCase()}</div>
              <div className="font-bold text-gray-800" style={{ fontSize: 14 }}>{v || "—"}</div>
            </div>
          ))}

          {snapDiscount > 0 && (
            <div className="col-span-2 px-6 py-3 bg-green-50 border-b border-green-100">
              <div className="text-green-500 font-semibold" style={{ fontSize: 11, letterSpacing: 1 }}>DISCOUNT APPLIED</div>
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2 mt-0.5">
                  <span className="bg-green-100 border border-green-300 text-green-700 font-black rounded-full px-3 py-0.5" style={{ fontSize: 13, letterSpacing: 1 }}>
                    🏷 {snapCode}
                  </span>
                  <span className="text-green-600 font-bold" style={{ fontSize: 13 }}>{discLabel}</span>
                </div>
                <span className="text-green-700 font-black" style={{ fontSize: 16 }}>−${snapDiscount.toFixed(2)}</span>
              </div>
            </div>
          )}
        </div>

        {/* Items table */}
        <table className="w-full border-b border-gray-100">
          <thead className="bg-gray-50">
            <tr>
              {["ITEM", "QTY", "UNIT", "TOTAL"].map((h) => (
                <th key={h} className={`py-3 text-gray-500 font-semibold ${h === "ITEM" ? "text-left px-6" : h === "TOTAL" ? "text-right px-6" : "text-center px-3"}`} style={{ fontSize: 12 }}>
                  {h}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {(order.items || []).map((item, i) => (
              <tr key={i} className="border-t border-gray-50">
                <td className="px-6 py-3 font-semibold text-gray-800" style={{ fontSize: 14 }}>{item.name}</td>
                <td className="px-3 py-3 text-center text-gray-500" style={{ fontSize: 14 }}>×{item.qty}</td>
                <td className="px-3 py-3 text-right text-gray-600" style={{ fontSize: 14 }}>${Number(item.price).toFixed(2)}</td>
                <td className="px-6 py-3 text-right font-black" style={{ fontSize: 14 }}>${(item.price * item.qty).toFixed(2)}</td>
              </tr>
            ))}
          </tbody>
          <tfoot className="bg-orange-50">
            <tr>
              <td colSpan={3} className="px-6 py-2 text-right text-gray-500" style={{ fontSize: 13 }}>Subtotal</td>
              <td className="px-6 py-2 text-right text-gray-700 font-bold" style={{ fontSize: 13 }}>${Number(order.subtotal || order.total).toFixed(2)}</td>
            </tr>
            {snapDiscount > 0 && (
              <tr>
                <td colSpan={3} className="px-6 py-2 text-right text-green-600 font-bold" style={{ fontSize: 13 }}>🏷 Discount ({snapCode})</td>
                <td className="px-6 py-2 text-right text-green-600 font-bold" style={{ fontSize: 13 }}>−${snapDiscount.toFixed(2)}</td>
              </tr>
            )}
            <tr>
              <td colSpan={3} className="px-6 py-4 text-right font-black tracking-widest text-gray-700" style={{ fontSize: 15 }}>TOTAL</td>
              <td className="px-6 py-4 text-right font-black text-primary" style={{ fontSize: 22 }}>${Number(order.total).toFixed(2)}</td>
            </tr>
          </tfoot>
        </table>

        {/* Delivery tracker */}
        <div className="px-6 py-5 border-b border-gray-100 bg-gray-50">
          <h3 className="font-black text-gray-500 mb-4" style={{ fontSize: 12, letterSpacing: 2 }}>DELIVERY STATUS</h3>
          <div className="flex items-center">
            {STATUS_STEPS.map((s, i) => (
              <div key={s.key} className="flex items-center flex-1">
                <div className="flex flex-col items-center flex-1">
                  <div className={`w-10 h-10 rounded-full flex items-center justify-center text-lg ${i <= deliveryStatus ? "bg-primary text-white shadow-md" : "bg-gray-200 text-gray-400"}`}>
                    {s.icon}
                  </div>
                  <div className={`mt-1 font-bold text-center ${i <= deliveryStatus ? "text-primary" : "text-gray-400"}`} style={{ fontSize: 10 }}>
                    {s.label}
                  </div>
                </div>
                {i < STATUS_STEPS.length - 1 && (
                  <div className={`h-1 flex-1 mx-1 rounded ${i < deliveryStatus ? "bg-primary" : "bg-gray-200"}`} />
                )}
              </div>
            ))}
          </div>
        </div>

        {/* Payment panel */}
        <div className="px-6 py-5 border-b border-gray-100">
          {order.payment_method === "cash" ? (
            <div className="bg-green-50 border border-green-200 rounded-2xl p-5">
              <div className="flex items-center gap-3 mb-4">
                <div className="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center" style={{ fontSize: 26 }}>💵</div>
                <div>
                  <div className="font-black text-green-700" style={{ fontSize: 18 }}>Cash on Delivery</div>
                  <div className="text-green-600" style={{ fontSize: 13 }}>Pay when your order arrives</div>
                </div>
              </div>
              <div className="bg-white border border-green-200 rounded-xl p-4 space-y-2" style={{ fontSize: 14 }}>
                <div className="flex justify-between">
                  <span className="text-gray-500">Prepare amount</span>
                  <span className="font-black text-green-700 text-lg">${Number(order.total).toFixed(2)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-500">Order ID</span>
                  <span className="font-bold font-mono">#{order.order_id || order.id}</span>
                </div>
                {order.delivery_date && (
                  <div className="flex justify-between">
                    <span className="text-gray-500">Date</span>
                    <span className="font-bold text-primary">
                      {order.delivery_date}{order.delivery_time_slot ? " · " + order.delivery_time_slot : ""}
                    </span>
                  </div>
                )}
              </div>
              <div className="mt-3 flex items-center gap-2 text-green-600" style={{ fontSize: 12 }}>
                <span>✅</span>
                <span className="font-semibold">Our team will contact you before delivery</span>
              </div>
            </div>
          ) : (
            <div className="bg-blue-50 border border-blue-200 rounded-2xl p-5 flex items-center gap-4">
              <div className="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center" style={{ fontSize: 26 }}>📱</div>
              <div>
                <div className="font-black text-blue-700" style={{ fontSize: 17 }}>ABA BAKONG KHQR</div>
                {deliveryStatus >= 1 ? (
                  <div className="text-green-600 font-bold" style={{ fontSize: 13 }}>✅ Payment confirmed</div>
                ) : (
                  <div className="text-blue-600" style={{ fontSize: 13 }}>Awaiting payment confirmation…</div>
                )}
                <div className="text-blue-400 font-mono" style={{ fontSize: 12 }}>Ref: {order.payment_ref || order.id}</div>
              </div>
            </div>
          )}
        </div>

        <div className="px-6 py-4 text-center text-gray-400" style={{ fontSize: 11, letterSpacing: 2 }}>
          THANK YOU FOR SHOPPING AT TRONMATIX COMPUTER
        </div>
      </div>

      {/* Action buttons */}
      <div className="flex gap-3 mt-6">
        <button onClick={printPDF} className="flex-1 flex items-center justify-center gap-2 bg-gray-800 text-white font-bold py-3 rounded-xl hover:bg-gray-700 transition-colors" style={{ fontFamily: "Rajdhani, sans-serif", fontSize: 14 }}>
          🖨 PRINT / PDF
        </button>
        <button onClick={() => navigate("/orders")} className="flex-1 flex items-center justify-center gap-2 border-2 border-primary text-primary font-bold py-3 rounded-xl hover:bg-primary hover:text-white transition-colors" style={{ fontFamily: "Rajdhani, sans-serif", fontSize: 14 }}>
          📋 MY ORDERS
        </button>
        <button onClick={() => navigate("/")} className="flex-1 bg-primary text-white font-bold py-3 rounded-xl hover:bg-orange-600 transition-colors" style={{ fontFamily: "Rajdhani, sans-serif", fontSize: 14 }}>
          🏠 HOME
        </button>
      </div>
    </div>
  )
}
