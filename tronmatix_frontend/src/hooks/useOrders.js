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
    w.document.write(`<!DOCTYPE html><html><head><meta charset="utf-8">
      <title>Receipt #${order.order_id || order.id}</title>
      <style>
        *{box-sizing:border-box}
        body{font-family:Arial,sans-serif;padding:32px;color:#111;max-width:680px;margin:0 auto}
        .brand{font-size:24px;font-weight:900;letter-spacing:3px;color:#F97316}
        .subtitle{color:#666;font-size:12px;margin:2px 0 20px;letter-spacing:2px}
        .info-row{display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid #f0f0f0;font-size:14px}
        .info-row span:first-child{color:#666} .info-row span:last-child{font-weight:600}
        table{width:100%;border-collapse:collapse;margin:20px 0}
        thead th{background:#f9fafb;font-size:12px;text-align:left;padding:10px 8px;border-bottom:2px solid #e5e7eb;letter-spacing:1px}
        tbody td{padding:9px 8px;border-bottom:1px solid #f5f5f5;font-size:13px}
        tfoot td{padding:8px;font-weight:700;font-size:14px}
        .discount-row td{color:#16a34a}
        .total-row td{font-size:18px;font-weight:900;color:#F97316;padding-top:12px}
        .footer{text-align:center;color:#9ca3af;font-size:11px;margin-top:28px;border-top:1px solid #eee;padding-top:16px;letter-spacing:1px}
        @media print{body{padding:16px}}
      </style></head><body>
      <div class="brand">TRONMATIX COMPUTER</div>
      <div class="subtitle">ORDER RECEIPT</div>
      <div class="info-row"><span>Order ID</span><span style="color:#F97316;font-family:monospace">#${order.order_id || order.id}</span></div>
      <div class="info-row"><span>Date</span><span>${new Date(order.created_at || Date.now()).toLocaleDateString("en-GB", { day:"2-digit", month:"short", year:"numeric", hour:"2-digit", minute:"2-digit" })}</span></div>
      <div class="info-row"><span>Customer</span><span>${(order.shipping || order.location)?.name || "—"}</span></div>
      <div class="info-row"><span>Phone</span><span>${(order.shipping || order.location)?.phone || "—"}</span></div>
      <div class="info-row"><span>Address</span><span>${(order.shipping || order.location)?.address || ""}${(order.shipping || order.location)?.city ? ", " + (order.shipping || order.location).city : ""}</span></div>
      <div class="info-row"><span>Payment</span><span>${order.payment_method === "cash" ? "💵 Cash on Delivery" : "📱 ABA BAKONG KHQR"}</span></div>
      ${discount > 0 ? `<div class="info-row"><span>Discount</span><span style="color:#16a34a">${order.discount_code || "—"} — −$${discount.toFixed(2)}</span></div>` : ""}
      <table>
        <thead><tr><th>ITEM</th><th style="text-align:center">QTY</th><th style="text-align:right">UNIT</th><th style="text-align:right">TOTAL</th></tr></thead>
        <tbody>${items.map((i) => `<tr><td>${i.name || i.product?.name || "—"}</td><td style="text-align:center">×${i.qty}</td><td style="text-align:right">$${Number(i.price || i.unit_price || 0).toFixed(2)}</td><td style="text-align:right">$${(Number(i.price || i.unit_price || 0) * i.qty).toFixed(2)}</td></tr>`).join("")}</tbody>
        <tfoot>
          <tr><td colspan="3" style="text-align:right;color:#666">Subtotal</td><td style="text-align:right">$${Number(order.subtotal || order.total).toFixed(2)}</td></tr>
          ${discount > 0 ? `<tr class="discount-row"><td colspan="3" style="text-align:right">🏷 Discount</td><td style="text-align:right">−$${discount.toFixed(2)}</td></tr>` : ""}
          <tr class="total-row"><td colspan="3" style="text-align:right">TOTAL</td><td style="text-align:right">$${Number(order.total).toFixed(2)}</td></tr>
        </tfoot>
      </table>
      <div class="footer">THANK YOU FOR SHOPPING AT TRONMATIX COMPUTER</div>
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
