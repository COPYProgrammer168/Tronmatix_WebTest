// src/components/orders/OrderCard.jsx
import { useTheme } from "../../context/ThemeContext";
import { useLang } from "../../context/LanguageContext";
import { StatusBadge, PaymentStatusBadge } from "./OrderBadges";
import FulfillmentBadge from "./FulfillmentBadge";
import OrderExpandedPanel from "./OrderExpandedPanel";

export default function OrderCard({
  order,
  expanded,
  onToggleExpand,
  onCancel,
  onDelete,
  onShowQR,
  onPrint,
  cancelling,
  deleting,
}) {
  const { dark } = useTheme();
  const { t, isKhmer } = useLang();
  const cardFont = isKhmer ? "KantumruyPro, Khmer OS, sans-serif" : "Rajdhani, sans-serif";
  const cardBg   = dark ? "#1f2937" : "#ffffff";
  const border   = dark ? "#374151" : "#e5e7eb";
  const textMain = dark ? "#f9fafb" : "#111827";
  const textSub  = dark ? "#9ca3af" : "#6b7280";

  const orderId        = order.order_id || order.id;
  const isExpanded     = expanded === orderId;
  const fulfillmentType = order.fulfillment_type ?? "delivery"; // ← pickup | delivery
  const isPickup       = fulfillmentType === "pickup";

  return (
    <div
      className="rounded-xl overflow-hidden hover:shadow-md transition-shadow"
      style={{ border: `1px solid ${border}`, background: cardBg }}
    >
      {/* Header row */}
      <div className="flex items-center justify-between p-4 gap-3 flex-wrap">

        {/* Left: order info (click to expand) */}
        <div
          className="flex items-center gap-3 flex-wrap flex-1 cursor-pointer rounded-lg p-1 -m-1 transition-colors"
          onMouseEnter={(e) => e.currentTarget.style.background = dark ? "#374151" : "#f9fafb"}
          onMouseLeave={(e) => e.currentTarget.style.background = "transparent"}
          onClick={() => onToggleExpand(orderId)}
        >
          <div>
            <div className="font-black" style={{ fontSize: 16, color: textMain }}>#{orderId}</div>
            <div style={{ fontSize: 13, color: textSub }}>
              {new Date(order.created_at || Date.now()).toLocaleDateString("en-GB", {
                day: "2-digit", month: "short", year: "numeric",
              })}{" "}
              <span style={{ fontSize: 11 }}>
                {new Date(order.created_at || Date.now()).toLocaleTimeString("en-GB", {
                  hour: "2-digit", minute: "2-digit",
                })}
              </span>
            </div>
          </div>
          <StatusBadge status={order.status || "confirmed"} fulfillmentType={fulfillmentType} />
          <PaymentStatusBadge paymentMethod={order.payment_method} paymentStatus={order.payment_status} fulfillmentType={fulfillmentType} />
          <FulfillmentBadge type={fulfillmentType} />
        </div>

        {/* Right: price + buttons */}
        <div className="flex items-center gap-2 flex-wrap">
          <div className="text-right mr-2">
            <div className="font-black text-primary" style={{ fontSize: 20 }}>
              ${Number(order.total).toFixed(2)}
            </div>
            {/* Handle both API field name (discount_amount) and local snapshot (_discountAmount) */}
            {Number(order.discount_amount || order._discountAmount || 0) > 0 && (
              <div className="text-green-500 font-bold" style={{ fontSize: 12 }}>
                🏷 {order.discount_code || order._discountCode
                  ? `${order.discount_code || order._discountCode} · `
                  : ''}
                −${Number(order.discount_amount || order._discountAmount).toFixed(2)} saved
              </div>
            )}
            <div style={{ fontSize: 13, color: textSub }}>
              {order.payment_method === "cash"
                ? (isPickup ? "💵 Pay at Store" : "💵 Cash")
                : "📱 BAKONG"}
            </div>
          </div>

          {/* Show QR */}
          {order.payment_method === "bakong" && order.payment_status !== "paid" && (
            <button
              onClick={(e) => { e.stopPropagation(); onShowQR(order); }}
              className="flex items-center gap-1.5 px-3 py-2 rounded-lg font-bold border-2 border-blue-300 text-blue-500 hover:bg-blue-500 hover:text-white hover:scale-105 active:scale-95 transition-all shadow-sm"
              style={{ fontSize: 12 }}
            >
              📱 {isKhmer ? t("orders.showQR") : "Show QR"}
            </button>
          )}

          {/* Cancel */}
          {["confirmed", "pending"].includes(order.status) && (
            <button
              onClick={(e) => { e.stopPropagation(); onCancel(order); }}
              disabled={cancelling === order.id}
              className="flex items-center gap-1.5 px-3 py-2 rounded-lg font-bold border-2 border-orange-300 text-orange-500 hover:bg-orange-500 hover:text-white hover:scale-105 active:scale-95 transition-all disabled:opacity-50 shadow-sm"
              style={{ fontSize: 12 }}
            >
              {cancelling === order.id ? (
                <><span className="w-3 h-3 border-2 border-orange-500 border-t-transparent rounded-full animate-spin inline-block" /> {isKhmer ? t("orders.cancelling") : "Cancelling…"}</>
              ) : <>{isKhmer ? `🚫 ${t("orders.cancel")}` : "🚫 Cancel"}</>}
            </button>
          )}

          {/* Delete */}
          {order.status === "cancelled" && (
            <button
              onClick={(e) => { e.stopPropagation(); onDelete(order); }}
              disabled={deleting === order.id}
              className="flex items-center gap-1.5 px-3 py-2 rounded-lg font-bold border-2 border-red-400 text-red-500 hover:bg-red-500 hover:text-white hover:scale-105 active:scale-95 transition-all disabled:opacity-50 shadow-sm"
              style={{ fontSize: 12 }}
            >
              {deleting === order.id ? (
                <><span className="w-3 h-3 border-2 border-red-500 border-t-transparent rounded-full animate-spin inline-block" /> {isKhmer ? t("orders.deleting") : "Deleting…"}</>
              ) : <>{isKhmer ? `🗑 ${t("common.delete")}` : "🗑 Delete"}</>}
            </button>
          )}

          {/* Print */}
          <button
            onClick={(e) => { e.stopPropagation(); onPrint(order); }}
            className="flex items-center gap-1.5 px-3 py-2 rounded-lg font-bold border-2 transition-all"
            style={{ fontSize: 12, borderColor: border, color: textSub }}
          >
            🖨 {isKhmer ? t("orders.receipt") : "Receipt"}
          </button>

          {/* Expand toggle */}
          <button
            onClick={() => onToggleExpand(orderId)}
            className="p-2 rounded-lg transition-colors"
            style={{ background: dark ? "#374151" : "#f3f4f6" }}
          >
            <svg
              className={`w-5 h-5 transition-transform ${isExpanded ? "rotate-180" : ""}`}
              style={{ color: textSub }}
              fill="none" stroke="currentColor" viewBox="0 0 24 24" strokeWidth={2}
            >
              <path strokeLinecap="round" strokeLinejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
        </div>
      </div>

      {/* Expanded panel */}
      {isExpanded && (
        <OrderExpandedPanel
          order={order}
          onShowQR={onShowQR}
          onPrint={onPrint}
        />
      )}
    </div>
  );
}