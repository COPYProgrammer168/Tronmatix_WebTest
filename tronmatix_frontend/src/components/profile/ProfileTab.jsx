// src/components/profile/ProfileTab.jsx
import { useState, useEffect } from 'react'
import axiosClient from '../../lib/axios'
import AvatarUpload from './AvatarUpload'
import TelegramConnect from './TelegramConnect'

const labelStyle = {
  display: 'block', fontSize: 11, fontWeight: 700, letterSpacing: 2,
  color: '#9CA3AF', marginBottom: 8,
}

const inputStyle = (hasError, editable = true) => ({
  width: '100%', boxSizing: 'border-box',
  padding: '12px 16px', borderRadius: 10, outline: 'none',
  fontFamily: 'Rajdhani, sans-serif', fontSize: 15, fontWeight: 600,
  border: hasError ? '1.5px solid #EF4444' : '1.5px solid #E5E7EB',
  background: editable ? '#fff' : '#F9FAFB',
  color: editable ? '#111' : '#374151',
  cursor: editable ? 'text' : 'default',
  transition: 'border-color 0.2s, box-shadow 0.2s',
})

const ROLE_STYLE = {
  customer: { bg: '#F9FAFB', border: '#E5E7EB', color: '#6B7280', icon: '👤', label: 'CUSTOMER', note: 'Standard Member' },
  vip:      { bg: 'rgba(249,115,22,0.06)', border: '#FDBA74', color: '#F97316', icon: '⭐', label: 'VIP', note: 'VIP Member' },
  reseller: { bg: 'rgba(59,130,246,0.06)', border: '#93C5FD', color: '#3B82F6', icon: '🏪', label: 'RESELLER', note: 'Reseller Partner' },
  banned:   { bg: 'rgba(239,68,68,0.06)', border: '#FCA5A5', color: '#EF4444', icon: '🚫', label: 'BANNED', note: 'Contact support' },
}

const fmt = (n) => '$' + n.toLocaleString('en-US', { maximumFractionDigits: 0 })

export default function ProfileTab({ user, totalSpent, VIP_GOAL, onSaved, notify, dark }) {
  const [editing,   setEditing]   = useState(false)
  const [saving,    setSaving]    = useState(false)
  const [saved,     setSaved]     = useState(false)
  const [errors,    setErrors]    = useState({})
  const [form,      setForm]      = useState({ name: '', phone: '' })
  const [localUser, setLocalUser] = useState(user)

  useEffect(() => {
    if (user) {
      setLocalUser(user)
      setForm({ name: user.username || user.name || '', phone: user.phone || '' })
    }
  }, [user])

  const set = (k, v) => setForm(f => ({ ...f, [k]: v }))

  const validate = () => {
    const errs = {}
    if (!form.name.trim()) errs.name = 'Name is required'
    if (form.phone && !/^[\d\s+\-()]{7,20}$/.test(form.phone)) errs.phone = 'Invalid phone number'
    setErrors(errs)
    return Object.keys(errs).length === 0
  }

  const handleAvatarUpdated = (updatedUser) => {
    if (updatedUser) setLocalUser(prev => ({ ...prev, ...updatedUser }))
    onSaved?.()
  }

  const handleSave = async () => {
    if (!validate()) return
    setSaving(true)
    try {
      await axiosClient.put('/api/user/profile', {
        username: form.name.trim(),
        phone:    form.phone.trim(),
      })
      setSaved(true)
      setEditing(false)
      setErrors({})
      setTimeout(() => { setSaved(false); onSaved?.() }, 1800)
      notify('Profile updated!', 'success')
    } catch (err) {
      notify(err.response?.data?.message || 'Failed to update profile', 'error')
    } finally { setSaving(false) }
  }

  const cancel = () => { setEditing(false); setErrors({}) }

  const role      = user?.role || 'customer'
  const spent     = totalSpent ?? 0
  const pct       = Math.min(100, Math.round((spent / VIP_GOAL) * 100))
  const remaining = Math.max(0, VIP_GOAL - spent)
  const showVip   = role === 'vip' || pct >= 100
  const s         = ROLE_STYLE[showVip ? 'vip' : role] || ROLE_STYLE.customer

  return (
    <div style={{ padding: 32, animation: 'fadeUp 0.3s ease', position: 'relative' }}>

      {/* Save success flash */}
      {saved && (
        <div style={{
          position: 'absolute', inset: 0, zIndex: 10, borderRadius: 20,
          background: 'rgba(34,197,94,0.07)',
          display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center',
          pointerEvents: 'none',
        }}>
          <div style={{ width: 72, height: 72, borderRadius: '50%', background: 'linear-gradient(135deg,#22C55E,#16A34A)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 36 }}>✓</div>
          <div style={{ marginTop: 14, fontSize: 18, fontWeight: 800, color: '#16A34A', fontFamily: 'Rajdhani,sans-serif', letterSpacing: 2 }}>SAVED!</div>
        </div>
      )}

      {/* Avatar */}
      <AvatarUpload user={localUser} onUpdated={handleAvatarUpdated} notify={notify} />

      {/* Header */}
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 28 }}>
        <div>
          <h2 style={{ fontSize: 20, fontWeight: 800, letterSpacing: 1, margin: 0 }}>Personal Information</h2>
          <div style={{ fontSize: 14, color: '#9CA3AF', marginTop: 4 }}>Manage your account details</div>
        </div>
        {!editing ? (
          <button onClick={() => setEditing(true)} style={{ background: '#FFF7ED', color: '#C2410C', border: '1px solid #FED7AA', borderRadius: 10, padding: '8px 20px', cursor: 'pointer', fontFamily: 'Rajdhani,sans-serif', fontSize: 14, fontWeight: 700, letterSpacing: 1 }}>✏️ EDIT</button>
        ) : (
          <button onClick={cancel} style={{ background: '#F9FAFB', color: '#6B7280', border: '1px solid #E5E7EB', borderRadius: 10, padding: '8px 20px', cursor: 'pointer', fontFamily: 'Rajdhani,sans-serif', fontSize: 14, fontWeight: 700, letterSpacing: 1 }}>CANCEL</button>
        )}
      </div>

      {/* Form fields */}
      <div style={{ display: 'grid', gap: 20 }}>
        <div>
          <label style={labelStyle}>FULL NAME {editing && '*'}</label>
          <input value={form.name} onChange={e => set('name', e.target.value)} readOnly={!editing} placeholder="Your full name"
            style={{ ...inputStyle(errors.name, editing), boxShadow: editing ? '0 0 0 3px rgba(249,115,22,0.1)' : 'none', borderColor: editing && !errors.name ? '#F97316' : errors.name ? '#EF4444' : '#E5E7EB' }} />
          {errors.name && <div style={{ color: '#DC2626', fontSize: 12, marginTop: 4 }}>{errors.name}</div>}
        </div>

        <div>
          <label style={labelStyle}>USERNAME</label>
          <div style={{ position: 'relative' }}>
            <input value={user?.username || ''} readOnly style={{ ...inputStyle(false, false), paddingRight: 80 }} />
            <span style={{ position: 'absolute', right: 14, top: '50%', transform: 'translateY(-50%)', fontSize: 11, fontWeight: 700, color: '#9CA3AF', background: '#F3F4F6', padding: '2px 8px', borderRadius: 6 }}>LOCKED</span>
          </div>
        </div>

        <div>
          <label style={labelStyle}>EMAIL ADDRESS</label>
          <div style={{ position: 'relative' }}>
            <input value={user?.email || ''} readOnly style={{ ...inputStyle(false, false), paddingRight: 80 }} />
            <span style={{ position: 'absolute', right: 14, top: '50%', transform: 'translateY(-50%)', fontSize: 11, fontWeight: 700, color: '#9CA3AF', background: '#F3F4F6', padding: '2px 8px', borderRadius: 6 }}>LOCKED</span>
          </div>
          <div style={{ fontSize: 12, color: '#9CA3AF', marginTop: 5 }}>Contact support to change your email address</div>
        </div>

        <div>
          <label style={labelStyle}>PHONE / TEL</label>
          <div style={{ position: 'relative' }}>
            <span style={{ position: 'absolute', left: 14, top: '50%', transform: 'translateY(-50%)', fontSize: 15, pointerEvents: 'none' }}>📞</span>
            <input value={form.phone} onChange={e => set('phone', e.target.value)} readOnly={!editing} placeholder="0xx xxx xxx"
              style={{ ...inputStyle(errors.phone, editing), paddingLeft: 40, boxShadow: editing ? '0 0 0 3px rgba(249,115,22,0.1)' : 'none', borderColor: editing && !errors.phone ? '#F97316' : errors.phone ? '#EF4444' : '#E5E7EB' }} />
          </div>
          {errors.phone && <div style={{ color: '#DC2626', fontSize: 12, marginTop: 4 }}>{errors.phone}</div>}
        </div>

        {editing && (
          <button onClick={handleSave} disabled={saving} style={{
            width: '100%', padding: 14, borderRadius: 12, border: 'none',
            background: saving ? 'linear-gradient(135deg,#FED7AA,#FCA5A5)' : 'linear-gradient(135deg,#F97316,#ea580c)',
            color: '#fff', fontFamily: 'Rajdhani,sans-serif', fontSize: 17, fontWeight: 800, letterSpacing: 2,
            cursor: saving ? 'wait' : 'pointer', boxShadow: saving ? 'none' : '0 4px 20px rgba(249,115,22,0.4)',
            display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10,
          }}>
            {saving ? '⏳ SAVING...' : '💾 SAVE CHANGES'}
          </button>
        )}
      </div>

        {/* ── FIX: Telegram Connect Section ───────────────────────────────────── */}
        <TelegramConnect
          user={localUser}
          dark={dark ?? false}
          onUpdate={(data) => {
            setLocalUser(prev => ({ ...prev, ...data }))
            onSaved?.()
          }}
          notify={notify}
        />
        
      {/* Role card + VIP progress */}
      <div style={{ marginTop: 28 }}>
        <div style={{ padding: '14px 18px', borderRadius: 12, background: s.bg, border: `1px solid ${s.border}`, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <span style={{ fontSize: 26 }}>{s.icon}</span>
            <div>
              <div style={{ fontSize: 10, letterSpacing: 2, color: '#9CA3AF', fontWeight: 700 }}>ACCOUNT ROLE</div>
              <div style={{ fontSize: 18, fontWeight: 800, color: s.color, fontFamily: 'Rajdhani,sans-serif', letterSpacing: 1 }}>{s.label}</div>
            </div>
          </div>
          <div style={{ textAlign: 'right' }}>
            <div style={{ fontSize: 12, color: s.color, fontWeight: 600, opacity: 0.8 }}>{s.note}</div>
            {totalSpent !== null && <div style={{ fontSize: 13, fontWeight: 700, color: '#374151', marginTop: 2 }}>{fmt(spent)} spent</div>}
          </div>
        </div>

        {!showVip && role === 'customer' && (
          <div style={{ marginTop: 10, padding: '14px 18px', borderRadius: 12, background: 'rgba(249,115,22,0.04)', border: '1px dashed rgba(249,115,22,0.25)' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 }}>
              <div style={{ fontSize: 12, fontWeight: 700, color: '#F97316', letterSpacing: 1 }}>⭐ VIP PROGRESS</div>
              <div style={{ fontSize: 12, color: '#9CA3AF', fontWeight: 600 }}>{pct >= 100 ? '🎉 Upgrading to VIP!' : `${fmt(remaining)} more to VIP`}</div>
            </div>
            <div style={{ height: 8, borderRadius: 8, background: '#F3F4F6', overflow: 'hidden' }}>
              <div style={{ height: '100%', borderRadius: 8, width: `${pct}%`, background: 'linear-gradient(90deg,#F97316,#fb923c)', transition: 'width 0.8s cubic-bezier(0.4,0,0.2,1)' }} />
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 5 }}>
              <div style={{ fontSize: 11, color: '#F97316', fontWeight: 700 }}>{totalSpent === null ? '...' : fmt(spent)}</div>
              <div style={{ fontSize: 11, color: '#9CA3AF', fontWeight: 600 }}>{pct}% · $1,000 goal</div>
            </div>
          </div>
        )}

        {showVip && (
          <div style={{ marginTop: 10, padding: '10px 18px', borderRadius: 12, textAlign: 'center', background: 'linear-gradient(135deg,rgba(249,115,22,0.1),rgba(251,191,36,0.1))', border: '1px solid rgba(249,115,22,0.3)', fontSize: 13, fontWeight: 700, color: '#F97316', letterSpacing: 1 }}>
            ⭐ VIP MEMBER — Thank you for your loyalty!
          </div>
        )}
      </div>

      {/* Account info strip */}
      <div style={{ marginTop: 12, padding: '16px 20px', borderRadius: 12, background: '#F9FAFB', border: '1px solid #F3F4F6', display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: 16 }}>
        {[
          { label: 'MEMBER SINCE', value: user?.created_at ? new Date(user.created_at).toLocaleDateString('en-US', { month: 'short', year: 'numeric' }) : '—' },
          { label: 'ACCOUNT ID',   value: `#${user?.id || '—'}` },
          { label: 'STATUS',       value: user?.role === 'banned' ? 'Banned 🚫' : 'Active ✓' },
        ].map(({ label, value }) => (
          <div key={label} style={{ textAlign: 'center' }}>
            <div style={{ fontSize: 10, letterSpacing: 2, color: '#9CA3AF', fontWeight: 700 }}>{label}</div>
            <div style={{ fontSize: 15, fontWeight: 700, color: '#374151', marginTop: 4 }}>{value}</div>
          </div>
        ))}
      </div>

    </div>
  )
}
