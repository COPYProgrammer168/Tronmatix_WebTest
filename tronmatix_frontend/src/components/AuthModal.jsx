import { useState, useEffect, useRef } from 'react'
import { useAuth } from '../context/AuthContext'
import { useLang } from '../context/LanguageContext'
import { useTheme } from '../context/ThemeContext'
import { signInWithPhoneNumber, RecaptchaVerifier } from 'firebase/auth'
import { auth } from '../lib/firebase'
import { toE164Phone, phoneValidationMessage } from '../lib/phone'
import logo from '../assets/logo.png'

const GOOGLE_CLIENT_ID = import.meta.env.VITE_GOOGLE_CLIENT_ID     || ''

export default function AuthModal({ mode, resetToken, resetEmail, onClose, onSwitch }) {
  const { login, register, forgotPassword, resetPassword, resetByPhone, googleLogin, telegramLogin, loading } = useAuth()
  const { dark } = useTheme()
  const { isKhmer } = useLang()
  const authFont     = isKhmer ? 'Kh_Jrung_Thom, Khmer OS, sans-serif' : 'Rajdhani, sans-serif'
  const authBodyFont = isKhmer
    ? "Kdam Thmor Pro, sans-serif"
    : "Rajdhani, sans-serif";

  const [form, setForm]           = useState({ usernameOrEmail: '', username: '', email: '', password: '', confirm: '' })
  const [error, setError]         = useState('')
  const [success, setSuccess]     = useState('')
  const [failCount, setFailCount] = useState(0)
  const [cooldown, setCooldown]   = useState(false)
  const [pwStrength, setPwStrength] = useState({ score: 0, hints: [] })
  const [socialLoading, setSocialLoading] = useState(null) // 'google' | 'telegram'

  // ── Forgot-password method toggle: email | phone (Firebase OTP) ──────────
  const [forgotMethod, setForgotMethod] = useState('email')
  const [phone, setPhone]               = useState('')
  const [otpCode, setOtpCode]           = useState('')
  const [phoneStep, setPhoneStep]       = useState('phone') // 'phone' | 'code'
  const [phoneBusy, setPhoneBusy]       = useState(false)
  const [cooldownSecs, setCooldownSecs] = useState(0) // email-resubmit / ban countdown
  const confirmationRef                 = useRef(null)
  const verifierRef                     = useRef(null)

  const isLogin    = mode === 'login'
  const isRegister = mode === 'register'
  const isForgot   = mode === 'forgot'
  const isReset    = mode === 'reset-password'
  const showSocial = isLogin

  const c = {
    modalBg:      dark ? '#1f2937' : '#ffffff',
    text:         dark ? '#f9fafb' : '#1f2937',
    textMuted:    dark ? '#9ca3af' : '#6b7280',
    inputBg:      dark ? '#111827' : '#f9fafb',
    inputBorder:  dark ? '#374151' : '#e5e7eb',
    tabBg:        dark ? '#111827' : '#f3f4f6',
    tabActive:    dark ? '#1f2937' : '#ffffff',
    closeBtn:     dark ? '#6b7280' : '#9ca3af',
    closeBtnHov:  dark ? '#f9fafb' : '#1f2937',
    btnBg:        dark ? '#111827' : '#ffffff',
    btnBorder:    dark ? '#374151' : '#e5e7eb',
    btnText:      dark ? '#f9fafb' : '#1f2937',
    divider:      dark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)',
    socialBg:     dark ? '#111827' : '#f9fafb',
    socialBorder: dark ? '#374151' : '#e5e7eb',
    socialHover:  dark ? '#1f2937' : '#f3f4f6',
  }

  const inputStyle = {
    // All layout + font via inline style — NO className — prevents Tailwind preflight override
    display: 'block',
    width: '100%',
    boxSizing: 'border-box',
    padding: '12px 16px',
    borderRadius: 12,
    outline: 'none',
    fontFamily: authBodyFont,
    fontSize: isKhmer ? 15 : 16,
    lineHeight: isKhmer ? 1.9 : 1.5,
    background: c.inputBg,
    border: `1px solid ${c.inputBorder}`,
    color: c.text,
    transition: 'border-color 0.15s',
  }
  const focusHandlers = {
    // Keep fontFamily explicitly on focus/blur so browser doesn't reset it
    onFocus: (e) => { e.target.style.borderColor = '#F97316'; e.target.style.fontFamily = authBodyFont },
    onBlur:  (e) => { e.target.style.borderColor = c.inputBorder; e.target.style.fontFamily = authBodyFont },
  }

  const handle = (e) => {
    const { name, value } = e.target
    setForm(prev => ({ ...prev, [name]: value }))
    if (name === 'password' && isRegister) setPwStrength(checkPasswordStrength(value))
  }

  function checkPasswordStrength(pw) {
    const hints = []
    if (pw.length < 8)             hints.push('At least 8 characters')
    if (!/[A-Z]/.test(pw))         hints.push('One uppercase letter')
    if (!/[a-z]/.test(pw))         hints.push('One lowercase letter')
    if (!/[0-9]/.test(pw))         hints.push('One number')
    if (!/[^A-Za-z0-9]/.test(pw))  hints.push('One symbol')
    return { score: 5 - hints.length, hints }
  }

  const strengthColors = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#16a34a']
  const strengthLabels = ['Very weak', 'Weak', 'Fair', 'Strong', 'Very strong']

  const validEmail    = (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)
  const validUsername = (v) => /^[a-zA-Z0-9_]{3,50}$/.test(v)
  const validPassword = (v) => v.length >= 8 && /[A-Z]/.test(v) && /[a-z]/.test(v) && /[0-9]/.test(v) && /[^A-Za-z0-9]/.test(v)

  const recordFailure = () => {
    const next = failCount + 1
    setFailCount(next)
    if (next >= 5) {
      setCooldown(true)
      setError('Too many attempts. Please wait 30 seconds.')
      setTimeout(() => { setCooldown(false); setFailCount(0); setError('') }, 30_000)
    }
  }

  const handleSwitch = (newMode, prefill = {}) => {
    setForm({ usernameOrEmail: prefill.email || prefill.username || '', username: '', email: '', password: '', confirm: '' })
    setError('')
    setSuccess('')
    setPwStrength({ score: 0, hints: [] })
    setForgotMethod('email')
    resetPhoneFlow()
    onSwitch(newMode)
  }

  // ── Load GSI script once ──────────────────────────────────────────────────
  useEffect(() => {
    if (!showSocial || !GOOGLE_CLIENT_ID) return
    if (!document.getElementById('gsi-script')) {
      const s    = document.createElement('script')
      s.id       = 'gsi-script'
      s.src      = 'https://accounts.google.com/gsi/client'
      s.async    = true
      document.head.appendChild(s)
    }
  }, [showSocial])

  // ── Google Login ──────────────────────────────────────────────────────────
  const handleGoogleLogin = () => {
    if (!GOOGLE_CLIENT_ID) {
      setError('Google login is not configured. Set VITE_GOOGLE_CLIENT_ID in .env.')
      return
    }
    if (typeof window.google === 'undefined') {
      setError('Google SDK is still loading. Please try again in a moment.')
      return
    }

    setSocialLoading('google')
    setError('')

    window.google.accounts.oauth2.initTokenClient({
      client_id: GOOGLE_CLIENT_ID,
      scope: 'email profile openid',
      callback: async (tokenResponse) => {
        if (tokenResponse.error) {
          setError('Google sign-in was cancelled.')
          setSocialLoading(null)
          return
        }

        //Use googleLogin from AuthContext — clean, centralized, handles is_new_user
        const res = await googleLogin(tokenResponse.access_token)

        setSocialLoading(null)

        if (res.success) {
          setSuccess('Signed in with Google!')
          // Close modal after brief delay — ProfileSetupModal shows if isNewUser
          setTimeout(onClose, 600)
        } else {
          setError(res.message)
          recordFailure()
        }
      },
    }).requestAccessToken()
  }


  // ── Submit ─────────────────────────────────────────────────────────────────
  const submit = async () => {
    if (cooldown || loading) return
    setError('')
    setSuccess('')

    const f = {
      usernameOrEmail: form.usernameOrEmail.trim(),
      username:        form.username.trim(),
      email:           form.email.trim().toLowerCase(),
      password:        form.password,
      confirm:         form.confirm,
    }

    if (isReset) {
      if (!form.password)             { setError('Please enter a new password.'); return }
      if (form.password.length < 8)   { setError('Password must be at least 8 characters.'); return }
      if (form.password !== form.confirm) { setError('Passwords do not match.'); return }
      const res = await resetPassword(resetToken, resetEmail, form.password)
      if (res.success) {
        setSuccess(res.message || 'Password reset successfully!')
        setTimeout(() => handleSwitch('login'), 1500)
      } else {
        setError(res.message)
      }
      return
    }

    if (isForgot) {
      if (forgotMethod === 'phone') {
        await handlePhoneReset()
        return
      }
      if (!f.email)             { setError('Please enter your email address.'); return }
      if (!validEmail(f.email)) { setError('Please enter a valid email address.'); return }
      const res = await forgotPassword(f.email)
      if (res.success) { setSuccess(res.message); setCooldownSecs(0) }
      else {
        setError(res.message)
        recordFailure()
        const secs = res.banSeconds || res.cooldownSeconds || 0
        if (secs > 0) { setCooldownSecs(secs); startCooldown(secs) }
      }
      return
    }

    if (isLogin) {
      if (!f.usernameOrEmail || !f.password) { setError('Please fill all fields.'); return }
      const res = await login(f.usernameOrEmail, f.password)
      if (res.success) { setSuccess('Login successful!'); setTimeout(onClose, 800) }
      else { setError(res.message); recordFailure() }
      return
    }

    if (!f.username || !f.email || !f.password) { setError('Please fill all required fields.'); return }
    if (!validUsername(f.username)) { setError('Username must be 3–50 characters: letters, numbers, underscores only.'); return }
    if (!validEmail(f.email))       { setError('Please enter a valid email address.'); return }
    if (!validPassword(f.password)) { setError('Password must be at least 8 characters with uppercase, lowercase, number, and symbol.'); return }
    if (f.password !== f.confirm)   { setError('Passwords do not match.'); return }

    const res = await register(f.username, f.email, f.password, f.confirm)
    if (res.success) {
      setSuccess('Account created! Redirecting to login…')
      setTimeout(() => handleSwitch('login', { email: res.email }), 1200)
    } else {
      setError(res.message)
      recordFailure()
    }
  }

  // ── Cooldown / ban countdown for email forgot-password ─────────────────────
  const startCooldown = (seconds) => {
    setCooldownSecs(seconds)
    const tick = setInterval(() => {
      setCooldownSecs(prev => {
        if (prev <= 1) { clearInterval(tick); return 0 }
        return prev - 1
      })
    }, 1000)
  }

  const fmtCooldown = (s) => {
    const m = Math.floor(s / 60)
    const ss = s % 60
    return `${m}:${String(ss).padStart(2, '0')}`
  }

  // ── Phone-OTP forgot-password handlers (Firebase) ──────────────────────────
  const makeRecaptchaVerifier = () => {
    if (verifierRef.current) return verifierRef.current
    if (!window.tronmatixRecaptchaContainer) {
      const div = document.createElement('div')
      div.id = 'authmodal-recaptcha'
      document.body.appendChild(div)
      window.tronmatixRecaptchaContainer = div
    }
    verifierRef.current = new RecaptchaVerifier(auth, 'authmodal-recaptcha', { size: 'invisible' })
    return verifierRef.current
  }

  const handleSendOtp = async () => {
    setError('')
    setSuccess('')
    // Show the raw input as-is in the field; only normalize here on submit.
    const raw = phone.trim()
    const e164 = toE164Phone(raw)
    const invalidMsg = phoneValidationMessage(raw, isKhmer)
    if (invalidMsg || !e164) {
      setError(invalidMsg || (isKhmer ? 'សូមបញ្ចូលលេខទូរស័ព្ទឱ្យបានត្រឹមត្រូវ' : 'Please enter a valid phone number.'))
      return
    }
    setPhoneBusy(true)
    try {
      const verifier = makeRecaptchaVerifier()
      // Strict E.164: +855XXXXXXXXX (no spaces)
      const confirmation = await signInWithPhoneNumber(auth, e164, verifier)
      confirmationRef.current = confirmation
      setPhoneStep('code')
    } catch (e) {
      console.error('sendOtp error', e)
      setError(e?.message || (isKhmer ? 'មិនអាចផ្ញើលេខកូដបានទេ' : 'Failed to send verification code.'))
    } finally {
      setPhoneBusy(false)
    }
  }

  const handlePhoneReset = async () => {
    setError('')
    setSuccess('')
    if (!confirmationRef.current) {
      setError(isKhmer ? 'សូមផ្ញើលេខកូដមុន' : 'Please send a code first.')
      return
    }
    if (!/^\d{6}$/.test(otpCode.trim())) {
      setError(isKhmer ? 'សូមបញ្ចូលកូដ ៦ ខ្ទង់' : 'Enter the 6-digit code.')
      return
    }
    if (!validPassword(form.password)) {
      setError(isKhmer ? 'ពាក្យសម្ងាត់ត្រូវមាន ៨ តួ អក្សរធំ លេខ និងនិមិត្តសញ្ញា' : 'Password must be at least 8 chars with uppercase, number, and symbol.')
      return
    }
    if (form.password !== form.confirm) {
      setError(isKhmer ? 'ពាក្យសម្ងាត់មិនដូចគ្នា' : 'Passwords do not match.')
      return
    }
    setPhoneBusy(true)
    try {
      const result = await confirmationRef.current.confirm(otpCode.trim())
      const idToken = await result.user.getIdToken()
      const res = await resetByPhone(idToken, form.password)
      if (res.success) {
        setSuccess(isKhmer ? 'បានកំណត់ពាក្យសម្ងាត់ថ្មី! អ្នកអាចចូលបាន។' : res.message || 'Password reset! You can now log in.')
        setTimeout(() => handleSwitch('login'), 1500)
      } else {
        setError(res.message)
      }
    } catch (e) {
      console.error('phoneReset error', e)
      setError(e?.message || (isKhmer ? 'កូដមិនត្រឹមត្រូវ' : 'Invalid code. Please try again.'))
    } finally {
      setPhoneBusy(false)
    }
  }

  const resetPhoneFlow = () => {
    setPhone('')
    setOtpCode('')
    setPhoneStep('phone')
    confirmationRef.current = null
    verifierRef.current = null
    if (window.tronmatixRecaptchaContainer) {
      window.tronmatixRecaptchaContainer.remove?.()
      delete window.tronmatixRecaptchaContainer
    }
  }

  const onKeyDown = (e) => { if (e.key === 'Enter') submit() }

  const socialBtnBase = {
    display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10,
    width: '100%', padding: '11px 16px', borderRadius: 12,
    border: `1px solid ${c.socialBorder}`, background: c.socialBg, color: c.text,
    fontSize: 15, fontWeight: 700, fontFamily: authFont,
    letterSpacing: 0.5, cursor: 'pointer', transition: 'background 0.15s, border-color 0.15s',
  }

  return (
    <div
      className="fixed inset-0 z-[100] flex items-center justify-center"
      style={{ background: 'rgba(0,0,0,0.55)', backdropFilter: 'blur(4px)' }}
      onClick={(e) => { if (e.target === e.currentTarget) onClose() }}
    >
      <div
        className="rounded-3xl shadow-2xl w-full mx-4 relative"
        style={{ background: c.modalBg, maxWidth: 400, maxHeight: '90vh', overflowY: 'auto', padding: '32px', scrollbarWidth: 'none' }}
      >
        {/* Close */}
        <button
          onClick={onClose}
          className="absolute top-4 right-4 text-xl font-bold transition-colors"
          style={{ color: c.closeBtn }}
          onMouseEnter={e => { e.currentTarget.style.color = c.closeBtnHov }}
          onMouseLeave={e => { e.currentTarget.style.color = c.closeBtn }}
        >✕</button>

        {/* Logo */}
        <div className="flex flex-col items-center mb-6">
          <img src={logo} alt="Tronmatix" className="h-14 mb-1" />
        </div>

        {/* Tabs */}
        {!isForgot && !isReset && (
          <div className="flex rounded-full p-1 mb-6" style={{ background: c.tabBg }}>
            {[
              ['login',    isKhmer ? 'ចូល'       : 'LOGIN'],
              ['register', isKhmer ? 'ចុះឈ្មោះ' : 'REGISTER'],
            ].map(([val, label]) => (
              <button key={val} onClick={() => handleSwitch(val)}
                className="flex-1 py-2 rounded-full font-bold tracking-wider transition-all"
                style={{
                  fontFamily: authFont,
                  fontSize: isKhmer ? 16 : 18,
                  letterSpacing: isKhmer ? 0 : 2,
                  background: mode === val ? c.tabActive : 'transparent',
                  color: mode === val ? '#F97316' : c.textMuted,
                  boxShadow: mode === val ? '0 1px 4px rgba(0,0,0,0.15)' : 'none',
                }}>{label}</button>
            ))}
          </div>
        )}

         {/* Reset-password header (from emailed link) */}
         {isReset && (
           <div className="mb-6 text-center">
             <div className="text-4xl mb-2">🔑</div>
             <h2 className="font-black" style={{ fontFamily: authFont, fontSize: isKhmer ? 20 : 24, color: c.text, letterSpacing: isKhmer ? 0 : 2 }}>
               {isKhmer ? 'កំណត់ពាក្យសម្ងាត់ថ្មី' : 'SET NEW PASSWORD'}
             </h2>
             <p className="mt-1" style={{ fontSize: 14, color: c.textMuted, fontFamily: authBodyFont }}>
               {isKhmer ? 'កំណត់ពាក្យសម្ងាត់ថ្មីសម្រាប់ ' + resetEmail : 'Set a new password for ' + resetEmail}
             </p>
           </div>
         )}

         {/* Forgot header */}
        {isForgot && (
          <div className="mb-6 text-center">
            <div className="text-4xl mb-2">🔐</div>
            <h2 className="font-black" style={{ fontFamily: authFont, fontSize: isKhmer ? 20 : 24, color: c.text, letterSpacing: isKhmer ? 0 : 2 }}>
              {isKhmer ? 'ភ្លេចពាក្យសម្ងាត់' : 'FORGOT PASSWORD'}
            </h2>
            <p className="mt-1" style={{ fontSize: 14, color: c.textMuted, fontFamily: authBodyFont }}>
              {forgotMethod === 'phone'
                ? (isKhmer ? 'បញ្ចូលលេខទូរស័ព្ទរបស់អ្នក ដើម្បីទទួលលេខកូដ' : "Enter your phone number to receive a reset code.")
                : (isKhmer ? 'បញ្ចូលអ៊ីមែលរបស់អ្នក ហើយយើងនឹងផ្ញើតំណកំណត់ពាក្យសម្ងាត់ឡើងវិញ។' : "Enter your email and we'll send you a reset link.")}
            </p>
          </div>
        )}

        {/* Social buttons */}
        {showSocial && (
          <div className="mb-5" style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>

            {/* Google */}
            <button
              style={socialBtnBase}
              disabled={!!socialLoading || loading}
              onClick={handleGoogleLogin}
              onMouseEnter={e => { e.currentTarget.style.background = c.socialHover; e.currentTarget.style.borderColor = '#4285F4' }}
              onMouseLeave={e => { e.currentTarget.style.background = c.socialBg;    e.currentTarget.style.borderColor = c.socialBorder }}
            >
              {socialLoading === 'google' ? (
                <span style={{ fontSize: 14, color: c.textMuted }}>Connecting…</span>
              ) : (
                <>
                  <svg width="20" height="20" viewBox="0 0 24 24">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                  </svg>
                  <span>{isKhmer ? 'ភ្ជាប់ជាមួយ Google' : 'Continue with Google'}</span>
                </>
              )}
            </button>
            
            {/* Divider */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 10, margin: '4px 0 2px' }}>
              <div style={{ flex: 1, height: 1, background: c.divider }} />
              <span style={{ fontSize: 11, fontWeight: 700, color: c.textMuted, letterSpacing: isKhmer ? 0 : 1, fontFamily: authFont }}>{isKhmer ? 'ឬ' : 'OR'}</span>
              <div style={{ flex: 1, height: 1, background: c.divider }} />
            </div>
          </div>
        )}

        {/* Fields */}
        <div className="space-y-3" onKeyDown={onKeyDown}>
          {isLogin && (
            <>
              <input name="usernameOrEmail" placeholder={isKhmer ? 'ឈ្មោះអ្នកប្រើ ឬ អ៊ីមែល' : 'Username or Email'}
                value={form.usernameOrEmail} onChange={handle}
                autoComplete="username email"
                style={inputStyle} {...focusHandlers} />
              <input name="password" type="password" placeholder={isKhmer ? 'ពាក្យសម្ងាត់' : 'Password'}
                value={form.password} onChange={handle}
                autoComplete="current-password"
                style={inputStyle} {...focusHandlers} />
            </>
          )}

          {isRegister && (
            <>
              <input name="username" placeholder={isKhmer ? 'ឈ្មោះអ្នកប្រើ' : 'Username'}
                value={form.username} onChange={handle}
                autoComplete="username"
                style={inputStyle} {...focusHandlers} />
              <input name="email" type="email" placeholder={isKhmer ? 'អ៊ីមែល' : 'Email address'}
                value={form.email} onChange={handle}
                autoComplete="email"
                style={inputStyle} {...focusHandlers} />
              <input name="password" type="password" placeholder={isKhmer ? 'ពាក្យសម្ងាត់' : 'Password'}
                value={form.password} onChange={handle}
                autoComplete="new-password"
                style={inputStyle} {...focusHandlers} />

              {form.password.length > 0 && (
                <div>
                  <div className="flex gap-1 mb-1">
                    {[1,2,3,4,5].map(i => (
                      <div key={i} className="h-1 flex-1 rounded-full transition-all"
                        style={{ background: i <= pwStrength.score ? strengthColors[pwStrength.score - 1] : (dark ? '#374151' : '#e5e7eb') }} />
                    ))}
                  </div>
                  {pwStrength.score < 5 && (
                    <p style={{ fontSize: 12, color: strengthColors[pwStrength.score - 1] || '#9ca3af' }}>
                      {pwStrength.score > 0 && `${strengthLabels[pwStrength.score - 1]} — `}
                      {isKhmer ? 'នៅខ្វះ: ' : 'Still need: '}{pwStrength.hints.join(', ')}
                    </p>
                  )}
                  {pwStrength.score === 5 && <p style={{ fontSize: 12, color: '#16a34a' }}>{isKhmer ? 'ពាក្យសម្ងាត់រឹងមាំ ✓' : 'Strong password ✓'}</p>}
                </div>
              )}

              <input name="confirm" type="password" placeholder={isKhmer ? 'បញ្ជាក់ពាក្យសម្ងាត់' : 'Confirm Password'}
                value={form.confirm} onChange={handle}
                autoComplete="new-password"
                style={inputStyle} {...focusHandlers} />
            </>
          )}

          {/* Reset-password fields */}
         {isReset && (
           <>
             <div style={{ marginBottom: 16, fontSize: 13, color: c.textMuted, textAlign: 'center' }}>
               {isKhmer ? 'កំណត់ពាក្យសម្ងាត់ថ្មីសម្រាប់' : 'Resetting password for'}: <strong style={{ color: c.text }}>{resetEmail}</strong>
             </div>
             <input name="password" type="password" placeholder={isKhmer ? 'ពាក្យសម្ងាត់ថ្មី' : 'New password'}
               value={form.password} onChange={handle}
               autoComplete="new-password"
               style={inputStyle} {...focusHandlers} />
             <input name="confirm" type="password" placeholder={isKhmer ? 'បញ្ជាក់ពាក្យសម្ងាត់ថ្មី' : 'Confirm new password'}
               value={form.confirm} onChange={handle}
               autoComplete="new-password"
               style={inputStyle} {...focusHandlers} />
           </>
         )}

         {isForgot && forgotMethod === 'email' && (
            <input name="email" type="email" placeholder={isKhmer ? 'អ៊ីមែលរបស់អ្នក' : 'Your email address'}
              value={form.email} onChange={handle}
              autoComplete="email"
              style={inputStyle} {...focusHandlers} />
          )}

          {isForgot && forgotMethod === 'phone' && phoneStep === 'phone' && (
            <div>
              <input name="phone" type="tel" placeholder={isKhmer ? 'លេខទូរស័ព្ទរបស់អ្នក' : 'Your phone number'}
                value={phone} onChange={(e) => setPhone(e.target.value)}
                autoComplete="tel"
                style={inputStyle} {...focusHandlers} />
              <button type="button" onClick={handleSendOtp} disabled={phoneBusy}
                className="w-full mt-2 rounded-full py-3 font-bold transition-all shadow disabled:opacity-50"
                style={{ fontFamily: authFont, fontSize: 15, background: '#0088cc', border: 'none', color: '#fff' }}>
                {phoneBusy ? (isKhmer ? 'កំពុងផ្ញើ...' : 'SENDING…') : (isKhmer ? '📨 ផ្ញើលេខកូដ' : '📨 SEND CODE')}
              </button>
            </div>
          )}

          {isForgot && forgotMethod === 'phone' && phoneStep === 'code' && (
            <>
              <input name="otp" type="text" inputMode="numeric" maxLength={6} placeholder={isKhmer ? 'លេខកូដ ៦ ខ្ទង់' : '6-digit code'}
                value={otpCode} onChange={(e) => setOtpCode(e.target.value)}
                style={inputStyle} {...focusHandlers} />
              <input name="password" type="password" placeholder={isKhmer ? 'ពាក្យសម្ងាត់ថ្មី' : 'New password'}
                value={form.password} onChange={handle}
                autoComplete="new-password"
                style={inputStyle} {...focusHandlers} />
              <input name="confirm" type="password" placeholder={isKhmer ? 'បញ្ជាក់ពាក្យសម្ងាត់' : 'Confirm new password'}
                value={form.confirm} onChange={handle}
                autoComplete="new-password"
                style={inputStyle} {...focusHandlers} />
            </>
          )}
        </div>

        {/* Forgot method toggle */}
        {isForgot && (
          <div className="flex justify-center mt-3">
            <button type="button" onClick={() => { setForgotMethod(forgotMethod === 'email' ? 'phone' : 'email'); setError(''); setSuccess('') }}
              className="font-semibold hover:underline"
              style={{ fontSize: 13, color: '#0088cc' }}>
              {forgotMethod === 'email'
                ? (isKhmer ? '📱 ប្រើលេខទូរស័ព្ទជំនួស' : '📱 Use phone instead')
                : (isKhmer ? '✉️ ប្រើអ៊ីមែលជំនួស' : '✉️ Use email instead')}
            </button>
          </div>
        )}

        {/* Forgot link */}
        {isLogin && (
          <div className="flex justify-end mt-2">
            <button onClick={() => handleSwitch('forgot')}
              className="font-semibold hover:underline"
              style={{ fontSize: 14, color: '#F97316' }}>
              {isKhmer ? 'ភ្លេចពាក្យសម្ងាត់?' : 'Forgot password?'}
            </button>
          </div>
        )}

        {/* Messages */}
        {error   && (
          <p className="text-red-500 mt-3 text-center" style={{ fontSize: 15 }}>
            {error}
            {cooldownSecs > 0 && (
              <span className="block mt-1" style={{ fontWeight: 800, color: '#F97316', fontSize: 18 }}>
                ⏳ {fmtCooldown(cooldownSecs)}
              </span>
            )}
          </p>
        )}
        {success && <p className="text-green-500 mt-3 text-center font-semibold" style={{ fontSize: 15 }}>{success}</p>}

        {/* Submit */}
        <button
          onClick={submit}
          disabled={loading || cooldown || cooldownSecs > 0}
          className="w-full mt-5 rounded-full py-3 font-bold transition-all shadow disabled:opacity-50 disabled:cursor-not-allowed"
          style={{
            fontFamily: authFont, fontSize: 18,
            background: c.btnBg, border: `1px solid ${c.btnBorder}`, color: c.btnText,
          }}
          onMouseEnter={e => { e.currentTarget.style.background = '#F97316'; e.currentTarget.style.borderColor = '#F97316'; e.currentTarget.style.color = '#fff' }}
          onMouseLeave={e => { e.currentTarget.style.background = c.btnBg;   e.currentTarget.style.borderColor = c.btnBorder; e.currentTarget.style.color = c.btnText }}
        >
          {loading || phoneBusy ? '…' : isReset
            ? (isKhmer ? 'កំណត់ពាក្យសម្ងាត់ថ្មី' : 'Reset Password')
            : isForgot
              ? (forgotMethod === 'phone'
                  ? (isKhmer ? 'កំណត់ពាក្យសម្ងាត់ឡើងវិញ' : 'Reset Password')
                  : (isKhmer ? 'ផ្ញើតំណកំណត់ឡើងវិញ' : 'Send Reset Link'))
              : isLogin
                ? (isKhmer ? 'ចូលគណនី' : 'Login')
                : (isKhmer ? 'ចុះឈ្មោះ' : 'Register')}
        </button>

        {(isForgot || isReset) && (
          <button onClick={() => handleSwitch('login')}
            className="w-full mt-3 font-semibold transition-colors"
            style={{ fontSize: 14, color: c.textMuted }}
            onMouseEnter={e => { e.currentTarget.style.color = '#F97316' }}
            onMouseLeave={e => { e.currentTarget.style.color = c.textMuted }}
          >{isKhmer ? '← ត្រឡប់ទៅចូលគណនី' : '← Back to Login'}</button>
        )}
      </div>
    </div>
  )
}