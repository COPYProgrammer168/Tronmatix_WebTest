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
  if (qrData?.qr_image) {
    const src = qrData.qr_image.startsWith("data:")
      ? qrData.qr_image
      : `data:image/png;base64,${qrData.qr_image}`;
    return (
      <div style={{ lineHeight: 0 }}>
        <img
          src={src}
          alt="KHQR Payment Code"
          style={{ display: "block", width: "100%", height: "auto" }}
        />
      </div>
    );
  }
  // Fallback: raw qr_code string → vector SVG (no image from PayWay)
  if (qrData?.qr_code) {
    return (
      <div style={{ padding: 12 }}>
        <QRCodeSVG
          value={qrData.qr_code}
          size={260}
          level="H"
          bgColor="#ffffff"
          fgColor="#1a1a1a"
          style={{ display: "block", width: "auto", height: "auto" }}
        />
      </div>
    );
  }
  return (
    <div
      style={{
        height: 180,
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
export default function BakongQRPanel({ orderId, total, onPaid }) {
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
      <div style={{ width: "auto", maxWidth: 360, position: "relative" }}>
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
        {/* PENDING STATE — Professional KHQR template                        */}
        {/* ══════════════════════════════════════════════════════════════════ */}
        {paymentStatus === "pending" && qrData && (
          <div
            style={{
              background: "#fff",
              borderRadius: 24,
              overflow: "hidden",
              boxShadow:
                "0 20px 40px rgba(0,0,0,0.1), 0 0 0 1px rgba(0,0,0,0.05)",
            }}
          >
            {/* Header */}
            <div
              style={{
                display: "flex",
                alignItems: "center",
                justifyContent: "space-between",
                padding: "20px 24px 12px",
              }}
            >
              <div style={{ fontWeight: 800, color: "#111827", fontSize: 16 }}>
                Scan to Pay
              </div>
              <div style={{ display: "flex", alignItems: "center", gap: 6 }}>
                <span style={{ fontSize: 14 }}>⏱</span>
                <span
                  style={{
                    fontWeight: 900,
                    fontSize: 16,
                    fontFamily: "monospace",
                    color: "#C8102E",
                  }}
                >
                  {countdown || "--:--"}
                </span>
              </div>
            </div>

            {/* QR Image - The Core Asset */}
            <div style={{ padding: "0 20px 20px" }}>
              <QRDisplay qrData={qrData} />
            </div>

            {/* Footer */}
            <div
              style={{
                background: "#f9fafb",
                padding: "20px 24px",
                textAlign: "center",
                borderTop: "1px solid #f3f4f6",
              }}
            >
              <div style={{ marginBottom: 16 }}>
                <div
                  style={{ fontSize: 13, color: "#4b5563", marginBottom: 4 }}
                >
                  Scan with ABA Mobile or any KHQR app
                </div>
                <div
                  style={{
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center",
                    gap: 6,
                  }}
                >
                  <div
                    style={{
                      width: 6,
                      height: 6,
                      borderRadius: "50%",
                      background: "#22c55e",
                      animation: "pulse 1.5s ease-in-out infinite",
                    }}
                  />
                  <span style={{ fontSize: 12, color: "#6b7280" }}>
                    Auto-checking payment...
                  </span>
                </div>
              </div>

              {qrData.abapay_deeplink && (
                <a
                  href={qrData.abapay_deeplink}
                  style={{
                    display: "block",
                    width: "100%",
                    padding: "12px",
                    background: "#003082",
                    color: "#fff",
                    fontWeight: 700,
                    fontSize: 13,
                    borderRadius: 12,
                    textDecoration: "none",
                    marginBottom: 10,
                  }}
                >
                  📱 Open in ABA Mobile
                </a>
              )}

              <button
                onClick={handleManualConfirm}
                style={{
                  width: "100%",
                  padding: "12px",
                  background: "#f9fafb",
                  color: "#374151",
                  fontWeight: 600,
                  fontSize: 13,
                  border: "1.5px solid #e5e7eb",
                  borderRadius: 12,
                  cursor: "pointer",
                }}
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
        {/* PAID STATE                                                        */}
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
            <div
              style={{
                background: "#16a34a",
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
                  width: 68,
                  height: 68,
                  borderRadius: "50%",
                  background: "#f0fdf4",
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                  margin: "0 auto 16px",
                  fontSize: 32,
                  animation: "popIn 0.5s cubic-bezier(0.34,1.56,0.64,1)",
                }}
              >
                ✓
              </div>
              <h2
                style={{
                  fontSize: 18,
                  fontWeight: 800,
                  color: "#16a34a",
                  margin: "0 0 8px",
                }}
              >
                {isKhmer ? t("qr.paymentSuccess") : "Payment Successful!"}
              </h2>
              <p style={{ color: "#6b7280", fontSize: 13, marginBottom: 20 }}>
                {isKhmer
                  ? t("qr.transactionComplete")
                  : "Transaction completed successfully!"}
              </p>
              <div
                style={{
                  background: "#f0fdf4",
                  border: "1px solid #bbf7d0",
                  borderRadius: 12,
                  padding: "14px 16px",
                  textAlign: "left",
                }}
              >
                <div
                  style={{
                    display: "flex",
                    justifyContent: "space-between",
                    fontSize: 13,
                    marginBottom: 8,
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
                    fontSize: 13,
                  }}
                >
                  <span style={{ color: "#6b7280" }}>Amount Paid</span>
                  <span style={{ fontWeight: 700, color: "#16a34a" }}>
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
        `}</style>
      </div>
    </div>
  );
}
