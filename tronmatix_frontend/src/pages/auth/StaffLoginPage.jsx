/**
 * src/pages/auth/StaffLoginPage.jsx
 */
import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../../context/AuthContext'
import api from '../../lib/axios'                  // ADD: direct api call

// Matches Staff::ROLES excluding developer
const STAFF_ROLES = ['editor', 'seller', 'delivery']  // UPDATED roles

export default function StaffLoginPage() {
  const navigate = useNavigate()
  const { user, staffLogin, loading } = useAuth() 

  const [form,     setForm]     = useState({ email: '', password: '' })
  const [error,    setError]    = useState(null)
  const [showPass, setShowPass] = useState(false)

  useEffect(() => {
    if (user && STAFF_ROLES.includes(user.role) && window.location.pathname !== '/staff/dashboard') {
      navigate('/staff/dashboard', { replace: true })
    }
  }, [user, navigate])

  const handle = (e) =>
    setForm(prev => ({ ...prev, [e.target.name]: e.target.value }))

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError(null)

    const result = await staffLogin(form.email, form.password)
    
    if (result.success) {
      navigate('/staff/dashboard', { replace: true })
    } else {
      setError(result.message)
    }
  }

  return (
    <div style={s.root}>
      <div style={s.grid} />
      <div style={s.card}>
        <div style={s.header}>
          <span style={s.badge}>STAFF PORTAL</span>
          <h1 style={s.title}>Tron<span style={s.orange}>matix</span></h1>
          <p style={s.sub}>Sign in to access the staff dashboard</p>
        </div>

        {error && <div style={s.err}>⚠ {error}</div>}

        <form onSubmit={handleSubmit} style={s.form}>
          <div style={s.field}>
            <label style={s.label}>Email Address</label>
            <input
              type="email" name="email"
              value={form.email} onChange={handle}
              required placeholder="staff@tronmatix.com"
              style={s.input}
              onFocus={e => (e.target.style.borderColor = '#F97316')}
              onBlur={e  => (e.target.style.borderColor = '#374151')}
            />
          </div>

          <div style={s.field}>
            <label style={s.label}>Password</label>
            <div style={{ position: 'relative' }}>
              <input
                type={showPass ? 'text' : 'password'} name="password"
                value={form.password} onChange={handle}
                required placeholder="••••••••"
                style={{ ...s.input, paddingRight: 44 }}
                onFocus={e => (e.target.style.borderColor = '#F97316')}
                onBlur={e  => (e.target.style.borderColor = '#374151')}
              />
              <button type="button" onClick={() => setShowPass(p => !p)} style={s.eye} tabIndex={-1}>
                {showPass ? '🙈' : '👁️'}
              </button>
            </div>
          </div>

          <button type="submit" disabled={loading} style={{ ...s.btn, opacity: loading ? 0.6 : 1 }}>
            {loading ? <span style={s.spin} /> : 'Sign In to Staff Portal'}
          </button>
        </form>

        <p style={s.note}>🔒 Restricted to authorised Tronmatix staff only.</p>
      </div>
      <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
    </div>
  )
}

const s = {
  root: { minHeight: '100vh', display: 'flex', alignItems: 'center', justifyContent: 'center', background: '#0a0f1a', padding: 16, position: 'relative', overflow: 'hidden' },
  grid: { position: 'absolute', inset: 0, pointerEvents: 'none', backgroundImage: 'linear-gradient(rgba(249,115,22,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(249,115,22,0.04) 1px,transparent 1px)', backgroundSize: '40px 40px' },
  card: { position: 'relative', width: '100%', maxWidth: 420, background: '#111827', border: '1px solid #1f2937', borderRadius: 16, padding: '40px 36px', boxShadow: '0 25px 60px rgba(0,0,0,0.6),0 0 0 1px rgba(249,115,22,0.08)' },
  header: { textAlign: 'center', marginBottom: 32 },
  badge: { display: 'inline-block', fontSize: 11, fontWeight: 700, letterSpacing: '0.15em', color: '#F97316', background: 'rgba(249,115,22,0.1)', border: '1px solid rgba(249,115,22,0.25)', borderRadius: 20, padding: '4px 14px', marginBottom: 12 },
  title: { fontSize: 28, fontWeight: 800, color: '#f9fafb', margin: '0 0 6px', fontFamily: 'Rajdhani,sans-serif' },
  orange: { color: '#F97316' },
  sub: { fontSize: 13, color: '#6b7280', margin: 0 },
  err: { display: 'flex', alignItems: 'center', gap: 8, background: 'rgba(239,68,68,0.1)', border: '1px solid rgba(239,68,68,0.3)', borderRadius: 8, padding: '10px 14px', color: '#fca5a5', fontSize: 13, marginBottom: 20 },
  form: { display: 'flex', flexDirection: 'column', gap: 18 },
  field: { display: 'flex', flexDirection: 'column', gap: 6 },
  label: { fontSize: 13, fontWeight: 500, color: '#9ca3af' },
  input: { width: '100%', padding: '11px 14px', background: '#1f2937', border: '1px solid #374151', borderRadius: 8, color: '#f9fafb', fontSize: 14, outline: 'none', transition: 'border-color 0.2s', boxSizing: 'border-box' },
  eye: { position: 'absolute', right: 12, top: '50%', transform: 'translateY(-50%)', background: 'none', border: 'none', cursor: 'pointer', fontSize: 16 },
  btn: { marginTop: 4, padding: '12px 0', background: 'linear-gradient(135deg,#F97316,#ea6d0e)', border: 'none', borderRadius: 8, color: '#fff', fontSize: 15, fontWeight: 700, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', transition: 'opacity 0.2s' },
  spin: { display: 'inline-block', width: 18, height: 18, border: '2px solid rgba(255,255,255,0.3)', borderTopColor: '#fff', borderRadius: '50%', animation: 'spin 0.7s linear infinite' },
  note: { marginTop: 24, fontSize: 12, color: '#4b5563', textAlign: 'center' },
}