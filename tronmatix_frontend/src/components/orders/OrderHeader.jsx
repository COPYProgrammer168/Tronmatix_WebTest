// src/components/orders/OrderHeader.jsx
import { Link } from "react-router-dom";
import { useTheme } from "../../context/ThemeContext";

export default function OrderHeader({ username }) {
  const { dark } = useTheme();
  return (
    <div className="flex items-center justify-between mb-6">
      <div>
        <h1
          className="font-black"
          style={{ fontFamily: "Rajdhani, sans-serif", fontSize: 30, color: dark ? "#f9fafb" : "#111827" }}
        >
          MY ORDERS
        </h1>
        <p style={{ fontSize: 18, color: dark ? "#9ca3af" : "#374151" }}>
          Welcome back,{" "}
          <span className="text-primary font-bold">{username}</span>
        </p>
      </div>
      <Link
        to="/"
        className="text-primary font-bold hover:underline flex items-center gap-1"
        style={{ fontSize: 16 }}
      >
        ← Continue Shopping
      </Link>
    </div>
  );
}
