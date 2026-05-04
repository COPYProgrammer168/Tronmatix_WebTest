// src/pages/OrdersPage.jsx
import { useState } from "react";
import { Link } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import { useTheme } from "../context/ThemeContext";
import { useLang } from "../context/LanguageContext";

import useOrders    from "../hooks/useOrders";
import OrderHeader  from "../components/orders/OrderHeader";
import OrderFilters from "../components/orders/OrderFilters";
import OrderCard    from "../components/orders/OrderCard";
import ConfirmModal from "../components/orders/ConfirmModal";
import QRModal      from "../components/orders/QRModal";

export default function OrdersPage() {
  const { user }  = useAuth();
  const { dark }  = useTheme();
  const { t, isKhmer}     = useLang();
  const [filter,   setFilter]  = useState("all");
  const [expanded, setExpanded] = useState(null);
  const [qrOrder,  setQrOrder]  = useState(null);
  const headfont  = isKhmer ? 'Kh_Jrung_Thom, Khmer OS, sans-serif' : 'HurstBagod, Rajdhani, sans-serif'
  const bodyFont  = isKhmer ? 'KantumruyPro, Khmer OS, sans-serif' : 'Rajdhani, sans-serif'

  const {
    orders, loading, cancelling, deleting,
    confirmModal, closeConfirm, handleConfirm,
    cancelOrder, deleteOrder, markPaid, printReceipt,
  } = useOrders();

  // "all" shows everything; any other filter value matches order.status exactly
  // This now correctly handles "cancelled", "shipped", "pickup" etc.
  const filtered = filter === "all" ? orders : orders.filter((o) => o.status === filter);

  function toggleExpand(orderId) {
    setExpanded((prev) => (prev === orderId ? null : orderId));
  }

  if (!user) return (
    <div className="max-w-[600px] mx-auto px-4 py-20 text-center">
      <div className="text-6xl mb-4">🔒</div>
      <h2 className="font-black mb-3"
        style={{ fontFamily: headfont, fontSize: 24, color: dark ? "#f9fafb" : "#1f2937" }}>
        {t('orders.loginRequired')}
      </h2>
      <p className="mb-6"
        style={{ fontFamily: bodyFont, fontSize: 16, color: dark ? "#9ca3af" : "#6b7280" }}>
        {t('orders.loginToView')}
      </p>
      <Link to="/"
        className="bg-primary text-white font-bold px-8 py-3 rounded-xl hover:bg-orange-600 transition-colors"
        style={{ fontFamily: headfont }}>
        {t('orders.goHome')}
      </Link>
    </div>
  );

  return (
    <>
      {/* Modals */}
      <ConfirmModal
        isOpen={confirmModal.isOpen}
        title={confirmModal.title}
        message={confirmModal.message}
        confirmLabel={confirmModal.confirmLabel}
        confirmStyle={confirmModal.confirmStyle}
        onConfirm={handleConfirm}
        onCancel={closeConfirm}
      />
      {qrOrder && (
        <QRModal
          order={qrOrder}
          onClose={() => setQrOrder(null)}
          onPaid={() => { markPaid(qrOrder.id); setQrOrder(null); }}
        />
      )}

      {/* Page */}
      <div className="max-w-[900px] mx-auto px-4 py-8" style={{ minHeight: "60vh" }}>
        <OrderHeader username={user.username} />
        <OrderFilters filter={filter} setFilter={setFilter} orders={orders} />

        {/* Content */}
        {loading ? (
          <div className="flex justify-center py-20">
            <div className="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin" />
          </div>
        ) : filtered.length === 0 ? (
          <div className="text-center py-20">
            <div className="text-5xl mb-4">📦</div>
            <h3 className="font-bold mb-2"
              style={{ fontFamily: 'Kh_Jrung_Thom, Rajdhani, sans-serif', fontSize: 20, color: dark ? "#9ca3af" : "#6b7280" }}>
              {t('orders.noOrders')}
            </h3>
            <Link to="/" className="text-primary font-bold hover:underline" style={{ fontSize: 15 }}>
              {t('orders.startShopping')}
            </Link>
          </div>
        ) : (
          <div className="space-y-4">
            {filtered.map((order) => (
              <OrderCard
                key={order.order_id || order.id}
                order={order}
                expanded={expanded}
                onToggleExpand={toggleExpand}
                onCancel={cancelOrder}
                onDelete={deleteOrder}
                onShowQR={setQrOrder}
                onPrint={printReceipt}
                cancelling={cancelling}
                deleting={deleting}
              />
            ))}
          </div>
        )}
      </div>
    </>
  );
}