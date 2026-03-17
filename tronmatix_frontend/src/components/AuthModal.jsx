import { useState } from 'react'
import { useAuth } from '../context/AuthContext'
import { useTheme } from '../context/ThemeContext'
import logo from '../assets/logo.png'

// mode: 'login' | 'register' | 'forgot'
export default function AuthModal({ mode, onClose, onSwitch }) {
  const { login, register, forgotPassword, loading } = useAuth()
  const { dark } = useTheme()
  const [form, setForm]       = useState({ username: '', email: '', password: '', confirm: '' })
  const [error, setError]     = useState('')
  const [success, setSuccess] = useState('')

  const isLogin    = mode === 'login'
  const isRegister = mode === 'register'
  const isForgot   = mode === 'forgot'

  // Theme tokens
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

  const inputClass = `w-full rounded-xl px-4 py-3 focus:outline-none transition-colors`
  const inputStyle = {
    fontSize: 16,
    background: c.inputBg,
    border: `1px solid ${c.inputBorder}`,
    color: c.text,
  }
  // Inline focus ring via onFocus/onBlur since we can't use Tailwind's focus: with dynamic styles
  const focusHandlers = {
    onFocus: (e) => { e.target.style.borderColor = '#F97316' },
    onBlur:  (e) => { e.target.style.borderColor = c.inputBorder },
  }

  const handle = (e) => setForm(prev => ({ ...prev, [e.target.name]: e.target.value }))

  const handleSwitch = (newMode) => {
    setForm({ username: '', email: '', password: '', confirm: '' })
    setError('')
    setSuccess('')
    onSwitch(newMode)
  }

  // ── Submit ─────────────────────────────────────────────────────────────────
  const submit = async () => {
    setError('')
    setSuccess('')

    if (isForgot) {
      if (!form.email) { setError('Please enter your email address'); return }
      const res = await forgotPassword(form.email)
      if (res.success) setSuccess(res.message || 'Password reset link sent! Check your email.')
      else setError(res.message)
      return
    }

    if (isLogin) {
      if (!form.username || !form.password) { setError('Please fill all fields'); return }
      const res = await login(form.username, form.password)
      if (res.success) { setSuccess('Login successful!'); setTimeout(onClose, 800) }
      else setError(res.message)
      return
    }

    // Register
    if (!form.username || !form.email || !form.password) { setError('Please fill all fields'); return }
    if (form.password !== form.confirm) { setError('Passwords do not match'); return }
    const res = await register(form.username, form.email, form.password, form.confirm)
    if (res.success) { setSuccess('Registered!'); setTimeout(onClose, 800) }
    else setError(res.message)
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
        {/* Close button */}
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

        {/* Tabs: Login / Register */}
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

        {/* Forgot password header */}
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
              <input name="username" placeholder="Username" value={form.username} onChange={handle}
                className={inputClass} style={inputStyle} {...focusHandlers} />
              <input name="password" type="password" placeholder="Password" value={form.password} onChange={handle}
                className={inputClass} style={inputStyle} {...focusHandlers} />
            </>
          )}
          {isRegister && (
            <>
              <input name="username" placeholder="Username" value={form.username} onChange={handle}
                className={inputClass} style={inputStyle} {...focusHandlers} />
              <input name="email" type="email" placeholder="Email" value={form.email} onChange={handle}
                className={inputClass} style={inputStyle} {...focusHandlers} />
              <input name="password" type="password" placeholder="Password" value={form.password} onChange={handle}
                className={inputClass} style={inputStyle} {...focusHandlers} />
              <input name="confirm" type="password" placeholder="Confirm Password" value={form.confirm} onChange={handle}
                className={inputClass} style={inputStyle} {...focusHandlers} />
            </>
          )}
          {isForgot && (
            <input name="email" type="email" placeholder="Your email address" value={form.email} onChange={handle}
              className={inputClass} style={inputStyle} {...focusHandlers} />
          )}
        </div>

        {/* Forgot password link */}
        {isLogin && (
          <div className="flex justify-end mt-2">
            <button
              onClick={() => handleSwitch('forgot')}
              className="font-semibold hover:underline transition-colors"
              style={{ fontSize: 14, color: '#F97316' }}
            >Forgot password?</button>
          </div>
        )}

        {/* Messages */}
        {error   && <p className="text-red-500 mt-3 text-center"          style={{ fontSize: 15 }}>{error}</p>}
        {success && <p className="text-green-500 mt-3 text-center font-semibold" style={{ fontSize: 15 }}>{success}</p>}

        {/* Main button */}
        <button
          onClick={submit}
          disabled={loading}
          className="w-full mt-5 rounded-full py-3 font-bold transition-all shadow disabled:opacity-50"
          style={{
            fontFamily: 'Rajdhani, sans-serif', fontSize: 18,
            background: c.btnBg,
            border: `1px solid ${c.btnBorder}`,
            color: c.btnText,
          }}
          onMouseEnter={(e) => {
            e.currentTarget.style.background = '#F97316'
            e.currentTarget.style.borderColor = '#F97316'
            e.currentTarget.style.color = '#ffffff'
          }}
          onMouseLeave={(e) => {
            e.currentTarget.style.background = c.btnBg
            e.currentTarget.style.borderColor = c.btnBorder
            e.currentTarget.style.color = c.btnText
          }}
        >
          {loading ? '…' : isForgot ? 'Send Reset Link' : isLogin ? 'Login' : 'Register'}
        </button>

        {/* Back to login */}
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
