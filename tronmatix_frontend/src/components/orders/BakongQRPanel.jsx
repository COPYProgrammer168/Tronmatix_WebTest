// src/components/orders/BakongQRPanel.jsx
import { useState, useEffect, useRef, useCallback } from "react"
import { generatekhqr_api, checkpayment_api, confirmManual_api } from "../../lib/qrApi"
import { QRCodeSVG } from "qrcode.react"

export default function BakongQRPanel({ orderId, total, onPaid }) {
  const [qrData,        setQrData]        = useState(null)
  const [loading,       setLoading]       = useState(false)
  const [paymentStatus, setPaymentStatus] = useState("idle")
  const [error,         setError]         = useState(null)
  const [countdown,     setCountdown]     = useState(null)

  const pollerRef    = useRef(null)
  const countdownRef = useRef(null)
  const paidRef      = useRef(false)   // FIX: prevent double onPaid calls

  useEffect(() => {
    if (orderId) generateQRCode()
    return () => stopAll()
  }, [orderId]) // eslint-disable-line

  const stopPoller = () => {
    if (pollerRef.current) { clearInterval(pollerRef.current); pollerRef.current = null }
  }

  const stopCountdown = () => {
    if (countdownRef.current) { clearInterval(countdownRef.current); countdownRef.current = null }
  }

  const stopAll = () => { stopPoller(); stopCountdown() }

  const generateQRCode = async () => {
    if (!orderId) { setError("Order ID not found"); return }
    stopAll()
    paidRef.current = false
    setLoading(true); setError(null); setPaymentStatus("idle")
    setQrData(null); setCountdown(null)

    try {
      const response = await generatekhqr_api({ id: orderId })
      if (!response.success) throw new Error(response.message || "Failed to generate QR")

      const data = response.data
      setQrData(data)
      setPaymentStatus("pending")
      startCountdown(data.qr_expiration)

      // FIX: always start polling — check immediately then every 4s
      startPoller()
    } catch (err) {
      setError(err.response?.data?.message || err.message || "Failed to generate QR")
      setPaymentStatus("idle")
    } finally {
      setLoading(false)
    }
  }

  // FIX: Use setInterval directly instead of qrApi pollPaymentStatus
  // This gives us full control and avoids the wrapper's issues
  const startPoller = useCallback(() => {
    stopPoller()   // FIX: only clear poller — don't kill the countdown interval
    let attempts = 0
    const MAX = 60  // 4min at 4s intervals

    const tick = async () => {
      if (paidRef.current) return
      attempts++

      try {
        const data = await checkpayment_api(orderId)

        if (data?.success && data?.status === 'paid') {
          stopAll()
          if (!paidRef.current) {
            paidRef.current = true
            setPaymentStatus("paid")
            onPaid?.()
          }
          return
        }

        if (data?.status === 'expired') {
          stopAll()
          setPaymentStatus("expired")
          setError("QR code has expired. Please generate a new one.")
          return
        }

        if (attempts >= MAX) {
          stopAll()
          setPaymentStatus("expired")
          setError("Payment window closed. Please generate a new QR code.")
        }
      } catch (err) {
        const status   = err?.response?.status
        const errBody  = err?.response?.data

        if (status === 400) {
          // Only treat 400 as expired if the backend explicitly says so.
          // A 400 on the first poll often means "QR not registered yet" —
          // NOT that it's expired. Keep polling until MAX attempts.
          const isExpired =
            errBody?.status === 'expired' ||
            errBody?.message?.toLowerCase().includes('expired') ||
            errBody?.error?.toLowerCase().includes('expired')

          if (isExpired) {
            stopAll(); setPaymentStatus("expired"); setError("QR expired.")
            return
          }
          // Not expired — just keep polling (backend not ready yet)
          if (attempts >= MAX) { stopAll(); setPaymentStatus("expired") }
          return
        }

        // 404 = still pending, keep polling
        // network errors: stop after max attempts
        if (attempts >= MAX) { stopAll(); setPaymentStatus("expired") }
      }
    }

    // Delay first check — give backend time to register the QR
    // Render cold starts + Bakong API registration can take 3-5s
    setTimeout(tick, 3000)
    pollerRef.current = setInterval(tick, 4000)
  }, [orderId, onPaid])

  // Normalize expiration to UTC ms.
  // Django returns datetimes without a timezone suffix (e.g. "2025-03-20T08:30:00").
  // JS new Date() treats tz-naive strings as LOCAL time, not UTC — so in UTC+7
  // the value is parsed 7 hours too early and the QR expires instantly.
  // Appending "Z" forces correct UTC interpretation.
  const parseExpirationMs = (exp) => {
    if (!exp) return null
    const hasTimezone = /Z|[+-]\d{2}:?\d{2}$/.test(exp)
    const normalized = hasTimezone ? exp : exp.replace(" ", "T") + "Z"
    return new Date(normalized).getTime()
  }

  const startCountdown = (qrExpiration) => {
    clearInterval(countdownRef.current)
    if (!qrExpiration) return

    const expiryMs = parseExpirationMs(qrExpiration)
    if (!expiryMs || isNaN(expiryMs)) return

    const tick = () => {
      const remaining = expiryMs - Date.now()
      if (remaining <= 0) {
        stopAll()   // FIX: stop both poller and countdown on expiry
        setCountdown("0:00")
        setPaymentStatus(prev => prev === "pending" ? "expired" : prev)
        setError("QR code has expired. Please generate a new one.")
        return
      }
      const m = Math.floor(remaining / 60000)
      const s = Math.floor((remaining % 60000) / 1000)
      setCountdown(`${m}:${s.toString().padStart(2, "0")}`)
    }
    tick()
    countdownRef.current = setInterval(tick, 1000)
  }

  const handleManualConfirm = async () => {
    try {
      await confirmManual_api(orderId)
      setPaymentStatus("manual")
      stopAll()
    } catch {
      setError("Could not submit manual confirmation. Please contact support.")
    }
  }

  const reset = () => {
    stopAll(); setQrData(null); setPaymentStatus("idle")
    setError(null); setCountdown(null); paidRef.current = false
  }

  return (
    <div className="flex items-center justify-center p-4">
      <div className="relative w-full max-w-sm">

        {error && (
          <div className="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-3 text-center text-sm">
            ⚠️ {error}
          </div>
        )}

        {/* IDLE / EXPIRED */}
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

        {/* PENDING */}
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
                  <p className="text-gray-800 font-bold text-lg">{qrData.merchant_name || "Tronmatix"}</p>
                  <p className="text-gray-900 font-black text-3xl mt-0.5">${qrData.amount || total || "0.00"}</p>
                </div>
                <div className="border-t-2 border-dashed border-gray-200 my-4" />
                <div className="flex justify-center mb-3">
                  {qrData.qr_code ? (
                    <QRCodeSVG value={qrData.qr_code} size={220} level="H" bgColor="#ffffff" fgColor="#000000" />
                  ) : (
                    <div className="w-[220px] h-[220px] flex items-center justify-center bg-gray-50 rounded">
                      <p className="text-gray-400 text-sm">Loading QR...</p>
                    </div>
                  )}
                </div>
                {/* FIX: show polling indicator */}
                <div className="flex items-center justify-center gap-2 mb-2">
                  <div className="w-2 h-2 rounded-full bg-green-400 animate-pulse" />
                  <span className="text-xs text-gray-400">Checking payment automatically...</span>
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
                  Keep this page open. Payment detects automatically every 4 seconds.
                </p>
              </div>
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

        {/* MANUAL */}
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
                Payment claim sent to admin for manual verification.
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

        {/* PAID */}
        {paymentStatus === "paid" && (
          <div className="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div className="bg-red-600 py-6 text-center">
              <span className="text-white text-3xl font-black tracking-widest">KHQR</span>
            </div>
            <div className="p-8 text-center">
              <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4"
                style={{ animation: 'popIn 0.5s cubic-bezier(0.34,1.56,0.64,1)' }}>
                <span className="text-green-500 text-3xl font-bold">✓</span>
              </div>
              <h2 className="text-xl font-black text-green-500 mb-1">Payment Successful!</h2>
              <p className="text-gray-500 text-sm mb-5">Transaction completed successfully!</p>
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
