import { useState } from 'react'
import { useAuth } from '../context/AuthContext'
import { useTheme } from '../context/ThemeContext'
import logo from '../assets/logo.png'

// mode: 'login' | 'register' | 'forgot'
export default function AuthModal({ mode, onClose, onSwitch }) {
  const { login, register, forgotPassword, loading } = useAuth()
  const { dark } = useTheme()
  const [form, setForm]           = useState({ usernameOrEmail: '', username: '', email: '', password: '', confirm: '' })
  const [error, setError]         = useState('')
  const [success, setSuccess]     = useState('')
  const [failCount, setFailCount] = useState(0)
  const [cooldown, setCooldown]   = useState(false)
  // Track password strength to give inline hints
  const [pwStrength, setPwStrength] = useState({ score: 0, hints: [] })

  const isLogin    = mode === 'login'
  const isRegister = mode === 'register'
  const isForgot   = mode === 'forgot'

  const c = {
    modalBg:      dark ? '#1f2937' : '#ffffff',
    text:         dark ? '#f9fafb' : '#1f2937',
    textMuted:    dark ? '#9ca3af' : '#6b7280',
    inputBg:      dark ? '#111827' : '#f9fafb',
    inputBorder:  dark ? '#374151' : '#e5e7eb',
    tabBg:        dark ? '#111827' : '#f3f4f6',
    tabActive:    dark ? '#1f2937' : '#ffffff',
    closeBtn:     dark ? '#6b7280' : '#9ca3af',
    closeBtnHover:dark ? '#f9fafb' : '#1f2937',
    btnBg:        dark ? '#111827' : '#ffffff',
    btnBorder:    dark ? '#374151' : '#e5e7eb',
    btnText:      dark ? '#f9fafb' : '#1f2937',
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
    if (name === 'password' && isRegister) {
      setPwStrength(checkPasswordStrength(value))
    }
  }

  // ── Password strength checker (mirrors backend rules) ──────────────────────
  // Backend requires: min 8, mixed case, numbers, symbols
  function checkPasswordStrength(pw) {
    const hints = []
    if (pw.length < 8)              hints.push('At least 8 characters')
    if (!/[A-Z]/.test(pw))          hints.push('One uppercase letter (A-Z)')
    if (!/[a-z]/.test(pw))          hints.push('One lowercase letter (a-z)')
    if (!/[0-9]/.test(pw))          hints.push('One number (0-9)')
    if (!/[^A-Za-z0-9]/.test(pw))   hints.push('One symbol (e.g. @, #, !)')
    const score = 5 - hints.length  // 0–5
    return { score, hints }
  }

  const strengthColors = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#16a34a']
  const strengthLabels = ['Very weak', 'Weak', 'Fair', 'Strong', 'Very strong']

  // ── Client-side validators ─────────────────────────────────────────────────
  const validEmail    = (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)
  const validUsername = (v) => /^[a-zA-Z0-9_]{3,50}$/.test(v)
  const validPassword = (v) => {
    // Must match all backend rules
    return v.length >= 8
      && /[A-Z]/.test(v)
      && /[a-z]/.test(v)
      && /[0-9]/.test(v)
      && /[^A-Za-z0-9]/.test(v)
  }

  // ── Cooldown after 5 consecutive failures ──────────────────────────────────
  const recordFailure = () => {
    const next = failCount + 1
    setFailCount(next)
    if (next >= 5) {
      setCooldown(true)
      setError('Too many attempts. Please wait 30 seconds.')
      setTimeout(() => { setCooldown(false); setFailCount(0); setError('') }, 30_000)
    }
  }

  /**
   * Switch mode and optionally prefill the login form.
   * When called after successful register, `prefill.email` is the Gmail/email
   * the user just registered with — so they can log in without retyping.
   */
  const handleSwitch = (newMode, prefill = {}) => {
    setForm({
      usernameOrEmail: prefill.email || prefill.username || '',
      username: '',
      email: '',
      password: '',
      confirm: '',
    })
    setError('')
    setSuccess('')
    setPwStrength({ score: 0, hints: [] })
    onSwitch(newMode)
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

    // ── Forgot password ──────────────────────────────────────────────────────
    if (isForgot) {
      if (!f.email)             { setError('Please enter your email address.'); return }
      if (!validEmail(f.email)) { setError('Please enter a valid email address.'); return }
      const res = await forgotPassword(f.email)
      if (res.success) setSuccess(res.message)
      else { setError(res.message); recordFailure() }
      return
    }

    // ── Login ────────────────────────────────────────────────────────────────
    if (isLogin) {
      if (!f.usernameOrEmail || !f.password) { setError('Please fill all fields.'); return }
      const res = await login(f.usernameOrEmail, f.password)
      if (res.success) { setSuccess('Login successful!'); setTimeout(onClose, 800) }
      else { setError(res.message); recordFailure() }
      return
    }

    // ── Register ─────────────────────────────────────────────────────────────
    if (!f.username || !f.email || !f.password) { setError('Please fill all required fields.'); return }
    if (!validUsername(f.username)) {
      setError('Username must be 3–50 characters: letters, numbers, underscores only.')
      return
    }
    if (!validEmail(f.email)) {
      setError('Please enter a valid email address.')
      return
    }
    if (!validPassword(f.password)) {
      setError('Password must be at least 8 characters with uppercase, lowercase, a number, and a symbol.')
      return
    }
    if (f.password !== f.confirm) {
      setError('Passwords do not match.')
      return
    }

    const res = await register(f.username, f.email, f.password, f.confirm)
    if (res.success) {
      setSuccess('Account created! Redirecting to login…')
      // Prefill the login form with the email they registered with.
      // If they used a Gmail address, it'll be ready to type the password and go.
      setTimeout(() => handleSwitch('login', { email: res.email }), 1200)
    } else {
      setError(res.message)
      recordFailure()
    }
  }

  const onKeyDown = (e) => { if (e.key === 'Enter') submit() }

  return (
    <div
      className="fixed inset-0 z-[100] flex items-center justify-center"
      style={{ background: 'rgba(0,0,0,0.55)', backdropFilter: 'blur(4px)' }}
      onClick={(e) => { if (e.target === e.currentTarget) onClose() }}
    >
      <div
        className="rounded-3xl shadow-2xl w-full max-w-[400px] mx-4 p-8 relative"
        style={{ background: c.modalBg }}
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

        {/* Fields */}
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
