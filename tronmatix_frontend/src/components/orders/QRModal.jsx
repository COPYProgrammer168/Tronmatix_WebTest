// src/components/orders/QRModal.jsx
import { useEffect } from "react";
import { useLang } from "../../context/LanguageContext";
import BakongQRPanel from "./BakongQRPanel";

export default function QRModal({ order, onClose, onPaid }) {
  const { t, isKhmer } = useLang();

  // Lock body scroll while modal is open
  useEffect(() => {
    document.body.style.overflow = "hidden";
    return () => {
      document.body.style.overflow = "";
    };
  }, []);

  const handlePaid = () => {
    onPaid?.();
    // Keep success screen visible for 2s before closing
    setTimeout(onClose, 2000);
  };

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center p-4"
      style={{
        background: "rgba(0,0,0,0.72)",
        backdropFilter: "blur(4px)",
        animation: "fadeInOverlay 0.15s ease",
      }}
      onClick={onClose}
    >
      <div
        className="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden max-h-[90vh] overflow-y-auto"
        style={{ animation: "popIn 0.2s cubic-bezier(0.34,1.56,0.64,1)" }}
        onClick={(e) => e.stopPropagation()}
      >
        {/* ── Modal header ───────────────────────────────────────────────── */}
        <div className="flex items-center justify-between px-5 pt-5 pb-3 border-b border-gray-100">
          <div>
            <div className="font-black text-gray-800" style={{ fontSize: 17 }}>
              📱 {isKhmer ? t("qr.title") : "KHQR Payment"}
            </div>
            <div className="text-gray-400" style={{ fontSize: 12 }}>
              Order #{order.order_id || order.id}
            </div>
          </div>
          <div className="flex items-center gap-2">
            <div
              className="text-white rounded-lg px-2 py-1 font-black"
              style={{ fontSize: 11, background: "#003082" }}
            >
              ABA
            </div>
            <button
              onClick={onClose}
              className="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 font-bold transition-colors"
              title="Close"
            >
              ✕
            </button>
          </div>
        </div>

        {/* ── Order summary ──────────────────────────────────────────────── */}
        <div className="px-5 py-3 border-b border-gray-100 bg-gray-50">
          {/* Subtotal — only when discount exists */}
          {Number(order.discount_amount) > 0 && (
            <div
              className="flex justify-between mb-1"
              style={{ fontSize: 13, color: "#6b7280" }}
            >
              <span>Subtotal</span>
              <span>${Number(order.subtotal ?? order.total).toFixed(2)}</span>
            </div>
          )}
          {/* Discount */}
          {Number(order.discount_amount) > 0 && (
            <div
              className="flex justify-between mb-1 font-bold"
              style={{ fontSize: 13, color: "#16a34a" }}
            >
              <span>
                🏷 Discount
                {order.discount_code ? ` (${order.discount_code})` : ""}
              </span>
              <span>−${Number(order.discount_amount).toFixed(2)}</span>
            </div>
          )}
          {/* Total */}
          <div
            className="flex justify-between font-black"
            style={{ fontSize: 16, color: "#111827" }}
          >
            <span>Total to Pay</span>
            <span style={{ color: "#C8102E" }}>
              ${Number(order.total).toFixed(2)}
            </span>
          </div>
        </div>

        {/* ── BakongQRPanel ──────────────────────────────────────────────── */}
        <div>
          <BakongQRPanel
            orderId={order.id ?? order.order_id}
            total={order.total}
            onPaid={handlePaid}
          />
        </div>
      </div>

      <style>{`
        @keyframes fadeInOverlay {
          from { opacity: 0; }
          to   { opacity: 1; }
        }
        @keyframes popIn {
          from { opacity: 0; transform: scale(0.85) translateY(16px); }
          to   { opacity: 1; transform: scale(1)    translateY(0);    }
        }
      `}</style>
    </div>
  );
}
