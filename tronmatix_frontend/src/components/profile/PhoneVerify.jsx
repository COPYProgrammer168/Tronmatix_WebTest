/**
 * src/components/profile/PhoneVerify.jsx
 * One-time phone verification via Firebase Phone Auth.
 * Renders only when user.phone_verified_at is null.
 *
 * Flow: enter phone → signInWithPhoneNumber (invisible reCAPTCHA) → SMS →
 *       enter 6-digit code → getIdToken() → POST /verify-phone → onVerified()
 */
import { useState, useRef } from 'react'
import {
  signInWithPhoneNumber,
  RecaptchaVerifier,
} from 'firebase/auth'
import { auth } from '../../lib/firebase'
import axiosClient from '../../lib/axios'
import { useLang } from '../../context/LanguageContext'

export default function PhoneVerify({ user, dark, notify, onVerified }) {
  const { t, isKhmer } = useLang()
  const font = isKhmer ? 'Kdam Thmor Pro, sans-serif' : 'Rajdhani,sans-serif'

  const [phone, setPhone] = useState(user?.phone ?? '')
  const [step, setStep] = useState('phone') // 'phone' | 'code' | 'done'
  const [code, setCode] = useState('')
  const [busy, setBusy] = useState(false)
  const [error, setError] = useState(null)

  // Store the Firebase confirmation result from signInWithPhoneNumber
  const confirmationRef = useRef(null)
  // Render the invisible reCAPTCHA into this container
  const recaptchaRef = useRef(null)
  const verifierRef = useRef(null)

  const c = {
    card:       dark ? '#111827' : '#F9FAFB',
    cardBorder: dark ? '#1F2937' : '#F3F4F6',
    text:       dark ? '#F9FAFB' : '#111827',
    muted:      dark ? '#9CA3AF' : '#6B7280',
    input:      dark ? '#1F2937' : '#FFFFFF',
    inputBorder: dark ? '#374151' : '#E5E7EB',
  }

  const makeVerifier = () => {
    if (verifierRef.current) return verifierRef.current
    // Invisible reCAPTCHA needs a container element in the DOM.
    if (!window.recaptchaVerifierContainer) {
      const div = document.createElement('div')
      div.id = 'phone-recaptcha'
      document.body.appendChild(div)
      window.recaptchaVerifierContainer = div
    }
    verifierRef.current = new RecaptchaVerifier(
      auth,
      'phone-recaptcha',
      { size: 'invisible' }
    )
    return verifierRef.current
  }

  const handleSendCode = async () => {
    setError(null)
    if (!/^\+?[0-9\s\-]{7,20}$/.test(phone.trim())) {
      setError(isKhmer ? 'សូមបញ្ចូលលេខទូរស័ព្ទឱ្យបានត្រឹមត្រូវ' : 'Please enter a valid phone number.')
      return
    }
    setBusy(true)
    try {
      const verifier = makeVerifier()
      // Firebase needs international format with country code.
      //   012 345 678 → +855 12 345 678 (Cambodian local format)
      const p = phone.trim()
      const digits = p.replace(/[^\d+]/g, '')
      let formatted
      if (digits.startsWith('+')) {
        formatted = digits
      } else if (digits.startsWith('0')) {
        formatted = '+855' + digits.slice(1)
      } else {
        formatted = '+855' + digits
      }
      const confirmation = await signInWithPhoneNumber(auth, formatted, verifier)
      confirmationRef.current = confirmation
      setStep('code')
    } catch (e) {
      console.error('sendCode error', e)
      setError(e?.message || (isKhmer ? 'មិនអាចផ្ញើលេខកូដបានទេ' : 'Failed to send verification code.'))
    } finally {
      setBusy(false)
    }
  }

  const handleVerifyCode = async () => {
    setError(null)
    if (!confirmationRef.current) { setError(isKhmer ? 'សូមចាប់ផ្តើមម្តងទៀត' : 'Please start again.'); return }
    if (!/^\d{6}$/.test(code.trim())) {
      setError(isKhmer ? 'សូមបញ្ចូលកូដ ៦ ខ្ទង់' : 'Enter the 6-digit code.')
      return
    }
    setBusy(true)
    try {
      const result = await confirmationRef.current.confirm(code.trim())
      const idToken = await result.user.getIdToken()
      const res = await axiosClient.post('/api/verify-phone', { id_token: idToken })
      if (res.data?.success) {
        setStep('done')
        notify(isKhmer ? 'បានផ្ទៀងផ្ទាត់លេខទូរស័ព្ទដោយជោគជ័យ! ✅' : 'Phone verified successfully! ✅', 'success')
        onVerified?.()
      } else {
        setError(res.data?.message || 'Verification failed.')
      }
    } catch (e) {
      console.error('verifyCode error', e)
      setError(e?.message || (isKhmer ? 'កូដមិនត្រឹមត្រូវ' : 'Invalid code. Please try again.'))
    } finally {
      setBusy(false)
    }
  }

  const resetFlow = () => {
    setStep('phone')
    setCode('')
    setError(null)
    confirmationRef.current = null
    verifierRef.current = null
    if (window.recaptchaVerifierContainer) {
      window.recaptchaVerifierContainer.remove?.()
      delete window.recaptchaVerifierContainer
    }
  }

  return (
    <div style={{ border: `1px solid ${c.cardBorder}`, borderRadius: 14, background: c.card, overflow: 'hidden', marginTop: 20, fontFamily: font }}>
      {/* Header */}
      <div style={{ padding: '14px 20px', display: 'flex', alignItems: 'center', gap: 12, borderBottom: `1px solid ${c.cardBorder}`, background: dark ? '#0F172A' : '#FFFFFF' }}>
        <div style={{ width: 36, height: 36, borderRadius: 10, background: 'linear-gradient(135deg,#4285F4,#34A853)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, fontSize: 17 }}>
          📱
        </div>
        <div>
          <div style={{ fontSize: 14, fontWeight: 800, letterSpacing: isKhmer ? 0 : 1, color: c.text }}>{isKhmer ? t('phoneVerify.title') : 'VERIFY YOUR PHONE'}</div>
          <div style={{ fontSize: 12, color: c.muted, marginTop: 1 }}>{isKhmer ? t('phoneVerify.subtitle') : 'One-time verification — secures your account'}</div>
        </div>
        <div style={{ marginLeft: 'auto' }}>
          <span style={{ fontSize: 11, fontWeight: 700, color: '#F59E0B', background: 'rgba(245,158,11,0.1)', padding: '4px 10px', borderRadius: 20, letterSpacing: isKhmer ? 0 : 1 }}>{isKhmer ? t('phoneVerify.pending') : 'NOT VERIFIED'}</span>
        </div>
      </div>

      {/* Body */}
      <div style={{ padding: '18px 20px' }}>
        {step === 'phone' && (
          <div>
            <p style={{ fontSize: 13, color: c.muted, lineHeight: 1.6, margin: '0 0 14px' }}>
              {isKhmer ? t('phoneVerify.desc') : 'Verify your phone number to enable account recovery and important notifications.'}
            </p>
            <label style={{ display: 'block', fontSize: 10, letterSpacing: isKhmer ? 0 : 2, color: c.muted, fontWeight: 700, marginBottom: 6, textTransform: 'uppercase' }}>
              {isKhmer ? t('phoneVerify.phoneLabel') : 'PHONE NUMBER'}
            </label>
            <input
              type="tel"
              value={phone}
              onChange={(e) => setPhone(e.target.value)}
              placeholder="+855 12 345 678"
              style={{ width: '100%', padding: '11px 14px', borderRadius: 10, background: c.input, border: `1px solid ${error ? '#EF4444' : c.inputBorder}`, color: c.text, fontSize: 14, outline: 'none', fontFamily: font, boxSizing: 'border-box' }}
            />
            {error && <div style={{ fontSize: 12, color: '#EF4444', marginTop: 6 }}>{error}</div>}
            <button
              onClick={handleSendCode}
              disabled={busy}
              style={{ width: '100%', marginTop: 14, padding: '11px 0', borderRadius: 10, border: 'none', background: 'linear-gradient(135deg,#4285F4,#34A853)', color: '#fff', fontSize: 13, fontWeight: 800, cursor: busy ? 'wait' : 'pointer', opacity: busy ? 0.6 : 1, letterSpacing: isKhmer ? 0 : 1 }}
            >
              {busy ? (isKhmer ? 'កំពុងផ្ញើ...' : 'SENDING...') : `📨 ${isKhmer ? t('phoneVerify.sendCode') : 'SEND CODE'}`}
            </button>
            <p style={{ fontSize: 10, color: c.muted, textAlign: 'center', margin: '8px 0 0' }}>
              {isKhmer ? t('phoneVerify.recaptchaHint') : 'Protected by reCAPTCHA. Carrier SMS rates may apply.'}
            </p>
          </div>
        )}

        {step === 'code' && (
          <div>
            <p style={{ fontSize: 13, color: c.muted, lineHeight: 1.6, margin: '0 0 14px' }}>
              {isKhmer
                ? t('phoneVerify.codeDesc')
                : `Enter the 6-digit code sent to ${phone}.`}
            </p>
            <input
              type="text"
              inputMode="numeric"
              pattern="[0-9]{6}"
              maxLength={6}
              value={code}
              onChange={(e) => setCode(e.target.value)}
              placeholder="123456"
              style={{ width: '100%', padding: '11px 14px', borderRadius: 10, background: c.input, border: `1px solid ${error ? '#EF4444' : c.inputBorder}`, color: c.text, fontSize: 16, letterSpacing: 4, textAlign: 'center', outline: 'none', fontFamily: font, boxSizing: 'border-box' }}
            />
            {error && <div style={{ fontSize: 12, color: '#EF4444', marginTop: 6 }}>{error}</div>}
            <button
              onClick={handleVerifyCode}
              disabled={busy}
              style={{ width: '100%', marginTop: 14, padding: '11px 0', borderRadius: 10, border: 'none', background: 'linear-gradient(135deg,#4285F4,#34A853)', color: '#fff', fontSize: 13, fontWeight: 800, cursor: busy ? 'wait' : 'pointer', opacity: busy ? 0.6 : 1, letterSpacing: isKhmer ? 0 : 1 }}
            >
              {busy ? (isKhmer ? 'កំពុងផ្ទៀងផ្ទាត់...' : 'VERIFYING...') : `✅ ${isKhmer ? t('phoneVerify.verify') : 'VERIFY'}`}
            </button>
            <button onClick={resetFlow} style={{ width: '100%', marginTop: 8, padding: '8px 0', background: 'transparent', border: 'none', color: c.muted, fontSize: 12, cursor: 'pointer', textDecoration: 'underline' }}>
              {isKhmer ? t('phoneVerify.changeNumber') : 'Change phone number'}
            </button>
          </div>
        )}

        {step === 'done' && (
          <div style={{ textAlign: 'center', padding: '8px 0' }}>
            <div style={{ fontSize: 28, marginBottom: 8 }}>🎉</div>
            <div style={{ fontSize: 15, fontWeight: 800, color: '#16A34A' }}>{isKhmer ? t('phoneVerify.verified') : 'PHONE VERIFIED'}</div>
            <div style={{ fontSize: 12, color: c.muted, marginTop: 4 }}>{phone}</div>
          </div>
        )}
      </div>
    </div>
  )
}
