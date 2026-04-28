// src/components/orders/OrderFilters.jsx
import { useTheme } from "../../context/ThemeContext";
import { useLang } from "../../context/LanguageContext";

const FILTERS = ["all", "confirmed", "processing", "shipped", "delivered", "cancelled"];

// ✅ Fixed: processing was wrongly mapped to "orders.pending"
const FILTER_KEYS = {
  all:        "orders.all",
  confirmed:  "orders.confirmed",
  processing: "orders.processing",   // was "orders.pending" — wrong key
  shipped:    "orders.shipped",
  delivered:  "orders.delivered",
  cancelled:  "orders.cancelled",    // was missing entirely
};

export default function OrderFilters({ filter, setFilter, orders }) {
  const { dark }        = useTheme();
  const { t, isKhmer } = useLang();
  const filterFont = isKhmer ? "KantumruyPro, Khmer OS, sans-serif" : "Rajdhani, sans-serif";
  const filterBg   = dark ? "#1f2937" : "#f3f4f6";
  const textSub    = dark ? "#9ca3af" : "#374151";

  // Pre-compute count per status so each pill shows a number
  const counts = FILTERS.reduce((acc, f) => {
    acc[f] = f === "all"
      ? orders.length
      : orders.filter((o) => o.status === f).length;
    return acc;
  }, {});

  return (
    <div className="flex gap-2 mb-6 overflow-x-auto pb-1">
      {FILTERS.map((f) => {
        const count  = counts[f];
        const active = filter === f;
        // Hide tabs with 0 orders except "all" and the currently active tab
        if (count === 0 && f !== "all" && !active) return null;
        return (
          <button
            key={f}
            onClick={() => setFilter(f)}
            className="px-4 py-2 rounded-full font-bold whitespace-nowrap transition-all flex items-center gap-1.5"
            style={{
              fontSize: 13, fontFamily: filterFont,
              background: active ? "#F97316" : filterBg,
              color: active ? "#fff" : textSub,
            }}
          >
            {t(FILTER_KEYS[f] || f)}
            <span style={{
              fontSize: 11, fontWeight: 800,
              background: active
                ? "rgba(0,0,0,0.18)"
                : (dark ? "rgba(255,255,255,0.1)" : "rgba(0,0,0,0.08)"),
              borderRadius: 999, padding: "1px 7px", lineHeight: "18px",
            }}>
              {count}
            </span>
          </button>
        );
      })}
    </div>
  );
}