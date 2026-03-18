// src/api/qrApi.js

import axios from '../lib/axios'

// ─────────────────────────────────────────────────────────────────────────────
// All paths start with /api/... — axios baseURL handles the host:
//   DEV:  '' + /api/...  →  Vite proxy  →  http://127.0.0.1:8000/api/...
//   PROD: 'https://tronmatix-beckend.onrender.com' + /api/...  ✅
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Generate a KHQR code for an order.
 * POST /api/payment/generate-qr
 *
 * @param {{ id: number }} order
 * @returns {Promise<{
 *   success: boolean,
 *   data: {
 *     qr_code: string,
 *     qr_md5: string|null,
 *     amount: number,
 *     currency: string,
 *     qr_expiration: string
 *   }
 * }>}
 */
export const generatekhqr_api = async (order) => {
  const response = await axios.post('/api/payment/generate-qr', {
    order_id: order.id,
  })
  return response.data
}

/**
 * Check / verify payment status for an order.
 * POST /api/payment/verify
 *
 * @param {number} orderId
 * @returns {Promise<{
 *   success: boolean,
 *   status: 'paid'|'pending'|'expired',
 *   bakong_hash?: string,
 *   paid_at?: string
 * }>}
 */
export const checkpayment_api = async (orderId) => {
  const response = await axios.post('/api/payment/verify', {
    order_id: orderId,
  })
  return response.data
}

/**
 * "I paid" manual fallback — marks order as manual_pending, alerts admin.
 * POST /api/payment/confirm-manual
 *
 * @param {number} orderId
 * @returns {Promise<{ success: boolean, status: 'manual_pending' }>}
 */
export const confirmManual_api = async (orderId) => {
  const response = await axios.post('/api/payment/confirm-manual', {
    order_id: orderId,
  })
  return response.data
}

/**
 * Poll payment status until paid, expired, or timed-out.
 *
 * @param {number} orderId
 * @param {object} opts
 * @param {number}   [opts.intervalMs=4000]  — poll every N ms
 * @param {number}   [opts.maxAttempts=45]   — stop after N attempts (~3 min)
 * @param {Function} [opts.onSuccess]        — called with data when paid
 * @param {Function} [opts.onExpired]        — called when expired
 * @param {Function} [opts.onTimeout]        — called after maxAttempts
 * @param {Function} [opts.onError]          — called on network/server error
 * @returns {{ stop: Function }}
 */
export const pollPaymentStatus = (orderId, {
  intervalMs  = 4000,
  maxAttempts = 45,
  onSuccess   = () => {},
  onExpired   = () => {},
  onTimeout   = () => {},
  onError     = () => {},
} = {}) => {
  let attempts = 0
  let stopped  = false
  let timer    = null

  const stop = () => {
    stopped = true
    if (timer) {
      clearInterval(timer)
      timer = null
    }
  }

  const tick = async () => {
    if (stopped) return
    attempts++

    try {
      const data = await checkpayment_api(orderId)

      if (data.success && data.status === 'paid') {
        stop()
        onSuccess(data)
        return
      }

      if (data.status === 'expired') {
        stop()
        onExpired()
        return
      }

      // Still pending — check if we've hit max attempts
      if (attempts >= maxAttempts) {
        stop()
        onTimeout()
      }

    } catch (err) {
      const status = err.response?.status

      // 404 = not found yet (backend returns 404 for pending) — keep polling
      if (status === 404) return

      // 400 = expired by backend
      if (status === 400) {
        stop()
        onExpired()
        return
      }

      // Network failure, 5xx — stop and report
      stop()
      onError(err)
    }
  }

  // Fire immediately, then repeat
  tick()
  timer = setInterval(tick, intervalMs)

  return { stop }
}