// src/components/profile/AvatarUpload.jsx
// Reusable avatar upload component — same style as admin dashboard avatar
import { useState, useRef } from 'react'
import axiosClient from '../../lib/axios'

export default function AvatarUpload({ user, onUpdated, notify }) {
  const [uploading, setUploading] = useState(false)
  const [preview,   setPreview]   = useState(null)
  const fileRef = useRef(null)

  const avatarUrl = preview
    || (user?.avatar
      ? (user.avatar.startsWith('http') ? user.avatar : null)
      : null)

  const initials = (user?.username || user?.name || '?')
    .charAt(0).toUpperCase()

  // Role color for gradient fallback
  const roleColors = {
    vip:      ['#F97316', '#FBBF24'],
    reseller: ['#3B82F6', '#60A5FA'],
    banned:   ['#EF4444', '#F87171'],
    customer: ['#F97316', '#ea580c'],
  }
  const [c1, c2] = roleColors[user?.role] || roleColors.customer

  const handleFileChange = async (e) => {
    const file = e.target.files?.[0]
    if (!file) return

    // Client-side validation
    if (file.size > 2 * 1024 * 1024) {
      notify('Image must be under 2MB', 'error')
      return
    }
    if (!['image/jpeg','image/png','image/webp'].includes(file.type)) {
      notify('Only JPG, PNG, WEBP allowed', 'error')
      return
    }

    // Show local preview immediately
    const reader = new FileReader()
    reader.onload = (ev) => setPreview(ev.target.result)
    reader.readAsDataURL(file)

    // Upload
    setUploading(true)
    try {
      const fd = new FormData()
      fd.append('avatar', file)
      const res = await axiosClient.post('/api/user/avatar', fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      const updated = res.data?.data ?? res.data
      setPreview(updated?.avatar || null)
      notify('Avatar updated! 🎉', 'success')
      onUpdated?.(updated)
    } catch (err) {
      setPreview(null)
      notify(err.response?.data?.message || 'Upload failed', 'error')
    } finally {
      setUploading(false)
      if (fileRef.current) fileRef.current.value = ''
    }
  }

  const handleRemove = async () => {
    setUploading(true)
    try {
      await axiosClient.delete('/api/user/avatar')
      setPreview(null)
      notify('Avatar removed', 'success')
      onUpdated?.({ ...user, avatar: null })
    } catch {
      notify('Failed to remove avatar', 'error')
    } finally {
      setUploading(false)
    }
  }

  return (
    <div style={{ display: 'flex', alignItems: 'center', gap: 20, marginBottom: 28 }}>

      {/* ── Avatar circle ───────────────────────────────────────────────────── */}
      <div style={{ position: 'relative', flexShrink: 0 }}>
        <div style={{
          width: 80, height: 80, borderRadius: '50%', overflow: 'hidden',
          border: '3px solid',
          borderColor: avatarUrl ? '#F97316' : 'rgba(249,115,22,0.3)',
          boxShadow: avatarUrl
            ? '0 0 0 3px rgba(249,115,22,0.15), 0 4px 16px rgba(0,0,0,0.12)'
            : '0 4px 16px rgba(0,0,0,0.08)',
          position: 'relative',
          transition: 'all 0.3s',
        }}>
          {uploading && (
            <div style={{
              position: 'absolute', inset: 0, zIndex: 10,
              background: 'rgba(0,0,0,0.55)',
              display: 'flex', alignItems: 'center', justifyContent: 'center',
              borderRadius: '50%',
            }}>
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                stroke="#F97316" strokeWidth="2.5"
                style={{ animation: 'spin 0.8s linear infinite' }}>
                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
              </svg>
            </div>
          )}
          {avatarUrl ? (
            <img
              src={avatarUrl}
              alt="Avatar"
              style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }}
              onError={(e) => { e.target.style.display = 'none'; e.target.nextSibling.style.display = 'flex' }}
            />
          ) : null}
          {/* Fallback gradient initial */}
          <div style={{
            display: avatarUrl ? 'none' : 'flex',
            width: '100%', height: '100%',
            background: `linear-gradient(135deg, ${c1}, ${c2})`,
            alignItems: 'center', justifyContent: 'center',
            fontSize: 32, fontWeight: 900, color: '#fff',
            fontFamily: 'Rajdhani, sans-serif',
          }}>
            {initials}
          </div>
        </div>

        {/* Camera overlay button */}
        <button
          onClick={() => fileRef.current?.click()}
          disabled={uploading}
          title="Change photo"
          style={{
            position: 'absolute', bottom: -2, right: -2,
            width: 26, height: 26, borderRadius: '50%',
            background: '#F97316', border: '2px solid #fff',
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            cursor: uploading ? 'wait' : 'pointer',
            boxShadow: '0 2px 8px rgba(249,115,22,0.4)',
            transition: 'transform 0.15s, background 0.15s',
            fontSize: 12,
          }}
          onMouseOver={e => e.currentTarget.style.transform = 'scale(1.1)'}
          onMouseOut={e => e.currentTarget.style.transform = 'scale(1)'}
        >
          📷
        </button>
      </div>

      {/* ── Buttons + info ──────────────────────────────────────────────────── */}
      <div style={{ flex: 1 }}>
        <div style={{ fontSize: 13, fontWeight: 700, color: '#374151', marginBottom: 8,
          fontFamily: 'Rajdhani, sans-serif', letterSpacing: 0.5 }}>
          PROFILE PHOTO
        </div>

        <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
          {/* Upload button */}
          <button
            onClick={() => fileRef.current?.click()}
            disabled={uploading}
            style={{
              display: 'inline-flex', alignItems: 'center', gap: 6,
              padding: '7px 14px', borderRadius: 8, cursor: uploading ? 'wait' : 'pointer',
              background: 'rgba(249,115,22,0.08)',
              border: '1px solid rgba(249,115,22,0.3)',
              color: '#C2410C', fontFamily: 'Rajdhani, sans-serif',
              fontSize: 13, fontWeight: 700, letterSpacing: 0.5,
              transition: 'all 0.15s',
            }}
            onMouseOver={e => e.currentTarget.style.background = 'rgba(249,115,22,0.14)'}
            onMouseOut={e => e.currentTarget.style.background = 'rgba(249,115,22,0.08)'}
          >
            📷 {uploading ? 'Uploading...' : 'Choose Photo'}
          </button>

          {/* Remove button — only if avatar exists */}
          {(avatarUrl || user?.avatar) && !uploading && (
            <button
              onClick={handleRemove}
              style={{
                display: 'inline-flex', alignItems: 'center', gap: 5,
                padding: '7px 14px', borderRadius: 8, cursor: 'pointer',
                background: 'rgba(239,68,68,0.06)',
                border: '1px solid rgba(239,68,68,0.25)',
                color: '#DC2626', fontFamily: 'Rajdhani, sans-serif',
                fontSize: 13, fontWeight: 700, letterSpacing: 0.5,
                transition: 'all 0.15s',
              }}
              onMouseOver={e => e.currentTarget.style.background = 'rgba(239,68,68,0.12)'}
              onMouseOut={e => e.currentTarget.style.background = 'rgba(239,68,68,0.06)'}
            >
              🗑 Remove
            </button>
          )}
        </div>

        <div style={{ fontSize: 11, color: '#9CA3AF', marginTop: 6 }}>
          JPG, PNG, WEBP · Max 2MB
        </div>
      </div>

      {/* Hidden file input */}
      <input
        ref={fileRef}
        type="file"
        accept="image/jpeg,image/png,image/webp"
        style={{ display: 'none' }}
        onChange={handleFileChange}
      />
    </div>
  )
}
