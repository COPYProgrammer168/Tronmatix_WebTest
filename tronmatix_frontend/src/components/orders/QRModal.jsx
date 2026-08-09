// src/components/orders/QRModal.jsx
import { useEffect } from "react";
import { useLang } from "../../context/LanguageContext";
import BakongQRPanel from "./BakongQRPanel";
import { useCart } from "../../context/CartContext";

export default function QRModal({ order, onClose, onPaid }) {
  const { t, isKhmer } = useLang();
  const { clearCart } = useCart();

  // Lock body scroll while modal is open
  useEffect(() => {
    document.body.style.overflow = "hidden";
    return () => {
      document.body.style.overflow = "";
    };
  }, []);

  const handlePaid = () => {
    clearCart();
    onPaid?.();
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
        className="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden max-h-[90vh] overflow-y-auto"
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

        {/* ── BakongQRPanel ──────────────────────────────────────────────── */}
        <div>
          <BakongQRPanel
            orderId={order.id ?? order.order_id}
            total={order.total}
            subtotal={order.subtotal}
            discountAmount={order.discount_amount}
            discountCode={order.discount_code}
            items={order.items}
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
