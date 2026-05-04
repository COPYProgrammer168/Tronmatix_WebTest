// src/components/orders/ConfirmModal.jsx
import { useEffect } from "react";
import { useLang } from "../../context/LanguageContext";

export default function ConfirmModal({ isOpen, onConfirm, onCancel, title, message, confirmLabel, confirmStyle = "danger" }) {
  const { t, isKhmer } = useLang();
  useEffect(() => {
    if (isOpen) document.body.style.overflow = "hidden";
    else document.body.style.overflow = "";
    return () => { document.body.style.overflow = ""; };
  }, [isOpen]);

  if (!isOpen) return null;

  const styles = {
    danger:  { btn: "bg-red-500 hover:bg-red-600",    ring: "focus:ring-red-400",    icon: "⚠️" },
    warning: { btn: "bg-orange-500 hover:bg-orange-600", ring: "focus:ring-orange-400", icon: "🚨" },
    delete:  { btn: "bg-red-700 hover:bg-red-800",    ring: "focus:ring-red-500",    icon: "🗑" },
  };
  const s = styles[confirmStyle] || styles.danger;

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center p-4"
      style={{ background: "rgba(0,0,0,0.55)", backdropFilter: "blur(4px)", animation: "fadeInOverlay 0.15s ease" }}
      onClick={onCancel}
    >
      <div
        className="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden"
        style={{ animation: "popIn 0.2s cubic-bezier(0.34,1.56,0.64,1)" }}
        onClick={(e) => e.stopPropagation()}
      >
        <div className="flex flex-col items-center pt-8 pb-4 px-6">
          <div style={{ fontSize: 48, lineHeight: 1, animation: "wobble 0.4s ease 0.15s both" }}>{s.icon}</div>
          <h3 className="font-black text-gray-800 mt-3 text-center" style={{ fontSize: 20 }}>{title}</h3>
          <p className="text-gray-500 mt-2 text-center leading-relaxed" style={{ fontSize: 14 }}>{message}</p>
        </div>

        <div className="flex gap-3 px-6 pb-6 mt-2">
          <button onClick={onCancel}
            className="flex-1 py-3 rounded-xl font-bold border-2 border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition-all"
            style={{ fontSize: 14 }}>
            {isKhmer ? t("orders.keepIt") : "Keep it"}
          </button>
          <button onClick={onConfirm}
            className={`flex-1 py-3 rounded-xl font-black text-white transition-all ${s.btn} focus:outline-none focus:ring-2 ${s.ring} active:scale-95`}
            style={{ fontSize: 14 }}>
            {confirmLabel}
          </button>
        </div>
      </div>

      <style>{`
        @keyframes fadeInOverlay { from { opacity:0 } to { opacity:1 } }
        @keyframes popIn {
          from { opacity:0; transform: scale(0.85) translateY(16px) }
          to   { opacity:1; transform: scale(1) translateY(0) }
        }
        @keyframes wobble {
          0%   { transform: rotate(0deg)   scale(1) }
          25%  { transform: rotate(-12deg) scale(1.15) }
          50%  { transform: rotate(10deg)  scale(1.1) }
          75%  { transform: rotate(-6deg)  scale(1.05) }
          100% { transform: rotate(0deg)  scale(1) }
        }
      `}</style>
    </div>
  );
}