// src/components/orders/DeliveryTracker.jsx
import { useState, useEffect } from "react";
import { useTheme } from "../../context/ThemeContext";
import { useLang } from "../../context/LanguageContext";
// ✅ Fix 1: import the new helpers instead of deleted STATUS_STEPS
import { getStatusSteps, getStatusLabel } from "./OrderBadges";

function useNow() {
  const [now, setNow] = useState(() => new Date());
  useEffect(() => {
    const timer = setInterval(() => setNow(new Date()), 1000);
    return () => clearInterval(timer);
  }, []);
  return now;
}

export default function DeliveryTracker({ status, order, fulfillmentType }) {
  const now      = useNow();
  const { dark } = useTheme();
  const { t, isKhmer } = useLang();

  // ✅ Fix 2: pick the correct pipeline based on fulfillment type
  const isPickup    = (fulfillmentType ?? order?.fulfillment_type ?? "delivery") === "pickup";
  const STATUS_STEPS = getStatusSteps(isPickup ? "pickup" : "delivery");
  const current      = STATUS_STEPS.indexOf(status);

  // ✅ Fix 3: labels and icons match the correct pipeline length
  // Delivery: Confirmed / Processing / Shipped / Delivered
  // Pickup:   Confirmed / Ready     / Picked Up
  const labels = isPickup
    ? (isKhmer
        ? [t("orders.confirmed"), t("orders.processing"), t("orders.delivered")]
        : ["Confirmed", "Ready", "Picked Up"])
    : (isKhmer
        ? [t("orders.confirmed"), t("orders.processing"), t("orders.shipped"), t("orders.delivered")]
        : ["Confirmed", "Processing", "Shipped", "Delivered"]);

  const icons = isPickup
    ? ["✅", "📦", "🏪"]
    : ["✅", "⚙️", "🚚", "📦"];

  // ✅ Fix 4: delivery date note uses pickup-aware labels
  let deliveryNote = null;
  if (order?.delivery_date) {
    const schedDay = new Date(order.delivery_date);
    schedDay.setHours(0, 0, 0, 0);
    const today = new Date(now);
    today.setHours(0, 0, 0, 0);
    const diffDays = Math.round((schedDay - today) / 86400000);

    if (status === "delivered") {
      deliveryNote = {
        color: "#16a34a", icon: "🏪",
        label: isPickup ? "PICKED UP ON" : "DELIVERED ON",
        extra: null,
      };
    } else if (diffDays < 0) {
      deliveryNote = {
        color: "#ef4444", icon: "⚠️",
        label: isPickup ? "PICKUP OVERDUE BY" : "OVERDUE BY",
        extra: `${Math.abs(diffDays)} day${Math.abs(diffDays) !== 1 ? "s" : ""}`,
      };
    } else if (diffDays === 0) {
      deliveryNote = {
        color: "#F97316", icon: "🚨",
        label: isPickup ? "PICKUP TODAY" : "DELIVERY TODAY",
        extra: null,
      };
    } else if (diffDays === 1) {
      deliveryNote = {
        color: "#7c3aed", icon: isPickup ? "🏪" : "🚚",
        label: isPickup ? "PICKUP TOMORROW" : "DELIVERY TOMORROW",
        extra: null,
      };
    } else {
      deliveryNote = {
        color: "#7c3aed", icon: "📅",
        label: isPickup ? "SCHEDULED PICKUP" : "SCHEDULED DELIVERY",
        extra: `in ${diffDays} days`,
      };
    }
  }

  return (
    <div>
      {/* Live clock */}
      <div className="flex items-center gap-2 mb-3">
        <span style={{ fontSize: 12, color: dark ? "#9ca3af" : "#6b7280" }}>🕐 Live:</span>
        <span className="font-black text-primary" style={{ fontSize: 13, fontFamily: "monospace" }}>
          {now.toLocaleDateString("en-GB", {
            weekday: "short", day: "2-digit", month: "short", year: "numeric",
          })}
          {" — "}
          {now.toLocaleTimeString("en-GB", {
            hour: "2-digit", minute: "2-digit", second: "2-digit",
          })}
        </span>
      </div>

      {/* Progress steps — correct length per fulfillment type */}
      <div className="flex items-center mt-1">
        {STATUS_STEPS.map((step, i) => (
          <div key={step} className="flex items-center flex-1">
            <div className="flex flex-col items-center">
              <div
                className="w-8 h-8 rounded-full flex items-center justify-center font-bold transition-all"
                style={{
                  fontSize: i <= current ? 14 : 11,
                  background: i <= current ? "#F97316" : (dark ? "#4b5563" : "#e5e7eb"),
                  color:      i <= current ? "#fff"    : (dark ? "#9ca3af" : "#6b7280"),
                }}
              >
                {i <= current ? icons[i] : i + 1}
              </div>
              <div
                className="mt-1 font-semibold text-center"
                style={{
                  fontSize: 11,
                  color: i <= current ? "#F97316" : (dark ? "#6b7280" : "#9ca3af"),
                }}
              >
                {labels[i]}
              </div>
            </div>
            {i < STATUS_STEPS.length - 1 && (
              <div
                className="h-1 flex-1 mx-1 rounded"
                style={{ background: i < current ? "#F97316" : (dark ? "#4b5563" : "#d1d5db") }}
              />
            )}
          </div>
        ))}
      </div>

      {/* Scheduled date / pickup date card */}
      {deliveryNote && order?.delivery_date && (
        <div
          className="mt-3 px-3 py-2 rounded-lg flex items-center justify-between gap-2"
          style={{
            background: deliveryNote.color + "14",
            border: `1px solid ${deliveryNote.color}44`,
          }}
        >
          <div className="flex items-center gap-2">
            <span style={{ fontSize: 20 }}>{deliveryNote.icon}</span>
            <div>
              <div
                className="font-black"
                style={{ fontSize: 12, color: deliveryNote.color, letterSpacing: 1 }}
              >
                {deliveryNote.label}
              </div>
              <div className="font-bold" style={{ fontSize: 14, color: dark ? "#d1d5db" : "#1f2937" }}>
                {new Date(order.delivery_date).toLocaleDateString("en-GB", {
                  weekday: "long", day: "2-digit", month: "long", year: "numeric",
                })}
                {order.delivery_time_slot && (
                  <span className="ml-2" style={{ color: deliveryNote.color }}>
                    🕐 {order.delivery_time_slot}
                  </span>
                )}
              </div>
            </div>
          </div>
          {deliveryNote.extra && (
            <span
              className="font-black rounded-full px-3 py-1"
              style={{
                fontSize: 12,
                background: deliveryNote.color + "20",
                color: deliveryNote.color,
              }}
            >
              {deliveryNote.extra}
            </span>
          )}
        </div>
      )}
    </div>
  );
}