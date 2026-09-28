/**
 * Cambodian phone number helpers for Firebase phone auth.
 *
 * Normalizes a local/international input to strict E.164 ("+855XXXXXXXXX")
 * before it's passed to Firebase signInWithPhoneNumber(), and validates the
 * final E.164 form so we fail fast with a friendly message instead of letting
 * Firebase reject it.
 */

const CAMBODIA_CODE = '855'

/**
 * Strip spaces/dashes, drop a leading "0" (Cambodian local trunk), and prepend
 * the +855 country code unless the input already has "+". Returns strict E.164
 * with NO spaces, or null if the input can't produce a valid number.
 *
 * Examples:
 *   "012949139"  → "+85512949139"
 *   "012 949 139"→ "+85512949139"
 *   "+85512949139" → "+85512949139"
 */
export function toE164Phone(raw) {
  if (!raw) return null
  const t = String(raw).trim()
  if (!t) return null

  // Remove everything except digits and a leading "+".
  let digits = t.replace(/[^\d+]/g, '')
  if (digits.startsWith('+')) {
    digits = digits.slice(1) // strip the "+" so we treat digits uniformly
  }

  // Drop a leading international dialing prefix "00".
  if (digits.startsWith('00')) {
    digits = digits.slice(2)
  }

  // Already includes the country code? Keep it.
  if (digits.startsWith(CAMBODIA_CODE)) {
    return '+' + digits
  }

  // Drop the Cambodian local trunk "0".
  if (digits.startsWith('0')) {
    digits = digits.slice(1)
  }

  return '+' + CAMBODIA_CODE + digits
}

/**
 * Strict E.164 validation for Cambodian numbers:
 *   +855 <first digit 1-9> <7 or 8 digits>  → no spaces.
 */
export function isValidE164Khmer(phone) {
  return /^\+855[1-9]\d{7,8}$/.test(phone || '')
}

/**
 * Returns a Khmer or English validation message, or null when valid.
 */
export function phoneValidationMessage(raw, isKhmer) {
  const e164 = toE164Phone(raw)
  if (!e164 || !isValidE164Khmer(e164)) {
    return isKhmer
      ? 'សូមបញ្ចូលលេខទូរស័ព្ទត្រឹមត្រូវ (ឧ. 012949139)'
      : 'Please enter a valid Cambodian phone number (e.g. 012949139).'
  }
  return null
}
