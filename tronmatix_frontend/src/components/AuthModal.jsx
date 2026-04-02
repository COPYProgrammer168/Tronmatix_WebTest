import { useState, useEffect, useRef } from 'react'
import { useAuth } from '../context/AuthContext'
import { useTheme } from '../context/ThemeContext'
import api from '../lib/axios'
import logo from '../assets/logo.png'

const BOT_USERNAME = import.meta.env.VITE_TELEGRAM_BOT_USERNAME || ''
const GOOGLE_CLIENT_ID = import.meta.env.VITE_GOOGLE_CLIENT_ID || ''

// mode: 'login' | 'register' | 'forgot'
export default function AuthModal({ mode, onClose, onSwitch }) {
  const { login, register, forgotPassword, loading, applyToken, applyUser } = useAuth()
  const { dark } = useTheme()
  const [form, setForm]           = useState({ usernameOrEmail: '', username: '', email: '', password: '', confirm: '' })
  const [error, setError]         = useState('')
  const [success, setSuccess]     = useState('')
  const [failCount, setFailCount] = useState(0)
  const [cooldown, setCooldown]   = useState(false)
  const [pwStrength, setPwStrength] = useState({ score: 0, hints: [] })
  const [socialLoading, setSocialLoading] = useState(null) // 'google' | 'telegram'
  const tgContainerRef = useRef(null)

  const isLogin    = mode === 'login'
  const isRegister = mode === 'register'
  const isForgot   = mode === 'forgot'
  const showSocial = isLogin

  const c = {
    modalBg:       dark ? '#1f2937' : '#ffffff',
    text:          dark ? '#f9fafb' : '#1f2937',
    textMuted:     dark ? '#9ca3af' : '#6b7280',
    inputBg:       dark ? '#111827' : '#f9fafb',
    inputBorder:   dark ? '#374151' : '#e5e7eb',
    tabBg:         dark ? '#111827' : '#f3f4f6',
    tabActive:     dark ? '#1f2937' : '#ffffff',
    closeBtn:      dark ? '#6b7280' : '#9ca3af',
    closeBtnHover: dark ? '#f9fafb' : '#1f2937',
    btnBg:         dark ? '#111827' : '#ffffff',
    btnBorder:     dark ? '#374151' : '#e5e7eb',
    btnText:       dark ? '#f9fafb' : '#1f2937',
    divider:       dark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)',
    socialBg:      dark ? '#111827' : '#f9fafb',
    socialBorder:  dark ? '#374151' : '#e5e7eb',
    socialHover:   dark ? '#1f2937' : '#f3f4f6',
  }

  const inputClass = 'w-full rounded-xl px-4 py-3 focus:outline-none transition-colors'
  const inputStyle = { fontSize: 16, background: c.inputBg, border: `1px solid ${c.inputBorder}`, color: c.text }
  const focusHandlers = {
    onFocus: (e) => { e.target.style.borderColor = '#F97316' },
    onBlur:  (e) => { e.target.style.borderColor = c.inputBorder },
  }

  const handle = (e) => {
    const { name, value } = e.target
    setForm(prev => ({ ...prev, [name]: value }))
    if (name === 'password' && isRegister) setPwStrength(checkPasswordStrength(value))
  }

  function checkPasswordStrength(pw) {
    const hints = []
    if (pw.length < 8)              hints.push('At least 8 characters')
    if (!/[A-Z]/.test(pw))          hints.push('One uppercase letter (A-Z)')
    if (!/[a-z]/.test(pw))          hints.push('One lowercase letter (a-z)')
    if (!/[0-9]/.test(pw))          hints.push('One number (0-9)')
    if (!/[^A-Za-z0-9]/.test(pw))   hints.push('One symbol (e.g. @, #, !)')
    const score = 5 - hints.length
    return { score, hints }
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
    onSwitch(newMode)
  }

  // ── Google OAuth ─────────────────────────────────────────────────────────────
  // Uses Google Identity Services (GSI) popup flow.
  // Backend endpoint: POST /api/auth/google  → { token, user }
  useEffect(() => {
    if (!showSocial || !GOOGLE_CLIENT_ID) return

    // Load GSI script once
    if (!document.getElementById('gsi-script')) {
      const s = document.createElement('script')
      s.id  = 'gsi-script'
      s.src = 'https://accounts.google.com/gsi/client'
      s.async = true
      document.head.appendChild(s)
    }
  }, [showSocial])

  const handleGoogleLogin = () => {
    if (!GOOGLE_CLIENT_ID) {
      setError('Google login is not configured. Set VITE_GOOGLE_CLIENT_ID.')
      return
    }
    if (typeof window.google === 'undefined') {
      setError('Google SDK not loaded yet. Please wait a moment and try again.')
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
        try {
          const res = await api.post('/api/auth/google', {
            access_token: tokenResponse.access_token,
          })
          const t = res.data?.token ?? res.data?.data?.token
          const u = res.data?.user ?? res.data?.data?.user ?? res.data?.data
          if (!t || !u) throw new Error('Unexpected response shape')
          // applyToken / applyUser come from AuthContext
          // If your AuthContext does not export these directly,
          // call login() with a special flag or handle via a dedicated hook.
          // For now we dispatch a custom event the AuthContext can listen to.
          window.dispatchEvent(new CustomEvent('auth:social-login', { detail: { token: t, user: u } }))
          setSuccess('Signed in with Google!')
          setTimeout(onClose, 700)
        } catch (e) {
          setError(e.response?.data?.message || 'Google sign-in failed. Please try again.')
          recordFailure()
        } finally {
          setSocialLoading(null)
        }
      },
    }).requestAccessToken()
  }

  // ── Telegram Login Widget ────────────────────────────────────────────────────
  // Injects the official Telegram Login Widget into a container div.
  // On auth callback → POST /api/auth/telegram → { token, user }
  useEffect(() => {
    if (!showSocial || !BOT_USERNAME) return
    const container = tgContainerRef.current
    if (!container) return

    container.innerHTML = ''

    window.onTelegramAuthModal = async (tgUser) => {
      if (!tgUser) return
      setSocialLoading('telegram')
      setError('')
      try {
        const res = await api.post('/api/auth/telegram', tgUser)
        const t = res.data?.token ?? res.data?.data?.token
        const u = res.data?.user ?? res.data?.data?.user ?? res.data?.data
        if (!t || !u) throw new Error('Unexpected response shape')
        window.dispatchEvent(new CustomEvent('auth:social-login', { detail: { token: t, user: u } }))
        setSuccess('Signed in with Telegram!')
        setTimeout(onClose, 700)
      } catch (e) {
        setError(e.response?.data?.message || 'Telegram sign-in failed. Please try again.')
        recordFailure()
      } finally {
        setSocialLoading(null)
      }
    }

    const script = document.createElement('script')
    script.src = 'https://telegram.org/js/telegram-widget.js?22'
    script.async = true
    script.setAttribute('data-telegram-login', BOT_USERNAME)
    script.setAttribute('data-size',           'large')
    script.setAttribute('data-radius',         '10')
    script.setAttribute('data-onauth',         'onTelegramAuthModal(user)')
    script.setAttribute('data-request-access', 'write')
    container.appendChild(script)

    return () => {
      delete window.onTelegramAuthModal
      if (container) container.innerHTML = ''
    }
  }, [showSocial, mode]) // re-inject when mode switches between login/register

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

    if (isForgot) {
      if (!f.email)             { setError('Please enter your email address.'); return }
      if (!validEmail(f.email)) { setError('Please enter a valid email address.'); return }
      const res = await forgotPassword(f.email)
      if (res.success) setSuccess(res.message)
      else { setError(res.message); recordFailure() }
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
    if (!validPassword(f.password)) { setError('Password must be at least 8 characters with uppercase, lowercase, a number, and a symbol.'); return }
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

  const onKeyDown = (e) => { if (e.key === 'Enter') submit() }

  // ── Social button shared style ─────────────────────────────────────────────
  const socialBtnBase = {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 10,
    width: '100%',
    padding: '11px 16px',
    borderRadius: 12,
    border: `1px solid ${c.socialBorder}`,
    background: c.socialBg,
    color: c.text,
    fontSize: 15,
    fontWeight: 700,
    fontFamily: 'Rajdhani, sans-serif',
    letterSpacing: 0.5,
    cursor: 'pointer',
    transition: 'background 0.15s, border-color 0.15s',
  }

  return (
    <div
      className="fixed inset-0 z-[100] flex items-center justify-center"
      style={{ background: 'rgba(0,0,0,0.55)', backdropFilter: 'blur(4px)' }}
      onClick={(e) => { if (e.target === e.currentTarget) onClose() }}
    >
      <div
        className="rounded-3xl shadow-2xl w-full mx-4 relative"
        style={{
          background: c.modalBg,
          maxWidth: 400,
          maxHeight: '90vh',
          overflowY: 'auto',
          padding: '32px',
          scrollbarWidth: 'none',
        }}
      >
        {/* Close */}
        <button
          onClick={onClose}
          className="absolute top-4 right-4 text-xl font-bold transition-colors"
          style={{ color: c.closeBtn }}
          onMouseEnter={(e) => { e.currentTarget.style.color = c.closeBtnHover }}
          onMouseLeave={(e) => { e.currentTarget.style.color = c.closeBtn }}
        >✕</button>

        {/* Logo */}
        <div className="flex flex-col items-center mb-6">
          <img src={logo} alt="Tronmatix" className="h-14 mb-1" />
        </div>

        {/* Tabs */}
        {!isForgot && (
          <div className="flex rounded-full p-1 mb-6" style={{ background: c.tabBg }}>
            {[['login', 'LOGIN'], ['register', 'REGISTER']].map(([val, label]) => (
              <button
                key={val}
                onClick={() => handleSwitch(val)}
                className="flex-1 py-2 rounded-full font-bold tracking-wider transition-all"
                style={{
                  fontFamily: 'Rajdhani, sans-serif', fontSize: 18,
                  background: mode === val ? c.tabActive : 'transparent',
                  color: mode === val ? '#F97316' : c.textMuted,
                  boxShadow: mode === val ? '0 1px 4px rgba(0,0,0,0.15)' : 'none',
                }}
              >{label}</button>
            ))}
          </div>
        )}

        {/* Forgot header */}
        {isForgot && (
          <div className="mb-6 text-center">
            <div className="text-4xl mb-2">🔐</div>
            <h2 className="font-black" style={{ fontFamily: 'Rajdhani, sans-serif', fontSize: 24, color: c.text }}>
              FORGOT PASSWORD
            </h2>
            <p className="mt-1" style={{ fontSize: 14, color: c.textMuted }}>
              Enter your email and we'll send you a reset link.
            </p>
          </div>
        )}

        {/* ── Social Login Buttons (above form, login + register only) ────── */}
        {showSocial && (
          <div className="mb-5" style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>

            {/* Google */}
            <button
              style={socialBtnBase}
              disabled={!!socialLoading || loading}
              onClick={handleGoogleLogin}
              onMouseEnter={(e) => { e.currentTarget.style.background = c.socialHover; e.currentTarget.style.borderColor = '#4285F4' }}
              onMouseLeave={(e) => { e.currentTarget.style.background = c.socialBg;    e.currentTarget.style.borderColor = c.socialBorder }}
            >
              {socialLoading === 'google' ? (
                <span style={{ fontSize: 14, color: c.textMuted }}>Connecting…</span>
              ) : (
                <>
                  {/* Google "G" SVG */}
                  <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                  </svg>
                  <span style={{ color: c.text }}>Continue with Google</span>
                </>
              )}
            </button>

            {/* Telegram — injects official widget into hidden div, shows styled button */}
            {BOT_USERNAME ? (
              <div style={{ position: 'relative' }}>
                {/* The actual Telegram widget is hidden; we show our own styled button
                    which programmatically clicks the real widget button */}
                <button
                  style={{
                    ...socialBtnBase,
                    opacity: socialLoading === 'telegram' ? 0.6 : 1,
                  }}
                  disabled={!!socialLoading || loading}
                  onClick={() => {
                    // Click the hidden telegram widget button
                    const btn = tgContainerRef.current?.querySelector('button, [role="button"], a')
                    if (btn) btn.click()
                    else {
                      // Fallback: widget may not be loaded yet
                      setError('Telegram widget is loading. Please try again in a moment.')
                    }
                  }}
                  onMouseEnter={(e) => { e.currentTarget.style.background = c.socialHover; e.currentTarget.style.borderColor = '#229ED9' }}
                  onMouseLeave={(e) => { e.currentTarget.style.background = c.socialBg;    e.currentTarget.style.borderColor = c.socialBorder }}
                >
                  {socialLoading === 'telegram' ? (
                    <span style={{ fontSize: 14, color: c.textMuted }}>Connecting…</span>
                  ) : (
                    <>
                      {/* Telegram plane icon */}
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="12" fill="#229ED9"/>
                        <path d="M5.5 11.5l10-4-3 10-2.5-3.5L5.5 11.5z" fill="white"/>
                        <path d="M10 14l-.7 3.5L12 15.5" fill="white"/>
                        <path d="M10 14l1.5 1.5 4-5" stroke="white" strokeWidth="0.5" fill="none"/>
                      </svg>
                      <span style={{ color: c.text }}>Continue with Telegram</span>
                    </>
                  )}
                </button>

                {/* Hidden real Telegram widget — provides the actual auth popup */}
                <div
                  ref={tgContainerRef}
                  style={{
                    position: 'absolute',
                    opacity: 0,
                    pointerEvents: 'none',
                    top: 0,
                    left: 0,
                    width: 1,
                    height: 1,
                    overflow: 'hidden',
                  }}
                />
              </div>
            ) : (
              // Telegram not configured — show disabled placeholder
              <button
                style={{ ...socialBtnBase, opacity: 0.4, cursor: 'not-allowed' }}
                disabled
                title="Set VITE_TELEGRAM_BOT_USERNAME to enable"
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                  <circle cx="12" cy="12" r="12" fill="#229ED9"/>
                  <path d="M5.5 11.5l10-4-3 10-2.5-3.5L5.5 11.5z" fill="white"/>
                </svg>
                <span style={{ color: c.textMuted }}>Telegram (not configured)</span>
              </button>
            )}

            {/* Divider */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 10, margin: '4px 0 2px' }}>
              <div style={{ flex: 1, height: 1, background: c.divider }} />
              <span style={{ fontSize: 11, fontWeight: 700, color: c.textMuted, letterSpacing: 1 }}>OR</span>
              <div style={{ flex: 1, height: 1, background: c.divider }} />
            </div>
          </div>
        )}

        {/* ── Fields ──────────────────────────────────────────────────────── */}
        <div className="space-y-3" onKeyDown={onKeyDown}>
          {isLogin && (
            <>
              <input
                name="usernameOrEmail"
                placeholder="Username or Email"
                value={form.usernameOrEmail}
                onChange={handle}
                autoComplete="username email"
                className={inputClass} style={inputStyle} {...focusHandlers}
              />
              <input
                name="password"
                type="password"
                placeholder="Password"
                value={form.password}
                onChange={handle}
                autoComplete="current-password"
                className={inputClass} style={inputStyle} {...focusHandlers}
              />
            </>
          )}

          {isRegister && (
            <>
              <input
                name="username"
                placeholder="Username"
                value={form.username}
                onChange={handle}
                autoComplete="username"
                className={inputClass} style={inputStyle} {...focusHandlers}
              />
              <input
                name="email"
                type="email"
                placeholder="Email (Gmail or any address)"
                value={form.email}
                onChange={handle}
                autoComplete="email"
                className={inputClass} style={inputStyle} {...focusHandlers}
              />
              <input
                name="password"
                type="password"
                placeholder="Password"
                value={form.password}
                onChange={handle}
                autoComplete="new-password"
                className={inputClass} style={inputStyle} {...focusHandlers}
              />

              {/* Password strength meter */}
              {form.password.length > 0 && (
                <div>
                  <div className="flex gap-1 mb-1">
                    {[1, 2, 3, 4, 5].map((i) => (
                      <div
                        key={i}
                        className="h-1 flex-1 rounded-full transition-all"
                        style={{
                          background: i <= pwStrength.score
                            ? strengthColors[pwStrength.score - 1]
                            : (dark ? '#374151' : '#e5e7eb'),
                        }}
                      />
                    ))}
                  </div>
                  {pwStrength.score < 5 && (
                    <p style={{ fontSize: 12, color: strengthColors[pwStrength.score - 1] || '#9ca3af' }}>
                      {pwStrength.score > 0 && `${strengthLabels[pwStrength.score - 1]} — `}
                      Still need: {pwStrength.hints.join(', ')}
                    </p>
                  )}
                  {pwStrength.score === 5 && (
                    <p style={{ fontSize: 12, color: '#16a34a' }}>Strong password ✓</p>
                  )}
                </div>
              )}

              <input
                name="confirm"
                type="password"
                placeholder="Confirm Password"
                value={form.confirm}
                onChange={handle}
                autoComplete="new-password"
                className={inputClass} style={inputStyle} {...focusHandlers}
              />
            </>
          )}

          {isForgot && (
            <input
              name="email"
              type="email"
              placeholder="Your email address"
              value={form.email}
              onChange={handle}
              autoComplete="email"
              className={inputClass} style={inputStyle} {...focusHandlers}
            />
          )}
        </div>

        {/* Forgot link */}
        {isLogin && (
          <div className="flex justify-end mt-2">
            <button
              onClick={() => handleSwitch('forgot')}
              className="font-semibold hover:underline"
              style={{ fontSize: 14, color: '#F97316' }}
            >Forgot password?</button>
          </div>
        )}

        {/* Messages */}
        {error   && <p className="text-red-500 mt-3 text-center"                style={{ fontSize: 15 }}>{error}</p>}
        {success && <p className="text-green-500 mt-3 text-center font-semibold" style={{ fontSize: 15 }}>{success}</p>}

        {/* Submit */}
        <button
          onClick={submit}
          disabled={loading || cooldown}
          className="w-full mt-5 rounded-full py-3 font-bold transition-all shadow disabled:opacity-50 disabled:cursor-not-allowed"
          style={{
            fontFamily: 'Rajdhani, sans-serif', fontSize: 18,
            background: c.btnBg,
            border: `1px solid ${c.btnBorder}`,
            color: c.btnText,
          }}
          onMouseEnter={(e) => {
            e.currentTarget.style.background  = '#F97316'
            e.currentTarget.style.borderColor = '#F97316'
            e.currentTarget.style.color       = '#ffffff'
          }}
          onMouseLeave={(e) => {
            e.currentTarget.style.background  = c.btnBg
            e.currentTarget.style.borderColor = c.btnBorder
            e.currentTarget.style.color       = c.btnText
          }}
        >
          {loading ? '…' : isForgot ? 'Send Reset Link' : isLogin ? 'Login' : 'Register'}
        </button>

        {isForgot && (
          <button
            onClick={() => handleSwitch('login')}
            className="w-full mt-3 font-semibold transition-colors"
            style={{ fontSize: 14, color: c.textMuted }}
            onMouseEnter={(e) => { e.currentTarget.style.color = '#F97316' }}
            onMouseLeave={(e) => { e.currentTarget.style.color = c.textMuted }}
          >← Back to Login</button>
        )}
      </div>
    </div>
  )
}
