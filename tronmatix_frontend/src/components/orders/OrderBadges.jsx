// src/components/orders/OrderBadges.jsx

export const STATUS_COLORS = {
  confirmed:  { bg: "bg-blue-50",   text: "text-blue-600",   border: "border-blue-200",   dot: "bg-blue-500" },
  processing: { bg: "bg-yellow-50", text: "text-yellow-600", border: "border-yellow-200", dot: "bg-yellow-500" },
  shipped:    { bg: "bg-purple-50", text: "text-purple-600", border: "border-purple-200", dot: "bg-purple-500" },
  delivered:  { bg: "bg-green-50",  text: "text-green-600",  border: "border-green-200",  dot: "bg-green-500" },
  cancelled:  { bg: "bg-red-50",    text: "text-red-600",    border: "border-red-200",    dot: "bg-red-500" },
  pending:    { bg: "bg-gray-50",   text: "text-gray-600",   border: "border-gray-200",   dot: "bg-gray-400" },
};

// Delivery pipeline: confirmed → processing → shipped → delivered
export const STATUS_STEPS_DELIVERY = ["confirmed", "processing", "shipped", "delivered"];

// Pickup pipeline: confirmed → processing (ready) → delivered (picked up) — no shipped
export const STATUS_STEPS_PICKUP = ["confirmed", "processing", "delivered"];

// Returns the correct steps array based on fulfillment type
export function getStatusSteps(fulfillmentType) {
  return fulfillmentType === "pickup" ? STATUS_STEPS_PICKUP : STATUS_STEPS_DELIVERY;
}

// Human-readable label per status, varies for pickup vs delivery
export function getStatusLabel(status, fulfillmentType) {
  const isPickup = fulfillmentType === "pickup";
  const labels = {
    confirmed:  "CONFIRMED",
    processing: isPickup ? "READY" : "PROCESSING",
    shipped:    "SHIPPED",
    delivered:  isPickup ? "PICKED UP" : "DELIVERED",
    cancelled:  "CANCELLED",
    pending:    "PENDING",
  };
  return labels[status] || (status?.toUpperCase() ?? "PENDING");
}

export const MOCK_ORDERS = [
  {
    id: "ORD-001", order_id: "TRX-ABCD1234",
    created_at: new Date(Date.now() - 86400000 * 2).toISOString(),
    status: "delivered", total: 698, subtotal: 748,
    discount_amount: 50, discount_code: "TRONMATIX10",
    payment_method: "cash", payment_status: "pending",
    fulfillment_type: "delivery",
    shipping: { name: "Test User", phone: "012345678", address: "Phnom Penh", city: "Phnom Penh" },
    items: [{ name: "AMD RYZEN 7 9700X", qty: 1, price: 349 }, { name: "DDR5 RAM 32GB", qty: 1, price: 399 }],
  },
  {
    id: "ORD-002", order_id: "TRX-EFGH5678",
    created_at: new Date(Date.now() - 3600000 * 3).toISOString(),
    status: "processing", total: 349, subtotal: 349,
    discount_amount: 0, discount_code: null,
    payment_method: "bakong", payment_status: "paid",
    fulfillment_type: "pickup",
    shipping: { name: "Test User", phone: "012345678", address: "Store Pickup", city: "" },
    items: [{ name: "AMD RYZEN 7 9800X3D", qty: 1, price: 349 }],
  },
];

// StatusBadge — pass fulfillmentType so label changes for pickup
export function StatusBadge({ status, fulfillmentType }) {
  const c = STATUS_COLORS[status] || STATUS_COLORS.pending;
  const label = getStatusLabel(status, fulfillmentType);
  return (
    <span
      className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-bold border ${c.bg} ${c.text} ${c.border}`}
      style={{ fontSize: 12 }}
    >
      <span className={`w-2 h-2 rounded-full ${c.dot}`} />
      {label}
    </span>
  );
}

export function PaymentStatusBadge({ paymentMethod, paymentStatus, fulfillmentType }) {
  const isPickup = fulfillmentType === "pickup";
  if (paymentMethod === "cash")
    return (
      <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-bold border bg-gray-50 text-gray-500 border-gray-200" style={{ fontSize: 12 }}>
        {isPickup ? "💵 PAY AT STORE" : "💵 COD"}
      </span>
    );
  if (paymentStatus === "paid")
    return <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-bold border bg-green-50 text-green-600 border-green-200" style={{ fontSize: 12 }}>✅ PAID</span>;
  if (paymentStatus === "failed")
    return <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-bold border bg-red-50 text-red-500 border-red-200" style={{ fontSize: 12 }}>❌ FAILED</span>;
  return <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-bold border bg-yellow-50 text-yellow-600 border-yellow-200 animate-pulse" style={{ fontSize: 12 }}>⏳ PENDING</span>;
}