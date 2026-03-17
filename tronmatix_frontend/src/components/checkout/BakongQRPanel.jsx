// src/components/orders/BakongQRPanel.jsx
import { useState, useEffect, useRef } from "react"
import { generatekhqr_api, checkpayment_api, confirmManual_api, pollPaymentStatus } from "../../lib/qrApi"
import { QRCodeSVG } from "qrcode.react"

export default function BakongQRPanel({ orderId, total, onPaid }) {
  const [qrData,         setQrData]         = useState(null)
  const [loading,        setLoading]        = useState(false)
  const [paymentStatus,  setPaymentStatus]  = useState("idle")   // idle | pending | paid | expired | manual
  const [error,          setError]          = useState(null)
  const [countdown,      setCountdown]      = useState(null)
  const [manualSent,     setManualSent]     = useState(false)

  const pollerRef    = useRef(null)   // pollPaymentStatus handle
  const countdownRef = useRef(null)   // setInterval for countdown timer

  // ── Auto-generate on mount ─────────────────────────────────────────────────
  useEffect(() => {
    if (orderId) generateQRCode()
    return () => stopAll()
  }, []) // eslint-disable-line react-hooks/exhaustive-deps

  const stopAll = () => {
    pollerRef.current?.stop()
    clearInterval(countdownRef.current)
  }

  // ── Generate QR ────────────────────────────────────────────────────────────
  const generateQRCode = async () => {
    if (!orderId) { setError("Order ID not found"); return }
    stopAll()
    setLoading(true); setError(null); setPaymentStatus("idle")
    setQrData(null); setCountdown(null); setManualSent(false)

    try {
      const response = await generatekhqr_api({ id: orderId })  // FIX: correct arg shape
      if (!response.success) throw new Error(response.message || "Failed to generate QR")

      const data = response.data
      setQrData(data)
      setPaymentStatus("pending")
      startCountdown(data.qr_expiration)

      // Only start polling if we have an md5 (dynamic QR); static fallback has no md5
      if (data.qr_md5) {
        startPoller()
      }
    } catch (err) {
      setError(err.response?.data?.message || err.message || "Failed to generate QR")
      setPaymentStatus("idle")
    } finally {
      setLoading(false)
    }
  }

  // ── Poll payment status ────────────────────────────────────────────────────
  const startPoller = () => {
    pollerRef.current = pollPaymentStatus(orderId, {
      intervalMs:  4000,
      maxAttempts: 45,   // ~3 min
      onSuccess: (data) => {
        stopAll()
        setPaymentStatus("paid")
        onPaid?.()
      },
      onExpired: () => {
        stopAll()
        setPaymentStatus("expired")
        setError("QR code has expired.")
      },
      onTimeout: () => {
        stopAll()
        setPaymentStatus("expired")
        setError("Payment window closed. Please generate a new QR code.")
      },
      onError: (err) => {
        stopAll()
        setError("Payment check failed: " + (err.message || "unknown error"))
        setPaymentStatus("idle")
      },
    })
  }

  // ── Countdown timer ────────────────────────────────────────────────────────
  const startCountdown = (qrExpiration) => {
    clearInterval(countdownRef.current)
    if (!qrExpiration) return

    const tick = () => {
      // qr_expiration is ISO string from backend (e.g. "2026-03-16T10:00:00+07:00")
      const remaining = new Date(qrExpiration).getTime() - Date.now()
      if (remaining <= 0) {
        clearInterval(countdownRef.current)
        setCountdown("00:00")
        // Poller's onExpired will handle state — only set here if poller not running
        setPaymentStatus(prev => prev === "pending" ? "expired" : prev)
        return
      }
      const m = Math.floor(remaining / 60000)
      const s = Math.floor((remaining % 60000) / 1000)
      setCountdown(`${m}:${s.toString().padStart(2, "0")}`)
    }

    tick()
    countdownRef.current = setInterval(tick, 1000)
  }

  // ── "I paid" manual fallback ───────────────────────────────────────────────
  const handleManualConfirm = async () => {
    try {
      await confirmManual_api(orderId)
      setManualSent(true)
      setPaymentStatus("manual")
      stopAll()
    } catch (err) {
      setError("Could not submit manual confirmation. Please contact support.")
    }
  }

  // ── Reset ──────────────────────────────────────────────────────────────────
  const reset = () => {
    stopAll()
    setQrData(null)
    setPaymentStatus("idle")
    setError(null)
    setCountdown(null)
    setManualSent(false)
  }

  // ── Render ─────────────────────────────────────────────────────────────────
  return (
    <div className="flex items-center justify-center p-4">
      <div className="relative w-full max-w-sm">

        {error && (
          <div className="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-3 text-center text-sm">
            ⚠️ {error}
          </div>
        )}

        {/* ── IDLE / LOADING ── */}
        {(paymentStatus === "idle" || paymentStatus === "expired") && (
          <div className="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div className="bg-red-600 py-8 text-center">
              <span className="text-white text-4xl font-black tracking-widest">KHQR</span>
            </div>
            <div className="p-10 text-center">
              {loading ? (
                <>
                  <div className="w-12 h-12 border-4 border-red-500 border-t-transparent rounded-full animate-spin mx-auto mb-4" />
                  <p className="text-gray-500 text-sm">Generating QR Code...</p>
                </>
              ) : (
                <button onClick={generateQRCode}
                  className="w-full py-4 bg-green-500 hover:bg-green-600 text-white font-bold text-lg rounded-xl transition-colors">
                  {paymentStatus === "expired" ? "🔄 Generate New QR" : "🔄 Generate QR"}
                </button>
              )}
            </div>
          </div>
        )}

        {/* ── PENDING ── */}
        {paymentStatus === "pending" && qrData && (
          <>
            <div className="bg-white rounded-2xl shadow-2xl overflow-hidden mb-3">
              <div className="relative bg-red-600 pt-5 pb-12 px-6 text-center">
                <button onClick={reset}
                  className="absolute top-3 right-3 w-8 h-8 bg-red-700 hover:bg-red-800 rounded-full flex items-center justify-center text-white font-bold transition-colors z-10">
                  ✕
                </button>
                <span className="text-white text-3xl font-black tracking-widest">KHQR</span>
                <div className="absolute bottom-0 left-0 right-0 h-8 bg-white rounded-t-3xl" />
              </div>
              <div className="px-6 pb-6">
                <div className="mb-1">
                  {/* FIX: backend returns merchant_name not marchant_name */}
                  <p className="text-gray-800 font-bold text-lg">{qrData.merchant_name || "Tronmatix"}</p>
                  <p className="text-gray-900 font-black text-3xl mt-0.5">${qrData.amount || total || "0.00"}</p>
                </div>
                <div className="border-t-2 border-dashed border-gray-200 my-4" />
                <div className="flex justify-center mb-3">
                  {qrData.qr_code ? (
                    <QRCodeSVG value={qrData.qr_code} size={240} level="H" bgColor="#ffffff" fgColor="#000000" />
                  ) : (
                    <div className="w-[240px] h-[240px] flex items-center justify-center bg-gray-50 rounded">
                      <p className="text-gray-400 text-sm">Loading QR...</p>
                    </div>
                  )}
                </div>
                {qrData.qr_md5 && (
                  <p className="text-center text-gray-400 text-xs">MD5: {qrData.qr_md5.slice(0, 16)}...</p>
                )}
              </div>
            </div>

            {/* Countdown + controls */}
            <div className="bg-white rounded-2xl shadow-xl px-5 py-4">
              <div className="flex items-center justify-between mb-3">
                <div className="flex items-center gap-2">
                  <span className="text-red-500 text-base">⏱</span>
                  <span className="text-red-500 font-bold text-sm">Time remaining:</span>
                </div>
                <span className="text-red-500 font-black text-2xl tabular-nums">{countdown || "--:--"}</span>
              </div>
              <div className="bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-3 mb-4 flex gap-2 items-start">
                <span className="text-yellow-500 text-sm mt-0.5">⚠️</span>
                <p className="text-gray-700 text-sm leading-snug">
                  Keep this page open until payment is confirmed. It detects automatically.
                </p>
              </div>
              {/* "I paid" manual fallback */}
              <button onClick={handleManualConfirm}
                className="w-full py-3 mb-2 border-2 border-blue-400 text-blue-600 font-bold text-sm rounded-xl hover:bg-blue-50 transition-colors">
                ✅ I already paid — notify admin
              </button>
              <button onClick={generateQRCode}
                className="w-full py-3 bg-green-500 hover:bg-green-600 text-white font-bold text-base rounded-xl transition-colors">
                🔄 Regenerate QR
              </button>
            </div>
          </>
        )}

        {/* ── MANUAL PENDING ── */}
        {paymentStatus === "manual" && (
          <div className="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div className="bg-yellow-500 py-6 text-center">
              <span className="text-white text-3xl font-black tracking-widest">KHQR</span>
            </div>
            <div className="p-8 text-center">
              <div className="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <span className="text-yellow-500 text-3xl">⏳</span>
              </div>
              <h2 className="text-xl font-black text-yellow-600 mb-1">Pending Verification</h2>
              <p className="text-gray-500 text-sm mb-5">
                Your payment claim has been sent to our admin for manual verification.
                We'll confirm your order shortly.
              </p>
              <div className="bg-yellow-50 rounded-xl p-4 mb-5 text-left">
                <div className="flex justify-between text-sm">
                  <span className="text-gray-500 font-medium">Order ID:</span>
                  <span className="font-bold text-gray-800">#{orderId}</span>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* ── PAID ── */}
        {paymentStatus === "paid" && (
          <div className="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div className="bg-red-600 py-6 text-center">
              <span className="text-white text-3xl font-black tracking-widest">KHQR</span>
            </div>
            <div className="p-8 text-center">
              <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <span className="text-green-500 text-3xl font-bold">✓</span>
              </div>
              <h2 className="text-xl font-black text-green-500 mb-1">Payment Successful!</h2>
              <p className="text-gray-500 text-sm mb-5">Your transaction has been completed successfully!</p>
              <div className="bg-green-50 rounded-xl p-4 text-left">
                <div className="flex justify-between text-sm mb-2">
                  <span className="text-gray-500 font-medium">Order ID:</span>
                  <span className="font-bold text-gray-800">#{orderId}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-gray-500 font-medium">Amount Paid:</span>
                  <span className="font-bold text-gray-800">${qrData?.amount ?? total} {qrData?.currency || "USD"}</span>
                </div>
              </div>
            </div>
          </div>
        )}

      </div>
    </div>
  )
}
