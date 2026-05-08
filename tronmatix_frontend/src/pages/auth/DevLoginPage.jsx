/**
 * src/pages/auth/DevLoginPage.jsx
 *
 * Hidden developer login — accessible at /dev/login
 * NOT linked anywhere in the customer UI.
 * Extra security: requires dev_key in addition to credentials.
 * Calls /api/dev/login (separate endpoint from customer login).
 */
import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../../context/AuthContext'
import api from '../../lib/axios' // existing axios instance

export default function DevLoginPage() {
  const navigate = useNavigate()
  const { user } = useAuth()                          // line 16: remove applyToken/applyUser — not exported from AuthContext

  const [form,     setForm]     = useState({ email: '', password: '', dev_key: '' })
  const [error,    setError]    = useState(null)
  const [loading,  setLoading]  = useState(false)
  const [showPass, setShowPass] = useState(false)
  const [showKey,  setShowKey]  = useState(false)

  // Already logged in as developer → skip login
  useEffect(() => {
    if (user?.role === 'developer') {
      navigate('/dev/dashboard', { replace: true })
    }
  }, [user, navigate])

  const handle = (e) =>
    setForm(prev => ({ ...prev, [e.target.name]: e.target.value }))

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError(null)
    setLoading(true)

    try {
      // Call dedicated dev endpoint — validates dev_key server-side
      const res = await api.post('/api/dev/login', {
        email:    form.email,
        password: form.password,
        dev_key:  form.dev_key,
      })

      const token = res.data?.token
      const devUser = res.data?.user

      if (!token || !devUser) throw new Error('Unexpected response')
      if (devUser.role !== 'developer') {
        setError('Access denied. Developer accounts only.')
        return
      }

      // Store in localStorage — same keys as AuthContext
      localStorage.setItem('token', token)
      localStorage.setItem('tronmatix_user', JSON.stringify(devUser))
      api.defaults.headers.common['Authorization'] = `Bearer ${token}`

      navigate('/dev/dashboard', { replace: true })
    } catch (err) {
      const data = err.response?.data
      let msg = 'Login failed. Check your credentials and developer key.'
      if (data?.errors)       msg = Object.values(data.errors).flat()[0] || msg
      else if (data?.message) msg = data.message
      setError(msg)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div style={s.root}>
      <div style={s.grid} />

      <div style={s.card}>
        {/* Header */}
        <div style={s.header}>
          <span style={s.badge}>DEVELOPER PORTAL</span>
          <h1 style={s.title}>Tron<span style={s.blue}>matix</span></h1>
          <p style={s.sub}>Restricted — authorised developers only</p>
        </div>

        {error && <div style={s.err}>⚠ {error}</div>}

        <form onSubmit={handleSubmit} style={s.form}>
          {/* Email */}
          <div style={s.field}>
            <label style={s.label}>Email Address</label>
            <input
              type="email" name="email"
              value={form.email} onChange={handle}
              required placeholder="dev@tronmatix.com"
              style={s.input}
              onFocus={e => (e.target.style.borderColor = '#3b82f6')}
              onBlur={e  => (e.target.style.borderColor = '#374151')}
            />
          </div>

          {/* Password */}
          <div style={s.field}>
            <label style={s.label}>Password</label>
            <div style={{ position: 'relative' }}>
              <input
                type={showPass ? 'text' : 'password'} name="password"
                value={form.password} onChange={handle}
                required placeholder="••••••••"
                style={{ ...s.input, paddingRight: 44 }}
                onFocus={e => (e.target.style.borderColor = '#3b82f6')}
                onBlur={e  => (e.target.style.borderColor = '#374151')}
              />
              <button type="button" onClick={() => setShowPass(p => !p)} style={s.eye} tabIndex={-1}>
                {showPass ? '🙈' : '👁️'}
              </button>
            </div>
          </div>

          {/* Developer Key */}
          <div style={s.field}>
            <label style={s.label}>
              Developer Key <span style={{ color: '#3b82f6', fontWeight: 400 }}>— required</span>
            </label>
            <div style={{ position: 'relative' }}>
              <input
                type={showKey ? 'text' : 'password'} name="dev_key"
                value={form.dev_key} onChange={handle}
                required placeholder="Your secret developer key"
                style={{ ...s.input, paddingRight: 44, borderColor: '#1d4ed8' }}
                onFocus={e => (e.target.style.borderColor = '#3b82f6')}
                onBlur={e  => (e.target.style.borderColor = '#1d4ed8')}
              />
              <button type="button" onClick={() => setShowKey(p => !p)} style={s.eye} tabIndex={-1}>
                {showKey ? '🙈' : '👁️'}
              </button>
            </div>
            <p style={s.keyHint}>🔑 Contact system admin if you don't have a key.</p>
          </div>

          <button type="submit" disabled={loading} style={{ ...s.btn, opacity: loading ? 0.6 : 1 }}>
            {loading ? <span style={s.spin} /> : 'Access Developer Portal'}
          </button>
        </form>

        <p style={s.note}>
          🔒 Unauthorised access attempts are logged.
        </p>
      </div>

      <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
    </div>
  )
}

const s = {
  root: {
    minHeight: '100vh', display: 'flex', alignItems: 'center',
    justifyContent: 'center', background: '#060b18', padding: 16,
    position: 'relative', overflow: 'hidden',
  },
  grid: {
    position: 'absolute', inset: 0, pointerEvents: 'none',
    backgroundImage:
      'linear-gradient(rgba(59,130,246,0.05) 1px,transparent 1px),' +
      'linear-gradient(90deg,rgba(59,130,246,0.05) 1px,transparent 1px)',
    backgroundSize: '40px 40px',
  },
  card: {
    position: 'relative', width: '100%', maxWidth: 440,
    background: '#0d1117', border: '1px solid #1e2a3a', borderRadius: 16,
    padding: '40px 36px',
    boxShadow: '0 25px 60px rgba(0,0,0,0.7),0 0 0 1px rgba(59,130,246,0.08)',
  },
  header: { textAlign: 'center', marginBottom: 32 },
  badge: {
    display: 'inline-block', fontSize: 11, fontWeight: 700,
    letterSpacing: '0.15em', color: '#3b82f6',
    background: 'rgba(59,130,246,0.1)', border: '1px solid rgba(59,130,246,0.25)',
    borderRadius: 20, padding: '4px 14px', marginBottom: 12,
  },
  title: {
    fontSize: 28, fontWeight: 800, color: '#f0f6ff',
    margin: '0 0 6px', fontFamily: 'Rajdhani,sans-serif',
  },
  blue: { color: '#3b82f6' },
  sub:  { fontSize: 13, color: '#4b5563', margin: 0 },
  err: {
    display: 'flex', alignItems: 'center', gap: 8,
    background: 'rgba(239,68,68,0.1)', border: '1px solid rgba(239,68,68,0.3)',
    borderRadius: 8, padding: '10px 14px', color: '#fca5a5',
    fontSize: 13, marginBottom: 20,
  },
  form:    { display: 'flex', flexDirection: 'column', gap: 18 },
  field:   { display: 'flex', flexDirection: 'column', gap: 6 },
  label:   { fontSize: 13, fontWeight: 500, color: '#6b7280' },
  input: {
    width: '100%', padding: '11px 14px', background: '#161b27',
    border: '1px solid #374151', borderRadius: 8, color: '#f0f6ff',
    fontSize: 14, outline: 'none', transition: 'border-color 0.2s',
    boxSizing: 'border-box',
  },
  eye: {
    position: 'absolute', right: 12, top: '50%',
    transform: 'translateY(-50%)', background: 'none',
    border: 'none', cursor: 'pointer', fontSize: 16,
  },
  keyHint: { fontSize: 11, color: '#374151', margin: '4px 0 0' },
  btn: {
    marginTop: 4, padding: '12px 0',
    background: 'linear-gradient(135deg,#2563eb,#1d4ed8)',
    border: 'none', borderRadius: 8, color: '#fff',
    fontSize: 15, fontWeight: 700, cursor: 'pointer',
    display: 'flex', alignItems: 'center', justifyContent: 'center',
    transition: 'opacity 0.2s',
  },
  spin: {
    display: 'inline-block', width: 18, height: 18,
    border: '2px solid rgba(255,255,255,0.3)', borderTopColor: '#fff',
    borderRadius: '50%', animation: 'spin 0.7s linear infinite',
  },
  note: { marginTop: 24, fontSize: 12, color: '#374151', textAlign: 'center' },
}