// src/hooks/useOrders.js
import { useState, useEffect, useCallback } from "react";
import axios from "../lib/axios";
import { MOCK_ORDERS } from "../components/orders/OrderBadges";

export default function useOrders() {
  const [orders,       setOrders]       = useState([]);
  const [loading,      setLoading]      = useState(true);
  const [cancelling,   setCancelling]   = useState(null);
  const [deleting,     setDeleting]     = useState(null);
  const [confirmModal, setConfirmModal] = useState({
    isOpen: false, order: null, action: null,
    title: "", message: "", confirmLabel: "", confirmStyle: "danger",
  });

  // ── Load ─────────────────────────────────────────────────────────────────────
  useEffect(() => {
    axios.get("/api/orders")
      .then((res) => setOrders(res.data?.data || res.data || MOCK_ORDERS))
      .catch(() => setOrders(MOCK_ORDERS))
      .finally(() => setLoading(false));
  }, []);

  // ── Confirm modal helpers ────────────────────────────────────────────────────
  function openCancelConfirm(order) {
    setConfirmModal({
      isOpen: true, order, action: "cancel",
      title: "Cancel Order?",
      message: `Cancel order #${order.order_id || order.id}? This cannot be undone.`,
      confirmLabel: "🚫 Yes, Cancel",
      confirmStyle: "warning",
    });
  }

  function openDeleteConfirm(order) {
    setConfirmModal({
      isOpen: true, order, action: "delete",
      title: "Delete Order?",
      message: `Permanently delete order #${order.order_id || order.id}? This cannot be undone.`,
      confirmLabel: "🗑 Yes, Delete",
      confirmStyle: "delete",
    });
  }

  function closeConfirm() {
    setConfirmModal((m) => ({ ...m, isOpen: false }));
  }

  async function handleConfirm() {
    const { order, action } = confirmModal;
    closeConfirm();
    if (action === "cancel") await cancelOrder(order, true);
    if (action === "delete") await deleteOrder(order, true);
  }

  // ── Delete ───────────────────────────────────────────────────────────────────
  async function deleteOrder(order, skipConfirm = false) {
    if (!skipConfirm) { openDeleteConfirm(order); return; }
    setDeleting(order.id);
    try {
      await axios.delete(`/api/orders/${order.id}`);
      setOrders((prev) => prev.filter((o) => o.id !== order.id));
    } catch (e) {
      alert(e.response?.data?.message || "Could not delete order. Please contact support.");
    } finally { setDeleting(null); }
  }

  // ── Cancel ───────────────────────────────────────────────────────────────────
  async function cancelOrder(order, skipConfirm = false) {
    if (!skipConfirm) { openCancelConfirm(order); return; }
    setCancelling(order.id);
    try {
      await axios.post(`/api/orders/${order.id}/cancel`);
      setOrders((prev) =>
        prev.map((o) => o.id === order.id ? { ...o, status: "cancelled" } : o)
      );
    } catch (e) {
      alert(e.response?.data?.message || "Could not cancel order. Please contact support.");
    } finally { setCancelling(null); }
  }

  // ── Mark paid (after QR) ─────────────────────────────────────────────────────
  function markPaid(orderId) {
    setOrders((prev) =>
      prev.map((o) => o.id === orderId ? { ...o, payment_status: "paid" } : o)
    );
  }

  // ── Print receipt ────────────────────────────────────────────────────────────
  const printReceipt = useCallback((order) => {
    const w = window.open("", "_blank", "width=720,height=960");
    const items = order.items || order.order_items || [];
    const discount = Number(order.discount_amount) || 0;

    // Legacy Receipt Format (SHOP COPY)
    const shopReceipt = `
      <div class="legacy-receipt">
        <div class="brand">TRONMATIX COMPUTER</div>
        <div class="subtitle">SHOP - ORDER RECEIPT</div>
        <div class="info-row"><span>Order ID</span><span style="color:#F97316;font-family:monospace">#${order.order_id || order.id}</span></div>
        <div class="info-row"><span>Date</span><span>${new Date(order.created_at || Date.now()).toLocaleDateString("en-GB", { day:"2-digit", month:"short", year:"numeric", hour:"2-digit", minute:"2-digit" })}</span></div>
        <div class="info-row"><span>Customer</span><span>${(order.shipping || order.location)?.name || "—"}</span></div>
        <div class="info-row"><span>Phone</span><span>${(order.shipping || order.location)?.phone || "—"}</span></div>
        <div class="info-row"><span>Address</span><span>${(order.shipping || order.location)?.address || ""}${(order.shipping || order.location)?.city ? ", " + (order.shipping || order.location).city : ""}</span></div>
        <div class="info-row"><span>Payment</span><span>${order.payment_method === "cash" ? "💵 Cash on Delivery" : "📱 ABA BAKONG KHQR"}</span></div>
        ${discount > 0 ? `<div class="info-row"><span>Discount</span><span style="color:#16a34a">${order.discount_code || "—"} — −$${discount.toFixed(2)}</span></div>` : ""}
        <table border="1" style="width:100%; border-collapse:collapse; margin:20px 0;">
          <thead><tr style="background:#f9fafb;"><th>ITEM</th><th>QTY</th><th>UNIT</th><th>TOTAL</th></tr></thead>
          <tbody>${items.map((i) => `<tr>
            <td>${i.name || i.product?.name || "—"}${ (i.warranty_start && i.warranty_end) ? `<br><small style="color:#F97316">🛡 Warranty: ${new Date(i.warranty_start).toLocaleDateString('en-GB')} - ${new Date(i.warranty_end).toLocaleDateString('en-GB')}</small>` : ""}</td>
            <td style="text-align:center">×${i.qty}</td>
            <td style="text-align:right">$${Number(i.price || i.unit_price || 0).toFixed(2)}</td>
            <td style="text-align:right">$${(Number(i.price || i.unit_price || 0) * i.qty).toFixed(2)}</td>
          </tr>`).join("")}</tbody>
          <tfoot>
            <tr><td colspan="3" style="text-align:right">Subtotal</td><td style="text-align:right">$${Number(order.subtotal || order.total).toFixed(2)}</td></tr>
            ${discount > 0 ? `<tr class="discount-row"><td colspan="3" style="text-align:right">🏷 Discount</td><td style="text-align:right">−$${discount.toFixed(2)}</td></tr>` : ""}
            <tr class="total-row"><td colspan="3" style="text-align:right">TOTAL</td><td style="text-align:right">$${Number(order.total).toFixed(2)}</td></tr>
          </tfoot>
        </table>
        <div class="footer">THANK YOU FOR SHOPPING AT TRONMATIX COMPUTER</div>
      </div>
      <div style="page-break-after: always;"></div>
    `;

    // Thermal Receipt Format (CUSTOMER COPY)
    const customerReceipt = `
      <div class="thermal-receipt">
        <div class="brand">TRONMATIX</div>
        <div class="subtitle">CUSTOMER</div>
        <div class="info-row"><span>ID:</span><span>#${order.order_id || order.id}</span></div>
        <div class="info-row"><span>Date:</span><span>${new Date(order.created_at || Date.now()).toLocaleDateString("en-GB")}</span></div>
        <div class="separator"></div>
        <table>
          <tbody>
            ${items.map((i) => `
              <tr>
                <td class="item-name">${i.name || i.product?.name || "—"}${ (i.warranty_start && i.warranty_end) ? `<br><small style="font-size:9px">🛡 Warranty: ${new Date(i.warranty_start).toLocaleDateString('en-GB')} - ${new Date(i.warranty_end).toLocaleDateString('en-GB')}</small>` : ""}</td>
                <td class="item-qty">x${i.qty}</td>
                <td class="item-total">$${(Number(i.price || i.unit_price || 0) * i.qty).toFixed(2)}</td>
              </tr>
            `).join("")}
          </tbody>
        </table>
        <div class="separator"></div>
        <div class="info-row"><span>SUBTOTAL</span><span>$${Number(order.subtotal || order.total).toFixed(2)}</span></div>
        ${discount > 0 ? `<div class="info-row"><span>DISCOUNT</span><span>-$${discount.toFixed(2)}</span></div>` : ""}
        <div class="info-row total-row"><span>TOTAL</span><span>$${Number(order.total).toFixed(2)}</span></div>
        <div class="separator"></div>
        <div class="footer">*** THANK YOU ***</div>
      </div>
    `;

    w.document.write(`<!DOCTYPE html><html><head><meta charset="utf-8">
      <title>Receipt #${order.order_id || order.id}</title>
      <style>
        .legacy-receipt { font-family:Arial,sans-serif; padding:32px; color:#111; max-width:680px; margin:0 auto; }
        .legacy-receipt .brand { font-size:24px; font-weight:900; letter-spacing:3px; color:#F97316; }
        .legacy-receipt .subtitle { color:#666; font-size:12px; margin-bottom:20px; }
        .legacy-receipt .info-row { display:flex; justify-content:space-between; padding:5px 0; font-size:13px; }
        .legacy-receipt table { width:100%; border-collapse:collapse; margin:20px 0; }
        .legacy-receipt thead th { background:#f9fafb; padding:8px; font-size:12px; text-align:left; }
        .legacy-receipt tbody td { padding:8px; border-bottom:1px solid #eee; font-size:13px; }
        .legacy-receipt tfoot td { padding:8px; font-weight:bold; }
        
        .thermal-receipt { font-family:'Courier New',Courier,monospace; padding:10px; color:#000; width:300px; margin:0 auto; }
        .thermal-receipt .brand { font-size:20px; font-weight:900; text-align:center; }
        .thermal-receipt .subtitle { text-align:center; font-size:12px; margin-bottom:15px; }
        .thermal-receipt .info-row { display:flex; justify-content:space-between; font-size:12px; }
        .thermal-receipt .separator { border-top:1px dashed #000; margin:10px 0; }
        .thermal-receipt table { width:100%; }
        .thermal-receipt .item-name { width:60%; font-size:12px; }
        .thermal-receipt .item-qty { text-align:center; width:10%; font-size:12px; }
        .thermal-receipt .item-total { text-align:right; width:30%; font-size:12px; }
        .thermal-receipt .total-row { font-weight:900; border-top:1px dashed #000; }
        .thermal-receipt .footer { text-align:center; font-size:10px; margin-top:20px; }
        
        @media print { body { width:100%; padding:0; } }
      </style></head><body>
      ${shopReceipt}
      ${customerReceipt}
    </body></html>`);
    w.document.close();
    setTimeout(() => w.print(), 600);
  }, []);

  return {
    orders, loading, cancelling, deleting,
    confirmModal, closeConfirm, handleConfirm,
    cancelOrder, deleteOrder, markPaid, printReceipt,
  };
}
