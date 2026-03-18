// src/api/qrApi.js
import axios from './axios'

// All paths below start with '/api/...' which is correct now that
// axios baseURL is '' (empty). Vite proxy intercepts /api/* → Laravel.

/**
 * Generate a KHQR code for an order.
 * POST /api/payment/generate-qr
 *
 * @param {{ id: number }} order
 * @returns {Promise<{ success: boolean, data: { qr_code: string, qr_md5: string|null, amount: number, currency: string, qr_expiration: string } }>}
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
 * @returns {Promise<{ success: boolean, status: 'paid'|'pending'|'expired', bakong_hash?: string, paid_at?: string }>}
 */
export const checkpayment_api = async (orderId) => {
  const response = await axios.post('/api/payment/verify', {
    order_id: orderId,
  })
  return response.data
}

/**
 * "I paid" manual fallback — marks order as manual_pending and alerts admin.
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
 * Poll payment status on an interval until paid, expired, or timed-out.
 *
 * @param {number}   orderId
 * @param {object}   opts
 * @param {number}   [opts.intervalMs=4000]   poll interval in ms
 * @param {number}   [opts.maxAttempts=45]    ~3 min at 4s intervals
 * @param {Function} [opts.onSuccess]         called with verify response when paid
 * @param {Function} [opts.onExpired]         called when backend returns status='expired'
 * @param {Function} [opts.onTimeout]         called after maxAttempts without payment
 * @param {Function} [opts.onError]           called on unrecoverable network/server error
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

      // status === 'pending' — keep polling
      if (attempts >= maxAttempts) {
        stop()
        onTimeout()
      }

    } catch (err) {
      const status = err.response?.status

      // 404 = payment not found yet (backend behaviour for pending) — keep polling
      if (status === 404) return

      // 400 = expired
      if (status === 400) {
        stop()
        onExpired()
        return
      }

      // Network failure, 5xx, etc — stop and report
      stop()
      onError(err)
    }
  }

  const stop = () => {
    stopped = true
    if (timer) clearInterval(timer)
  }

  // Fire immediately, then on interval
  tick()
  timer = setInterval(tick, intervalMs)

  return { stop }
}