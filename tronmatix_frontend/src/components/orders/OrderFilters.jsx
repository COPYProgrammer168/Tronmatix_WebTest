// src/components/orders/OrderFilters.jsx
import { useTheme } from "../../context/ThemeContext";

const FILTERS = ["all", "confirmed", "processing", "shipped", "delivered"];

export default function OrderFilters({ filter, setFilter, orders }) {
  const { dark } = useTheme();
  const filterBg = dark ? "#1f2937" : "#f3f4f6";
  const textSub  = dark ? "#9ca3af" : "#374151";

  return (
    <div className="flex gap-2 mb-6 overflow-x-auto pb-1">
      {FILTERS.map((f) => (
        <button
          key={f}
          onClick={() => setFilter(f)}
          className="px-4 py-2 rounded-full font-bold whitespace-nowrap transition-all"
          style={{
            fontSize: 13,
            background: filter === f ? "#F97316" : filterBg,
            color: filter === f ? "#fff" : textSub,
          }}
        >
          {f === "all"
            ? `All (${orders.length})`
            : f.charAt(0).toUpperCase() + f.slice(1)}
        </button>
      ))}
    </div>
  );
}
