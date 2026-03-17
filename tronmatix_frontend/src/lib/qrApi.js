import axios from './axios'

/**
 * Generate a KHQR code for an order.
 * POST /api/payment/generate-qr
 * @param {{ id: number }} order  — the full order object or { id }
 * @returns {Promise<{ success: boolean, data: { qr_code: string, qr_md5: string|null, amount: number, currency: string, qr_expiration: string } }>}
 */
export const generatekhqr_api = async (order) => {
  const response = await axios.post('/api/payment/generate-qr', {
    order_id: order.id,   // backend validates: 'order_id' => 'required|integer'
  })
  return response.data    // { success, data: { qr_code, qr_md5, amount, currency, qr_expiration } }
}

/**
 * Check / verify payment status for an order.
 * POST /api/payment/verify
 * @param {number} orderId
 * @returns {Promise<{ success: boolean, status: 'paid'|'pending'|'expired', bakong_hash?: string, paid_at?: string }>}
 */
export const checkpayment_api = async (orderId) => {
  const response = await axios.post('/api/payment/verify', {
    order_id: orderId,    // backend validates: 'order_id' => 'required|integer'
  })
  return response.data    // { success, status, bakong_hash?, paid_at? }
}

/**
 * "I paid" manual fallback — tells backend to mark as manual_pending
 * and alert admin via Telegram.
 * POST /api/payment/confirm-manual
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
 * @param {number}   [opts.intervalMs=4000]  - poll interval in ms
 * @param {number}   [opts.maxAttempts=45]   - ~3 min at 4 s intervals
 * @param {Function} [opts.onSuccess]        - called with verify response when paid
 * @param {Function} [opts.onExpired]        - called when backend returns status='expired'
 * @param {Function} [opts.onTimeout]        - called after maxAttempts without payment
 * @param {Function} [opts.onError]          - called on unrecoverable network error
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

  const tick = async () => {
    if (stopped) return
    attempts++

    try {
      const data = await checkpayment_api(orderId)

      if (data.success && data.status === 'paid') {
        stopped = true
        clearInterval(timer)
        onSuccess(data)
        return
      }

      if (data.status === 'expired') {
        stopped = true
        clearInterval(timer)
        onExpired()
        return
      }

      // status === 'pending' — keep going
      if (attempts >= maxAttempts) {
        stopped = true
        clearInterval(timer)
        onTimeout()
      }

    } catch (err) {
      // 404 = "not found yet" from verify — keep polling (backend returns 404 for pending)
      if (err.response?.status === 404) return

      // 400 = expired
      if (err.response?.status === 400) {
        stopped = true
        clearInterval(timer)
        onExpired()
        return
      }

      // Anything else (network down, 5xx) — stop and report
      stopped = true
      clearInterval(timer)
      onError(err)
    }
  }

  const timer = setInterval(tick, intervalMs)
  tick() // fire immediately on first call

  return {
    stop: () => {
      stopped = true
      clearInterval(timer)
    },
  }
}