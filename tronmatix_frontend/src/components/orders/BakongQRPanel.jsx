import { useState, useEffect, useRef, useCallback } from "react";
import { useLang } from "../../context/LanguageContext";
import {
  generatekhqr_api,
  checkpayment_api,
  confirmManual_api,
} from "../../lib/qrApi";
import { QRCodeSVG } from "qrcode.react";

// ── QR display ────────────────────────────────────────────────────────────────
function QRDisplay({ qrData }) {
  const boxStyle = {
    width: "100%",
    maxWidth: 440,
    aspectRatio: "1 / 1",
    margin: "0 auto",
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    background: "#fff",
  };

  if (qrData?.qr_image) {
    const src = qrData.qr_image.startsWith("data:")
      ? qrData.qr_image
      : `data:image/png;base64,${qrData.qr_image}`;
    return (
      <div style={boxStyle}>
        <img
          src={src}
          alt="KHQR Payment Code"
          style={{ width: "100%", height: "100%", objectFit: "contain" }}
        />
      </div>
    );
  }
  // Fallback: raw qr_code string → vector SVG (no image from PayWay)
  if (qrData?.qr_code) {
    return (
      <div style={boxStyle}>
        <QRCodeSVG
          value={qrData.qr_code}
          size={360}
          level="H"
          bgColor="#ffffff"
          fgColor="#1a1a1a"
          style={{ display: "block" }}
        />
      </div>
    );
  }
  return (
    <div
      style={{
        height: 360,
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
      }}
    >
      <p style={{ fontSize: 12, color: "#9ca3af" }}>Loading QR...</p>
    </div>
  );
}

// ── Main component ─────────────────────────────────────────────────────────────
export default function BakongQRPanel({
  orderId,
  total,
  subtotal,
  discountAmount,
  discountCode,
  items,
  onPaid,
  onSuccessAlert,
}) {
  const { t, isKhmer } = useLang();

  const [qrData, setQrData] = useState(null);
  const [loading, setLoading] = useState(false);
  const [paymentStatus, setPaymentStatus] = useState("idle");
  const [error, setError] = useState(null);
  const [countdown, setCountdown] = useState(null);

  const pollerRef = useRef(null);
  const countdownRef = useRef(null);
  const paidRef = useRef(false); // prevent double onPaid calls

  // Auto-generate on mount
  useEffect(() => {
    if (orderId) generateQRCode();
    return () => stopAll();
  }, [orderId]); // eslint-disable-line

  // ── Interval helpers ───────────────────────────────────────────────────────
  const stopPoller = () => {
    if (pollerRef.current) {
      clearInterval(pollerRef.current);
      pollerRef.current = null;
    }
  };
  const stopCountdown = () => {
    if (countdownRef.current) {
      clearInterval(countdownRef.current);
      countdownRef.current = null;
    }
  };
  const stopAll = () => {
    stopPoller();
    stopCountdown();
  };

  // ── Generate QR ────────────────────────────────────────────────────────────
  const generateQRCode = async () => {
    if (!orderId) {
      setError("Order ID not found");
      return;
    }
    stopAll();
    paidRef.current = false;
    setLoading(true);
    setError(null);
    setPaymentStatus("idle");
    setQrData(null);
    setCountdown(null);

    try {
      const response = await generatekhqr_api({ id: orderId });
      if (!response.success)
        throw new Error(response.message || "Failed to generate QR");

      const data = response.data;
      setQrData(data);
      setPaymentStatus("pending");
      startCountdown(data.qr_expiration);
      startPoller();
    } catch (err) {
      setError(
        err.response?.data?.message || err.message || "Failed to generate QR",
      );
      setPaymentStatus("idle");
    } finally {
      setLoading(false);
    }
  };

  // ── Payment poller ─────────────────────────────────────────────────────────
  const startPoller = useCallback(() => {
    stopPoller();
    let attempts = 0;
    const MAX = 60; // 4 min at 4s intervals

    const tick = async () => {
      if (paidRef.current) return;
      attempts++;

      try {
        const data = await checkpayment_api(orderId);

        if (data?.success && data?.status === "paid") {
          stopAll();
          if (!paidRef.current) {
            paidRef.current = true;
            setPaymentStatus("paid");
            onPaid?.();
            onSuccessAlert?.();
          }
          return;
        }

        if (data?.status === "expired") {
          stopAll();
          setPaymentStatus("expired");
          setError("QR code has expired. Please generate a new one.");
          return;
        }

        if (attempts >= MAX) {
          stopAll();
          setPaymentStatus("expired");
          setError("Payment window closed. Please generate a new QR code.");
        }
      } catch (err) {
        const status = err?.response?.status;
        const errBody = err?.response?.data;

        if (status === 400) {
          const isExpired =
            errBody?.status === "expired" ||
            errBody?.message?.toLowerCase().includes("expired") ||
            errBody?.error?.toLowerCase().includes("expired");
          if (isExpired) {
            stopAll();
            setPaymentStatus("expired");
            setError("QR expired.");
            return;
          }
          if (attempts >= MAX) {
            stopAll();
            setPaymentStatus("expired");
          }
          return;
        }
        // 404 = still pending — keep polling
        if (attempts >= MAX) {
          stopAll();
          setPaymentStatus("expired");
        }
      }
    };

    // First tick after 3s — give backend time to register QR with PayWay
    setTimeout(tick, 3000);
    pollerRef.current = setInterval(tick, 4000);
  }, [orderId, onPaid]);

  // ── Countdown ──────────────────────────────────────────────────────────────
  const parseExpirationMs = (exp) => {
    if (!exp) return null;
    const hasTimezone = /Z|[+-]\d{2}:?\d{2}$/.test(exp);
    const normalized = hasTimezone ? exp : exp.replace(" ", "T") + "Z";
    return new Date(normalized).getTime();
  };

  const startCountdown = (qrExpiration) => {
    clearInterval(countdownRef.current);
    if (!qrExpiration) return;
    const expiryMs = parseExpirationMs(qrExpiration);
    if (!expiryMs || isNaN(expiryMs)) return;

    const tick = () => {
      const remaining = expiryMs - Date.now();
      if (remaining <= 0) {
        stopAll();
        setCountdown("0:00");
        setPaymentStatus((prev) => (prev === "pending" ? "expired" : prev));
        setError("QR code has expired. Please generate a new one.");
        return;
      }
      const m = Math.floor(remaining / 60000);
      const s = Math.floor((remaining % 60000) / 1000);
      setCountdown(`${m}:${s.toString().padStart(2, "0")}`);
    };
    tick();
    countdownRef.current = setInterval(tick, 1000);
  };

  // ── Manual confirm ─────────────────────────────────────────────────────────
  const handleManualConfirm = async () => {
    try {
      await confirmManual_api(orderId);
      setPaymentStatus("manual");
      stopAll();
    } catch {
      setError("Could not submit manual confirmation. Please contact support.");
    }
  };

  const reset = () => {
    stopAll();
    setQrData(null);
    setPaymentStatus("idle");
    setError(null);
    setCountdown(null);
    paidRef.current = false;
  };

  // ══════════════════════════════════════════════════════════════════════════
  // RENDER
  // ══════════════════════════════════════════════════════════════════════════
  return (
    <div
      style={{
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        padding: 16,
      }}
    >
      <div style={{ width: "auto", maxWidth: 1080, position: "relative" }}>
        {/* ── Error banner ─────────────────────────────────────────────────── */}
        {error && (
          <div
            style={{
              background: "#fef2f2",
              border: "1px solid #fecaca",
              color: "#b91c1c",
              borderRadius: 12,
              padding: "10px 16px",
              marginBottom: 12,
              fontSize: 13,
              textAlign: "center",
              fontWeight: 500,
            }}
          >
            ⚠️ {error}
          </div>
        )}

        {/* ══════════════════════════════════════════════════════════════════ */}
        {/* IDLE / EXPIRED STATE                                              */}
        {/* ══════════════════════════════════════════════════════════════════ */}
        {(paymentStatus === "idle" || paymentStatus === "expired") && (
          <div
            style={{
              background: "#fff",
              borderRadius: 20,
              overflow: "hidden",
              boxShadow: "0 8px 32px rgba(0,0,0,0.12)",
            }}
          >
            {/* Red ABA/KHQR header */}
            <div
              style={{
                background: "#C8102E",
                padding: "24px 20px",
                textAlign: "center",
              }}
            >
              <div
                style={{
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                  gap: 10,
                }}
              >
                <div
                  style={{
                    background: "#fff",
                    borderRadius: 6,
                    padding: "2px 8px",
                  }}
                >
                  <span
                    style={{
                      color: "#003082",
                      fontSize: 14,
                      fontWeight: 900,
                      letterSpacing: 1,
                    }}
                  >
                    ABA
                  </span>
                </div>
                <span
                  style={{
                    color: "#fff",
                    fontSize: 22,
                    fontWeight: 900,
                    letterSpacing: 4,
                  }}
                >
                  KHQR
                </span>
              </div>
              <p
                style={{
                  color: "rgba(255,255,255,0.7)",
                  fontSize: 11,
                  marginTop: 4,
                  letterSpacing: 0.5,
                }}
              >
                National QR Payment Standard
              </p>
            </div>

            {/* Body */}
            <div style={{ padding: "32px 24px", textAlign: "center" }}>
              {loading ? (
                <>
                  <div
                    style={{
                      width: 48,
                      height: 48,
                      borderRadius: "50%",
                      border: "4px solid #C8102E",
                      borderTopColor: "transparent",
                      animation: "spin 0.8s linear infinite",
                      margin: "0 auto 16px",
                    }}
                  />
                  <p style={{ color: "#6b7280", fontSize: 14 }}>
                    {isKhmer ? t("qr.generating") : "Generating QR Code..."}
                  </p>
                  <p style={{ color: "#9ca3af", fontSize: 12, marginTop: 4 }}>
                    Connecting to ABA PayWay...
                  </p>
                </>
              ) : (
                <>
                  <div
                    style={{
                      width: 64,
                      height: 64,
                      borderRadius: "50%",
                      background: "#fff0f0",
                      display: "flex",
                      alignItems: "center",
                      justifyContent: "center",
                      margin: "0 auto 16px",
                      fontSize: 28,
                    }}
                  >
                    📱
                  </div>
                  <p
                    style={{
                      color: "#374151",
                      fontWeight: 700,
                      fontSize: 15,
                      marginBottom: 6,
                    }}
                  >
                    {paymentStatus === "expired"
                      ? "QR Code Expired"
                      : "Pay with KHQR"}
                  </p>
                  <p
                    style={{
                      color: "#9ca3af",
                      fontSize: 12,
                      marginBottom: 24,
                      lineHeight: 1.5,
                    }}
                  >
                    Scan with ABA Mobile or any KHQR‑supported banking app
                  </p>
                  <button
                    onClick={generateQRCode}
                    style={{
                      width: "100%",
                      padding: "14px 0",
                      background: "#C8102E",
                      color: "#fff",
                      fontWeight: 700,
                      fontSize: 14,
                      border: "none",
                      borderRadius: 12,
                      cursor: "pointer",
                      transition: "background 0.2s",
                    }}
                    onMouseEnter={(e) =>
                      (e.target.style.background = "#a50e26")
                    }
                    onMouseLeave={(e) =>
                      (e.target.style.background = "#C8102E")
                    }
                  >
                    {paymentStatus === "expired"
                      ? `🔄 ${isKhmer ? t("qr.generateNew") : "Generate New QR"}`
                      : `🔄 ${isKhmer ? t("qr.generate") : "Generate QR Code"}`}
                  </button>
                </>
              )}
            </div>
          </div>
        )}

        {/* ══════════════════════════════════════════════════════════════════ */}
        {/* PENDING STATE — summary card (left) + unboxed QR (right)           */}
        {/* ══════════════════════════════════════════════════════════════════ */}
        {paymentStatus === "pending" && qrData && (
          <div>
            <div className="khqr-pending-grid">
              {/* Left: unboxed QR */}
              <div style={{ display: "flex", flexDirection: "column", alignItems: "center", justifyContent: "center", padding: "8px 0" }}>
                <QRDisplay qrData={qrData} />
                <div style={{ marginTop: 16, fontSize: 13, color: "#6b7280" }}>
                  Time:{" "}
                  <span style={{ fontWeight: 700, color: "#C8102E", fontFamily: "monospace" }}>
                    {countdown || "--:--"}
                  </span>
                </div>
              </div>

              {/* Right: summary card */}
              <div
                style={{
                  background: "#fff",
                  border: "1px solid #e5e7eb",
                  borderRadius: 16,
                  boxShadow: "0 1px 3px rgba(0,0,0,0.08)",
                  padding: 20,
                }}
              >
                <div style={{ display: "flex", alignItems: "flex-start", justifyContent: "space-between", marginBottom: 16 }}>
                  <div>
                    <div style={{ fontWeight: 800, fontSize: 15, color: "#111827" }}>
                      Payment summary
                    </div>
                    <div style={{ fontSize: 12, color: "#9ca3af", marginTop: 2 }}>
                      Order #{orderId}
                    </div>
                  </div>
                  <span style={{ background: "#fef9c3", color: "#a16207", fontSize: 11, fontWeight: 700, borderRadius: 999, padding: "3px 10px" }}>
                    Pending
                  </span>
                </div>

                {qrData?.merchant_name && (
                  <>
                    <div style={{ fontSize: 10, fontWeight: 700, color: "#9ca3af", letterSpacing: 0.5, marginBottom: 4 }}>
                      MERCHANT
                    </div>
                    <div
                      style={{
                        fontSize: 16,
                        fontWeight: 800,
                        color: "#F97316",
                        marginBottom: 16,
                        letterSpacing: "0.5px",
                        textShadow: "0 0 8px rgba(249,115,22,0.5), 0 0 20px rgba(249,115,22,0.3)",
                      }}
                    >
                      {qrData.merchant_name}
                    </div>
                  </>
                )}

                {Array.isArray(items) && items.length > 0 && (
                  <div style={{ marginBottom: 16 }}>
                    <div style={{ fontSize: 10, fontWeight: 700, color: "#9ca3af", letterSpacing: 0.5, marginBottom: 6 }}>
                      ITEMS
                    </div>
                    {items.map((item, idx) => {
                      const itemName = item.name || item.product?.name || item.product_name || "Item";
                      const itemQty = Number(item.qty || item.quantity || 1);
                      const itemPrice = Number(item.price ?? item.unit_price ?? 0);
                      return (
                        <div key={item.id ?? idx} style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start", gap: 8, fontSize: 12, color: "#374151", marginBottom: 5 }}>
                          <span style={{ flex: "1 1 auto", lineHeight: 1.35 }}>
                            {itemName}
                            <span style={{ color: "#9ca3af" }}> × {itemQty}</span>
                          </span>
                          <span style={{ flex: "0 0 auto", fontWeight: 600, color: "#111827" }}>
                            ${(itemPrice * itemQty).toFixed(2)}
                          </span>
                        </div>
                      );
                    })}
                  </div>
                )}

                <div style={{ borderTop: "1px solid #f3f4f6", paddingTop: 12 }}>
                  {Number(discountAmount) > 0 && (
                    <>
                      <div style={{ display: "flex", justifyContent: "space-between", fontSize: 13, color: "#6b7280", marginBottom: 6 }}>
                        <span>Subtotal</span>
                        <span>${Number(subtotal ?? total).toFixed(2)}</span>
                      </div>
                      <div style={{ display: "flex", justifyContent: "space-between", fontSize: 13, color: "#16a34a", marginBottom: 6 }}>
                        <span>Discount{discountCode ? ` (${discountCode})` : ""}</span>
                        <span>−${Number(discountAmount).toFixed(2)}</span>
                      </div>
                    </>
                  )}
                  <div style={{ display: "flex", justifyContent: "space-between", alignItems: "baseline", paddingTop: 8, borderTop: "1px solid #f3f4f6" }}>
                    <span style={{ fontSize: 14, fontWeight: 700, color: "#111827" }}>
                      {isKhmer ? t("qr.totalToPay") : "Total"}
                    </span>
                    <span style={{ fontSize: 20, fontWeight: 800, color: "#111827" }}>
                      ${Number(total ?? qrData?.amount ?? 0).toFixed(2)}{" "}
                      <span style={{ fontSize: 12, color: "#9ca3af", fontWeight: 500 }}>USD</span>
                    </span>
                  </div>
                </div>

                <div style={{ marginTop: 16, paddingTop: 12, borderTop: "1px solid #f3f4f6" }}>
                  {["Open ABA Mobile or any KHQR app", "Scan the QR code on the left", "Enter amount & confirm payment"].map((step, i) => (
                    <div key={i} style={{ display: "flex", gap: 8, fontSize: 12, color: "#6b7280", marginBottom: 6 }}>
                      <span style={{ fontWeight: 700, color: "#9ca3af" }}>{i + 1}.</span>
                      <span>{step}</span>
                    </div>
                  ))}
                </div>

                <div style={{ marginTop: 12, display: "flex", alignItems: "center", gap: 6, fontSize: 12, color: "#16a34a", fontWeight: 600 }}>
                  <span style={{ width: 6, height: 6, borderRadius: "50%", background: "#22c55e", animation: "pulse 1.5s ease-in-out infinite" }} />
                  Auto-checking payment...
                </div>
              </div>
            </div>

            {/* Footer actions */}
            <div style={{ marginTop: 24, paddingTop: 20, borderTop: "1px solid #f3f4f6", display: "flex", flexDirection: "column", gap: 10 }} className="sm:flex-row">
              {qrData.abapay_deeplink && (
                <a
                  href={qrData.abapay_deeplink}
                  style={{ flex: "1 1 auto", padding: 12, background: "#003082", color: "#fff", fontWeight: 700, fontSize: 13, borderRadius: 12, textDecoration: "none", textAlign: "center" }}
                >
                  📱 Open in ABA Mobile
                </a>
              )}
              <button
                onClick={handleManualConfirm}
                style={{ flex: "1 1 auto", padding: 12, background: "#fff", color: "#374151", fontWeight: 600, fontSize: 13, border: "1.5px solid #e5e7eb", borderRadius: 12, cursor: "pointer" }}
              >
                I already paid – notify admin
              </button>
            </div>
          </div>
        )}

        {/* ══════════════════════════════════════════════════════════════════ */}
        {/* MANUAL PENDING STATE                                              */}
        {/* ══════════════════════════════════════════════════════════════════ */}
        {paymentStatus === "manual" && (
          <div
            style={{
              background: "#fff",
              borderRadius: 20,
              overflow: "hidden",
              boxShadow: "0 8px 32px rgba(0,0,0,0.12)",
            }}
          >
            <div
              style={{
                background: "#f59e0b",
                padding: "24px 20px",
                textAlign: "center",
              }}
            >
              <div
                style={{
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                  gap: 8,
                }}
              >
                <div
                  style={{
                    background: "#fff",
                    borderRadius: 5,
                    padding: "2px 7px",
                  }}
                >
                  <span
                    style={{
                      color: "#003082",
                      fontSize: 13,
                      fontWeight: 900,
                      letterSpacing: 1,
                    }}
                  >
                    ABA
                  </span>
                </div>
                <span
                  style={{
                    color: "#fff",
                    fontSize: 18,
                    fontWeight: 900,
                    letterSpacing: 3,
                  }}
                >
                  KHQR
                </span>
              </div>
            </div>
            <div style={{ padding: "32px 24px", textAlign: "center" }}>
              <div
                style={{
                  width: 64,
                  height: 64,
                  borderRadius: "50%",
                  background: "#fffbeb",
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                  margin: "0 auto 16px",
                  fontSize: 28,
                }}
              >
                ⏳
              </div>
              <h2
                style={{
                  fontSize: 18,
                  fontWeight: 800,
                  color: "#d97706",
                  margin: "0 0 8px",
                }}
              >
                {isKhmer ? t("qr.pendingVerification") : "Pending Verification"}
              </h2>
              <p
                style={{
                  color: "#6b7280",
                  fontSize: 13,
                  marginBottom: 20,
                  lineHeight: 1.5,
                }}
              >
                {isKhmer
                  ? t("qr.paymentClaim")
                  : "Payment claim sent to admin for manual verification."}
              </p>
              <div
                style={{
                  background: "#fffbeb",
                  border: "1px solid #fde68a",
                  borderRadius: 12,
                  padding: "12px 16px",
                  textAlign: "left",
                }}
              >
                <div
                  style={{
                    display: "flex",
                    justifyContent: "space-between",
                    fontSize: 13,
                  }}
                >
                  <span style={{ color: "#6b7280" }}>Order ID</span>
                  <span style={{ fontWeight: 700, color: "#111827" }}>
                    #{orderId}
                  </span>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* ══════════════════════════════════════════════════════════════════ */}
        {/* PAID STATE — celebratory confirmation animation                   */}
        {/* ══════════════════════════════════════════════════════════════════ */}
        {paymentStatus === "paid" && (
          <div
            style={{
              background: "#fff",
              borderRadius: 20,
              overflow: "hidden",
              boxShadow: "0 8px 32px rgba(0,0,0,0.12)",
            }}
          >
            {/* Green gradient header with animated checkmark + confetti */}
            <div
              style={{
                background: "linear-gradient(135deg, #16a34a, #22c55e)",
                padding: "32px 20px",
                textAlign: "center",
                position: "relative",
                overflow: "hidden",
              }}
            >
              <div className="confetti-circle c1" />
              <div className="confetti-circle c2" />
              <div className="confetti-circle c3" />
              <div className="confetti-circle c4" />

              <div
                style={{
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                  gap: 8,
                  marginBottom: 16,
                }}
              >
                <div
                  style={{
                    background: "#fff",
                    borderRadius: 5,
                    padding: "2px 7px",
                  }}
                >
                  <span
                    style={{
                      color: "#003082",
                      fontSize: 13,
                      fontWeight: 900,
                      letterSpacing: 1,
                    }}
                  >
                    ABA
                  </span>
                </div>
                <span
                  style={{
                    color: "#fff",
                    fontSize: 18,
                    fontWeight: 900,
                    letterSpacing: 3,
                  }}
                >
                  KHQR
                </span>
              </div>

              {/* Animated checkmark */}
              <div
                className="checkmark-circle"
                style={{
                  width: 80,
                  height: 80,
                  borderRadius: "50%",
                  background: "rgba(255,255,255,0.2)",
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                  margin: "0 auto",
                  animation: "checkBounce 0.7s cubic-bezier(0.34,1.56,0.64,1) 0.2s both",
                }}
              >
                <span
                  className="checkmark-icon"
                  style={{
                    color: "#fff",
                    fontSize: 42,
                    fontWeight: 900,
                    animation: "checkDraw 0.4s ease-out 0.6s both",
                  }}
                >
                  ✓
                </span>
              </div>
            </div>

            <div style={{ padding: "32px 24px", textAlign: "center" }}>
              <h2
                style={{
                  fontSize: 22,
                  fontWeight: 900,
                  color: "#16a34a",
                  margin: "0 0 6px",
                  animation: "fadeInUp 0.5s ease-out 0.3s both",
                }}
              >
                {isKhmer ? t("qr.paymentSuccess") : "Payment Successful! 🎉"}
              </h2>
              <p
                style={{
                  color: "#6b7280",
                  fontSize: 14,
                  marginBottom: 24,
                  animation: "fadeInUp 0.5s ease-out 0.4s both",
                }}
              >
                {isKhmer
                  ? t("qr.transactionComplete")
                  : "Your payment was confirmed automatically!"}
              </p>
              <div
                style={{
                  background: "#f0fdf4",
                  border: "1px solid #bbf7d0",
                  borderRadius: 14,
                  padding: "16px 20px",
                  textAlign: "left",
                  animation: "fadeInUp 0.5s ease-out 0.5s both",
                }}
              >
                <div
                  style={{
                    display: "flex",
                    justifyContent: "space-between",
                    fontSize: 14,
                    marginBottom: 10,
                  }}
                >
                  <span style={{ color: "#6b7280" }}>Order ID</span>
                  <span style={{ fontWeight: 700, color: "#111827" }}>
                    #{orderId}
                  </span>
                </div>
                <div
                  style={{
                    display: "flex",
                    justifyContent: "space-between",
                    fontSize: 14,
                    paddingTop: 10,
                    borderTop: "1px solid #bbf7d0",
                  }}
                >
                  <span style={{ color: "#6b7280" }}>Amount Paid</span>
                  <span style={{ fontWeight: 800, color: "#16a34a", fontSize: 16 }}>
                    ${Number(qrData?.amount ?? total ?? 0).toFixed(2)}{" "}
                    {qrData?.currency || "USD"}
                  </span>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* ── Keyframe animations ───────────────────────────────────────────── */}
        <style>{`
          /* KHQR pending-state grid — deterministic layout, no Tailwind
             arbitrary-value classes (avoids this project's JIT-purge issue).
             Stacks by default; 2 equal columns at md+ (both tracks minmax(0,1fr)
             so they shrink to the actual modal width instead of overflowing). */
          .khqr-pending-grid {
            display: flex;
            flex-direction: column;
            gap: 24px;
          }
          @media (min-width: 768px) {
            .khqr-pending-grid {
              display: grid;
              grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
              align-items: start;
              gap: 32px;
            }
          }
          @keyframes spin {
            to { transform: rotate(360deg); }
          }
          @keyframes pulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.35; }
          }
          @keyframes popIn {
            from { opacity: 0; transform: scale(0.75); }
            to   { opacity: 1; transform: scale(1); }
          }
          @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
          }
          @keyframes checkBounce {
            0%   { transform: scale(0); opacity: 0; }
            60%  { transform: scale(1.15); }
            100% { transform: scale(1); opacity: 1; }
          }
          @keyframes checkDraw {
            from { opacity: 0; transform: scale(0.5); }
            to   { opacity: 1; transform: scale(1); }
          }
          @keyframes confettiFloat {
            0%   { transform: translateY(0) rotate(0deg); opacity: 0.8; }
            100% { transform: translateY(-60px) rotate(90deg); opacity: 0; }
          }
          .confetti-circle {
            position: absolute;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            opacity: 0;
            animation: confettiFloat 1.2s ease-out 0.3s forwards;
          }
          .c1 { background: #fbbf24; top: 10%; left: 15%; animation-delay: 0.3s; }
          .c2 { background: #60a5fa; top: 20%; right: 20%; animation-delay: 0.5s; width: 8px; height: 8px; }
          .c3 { background: #f472b6; top: 15%; left: 50%; animation-delay: 0.7s; width: 10px; height: 10px; }
          .c4 { background: #34d399; top: 8%; right: 35%; animation-delay: 0.9s; }
        `}</style>
      </div>
    </div>
  );
}
