// src/components/profile/Toast.jsx
import { useEffect } from 'react'

export default function Toast({ msg, type, onDone }) {
  useEffect(() => {
    if (!msg) return
    const t = setTimeout(onDone, 3000)
    return () => clearTimeout(t)
  }, [msg])

  if (!msg) return null
  return (
    <div style={{
      position: 'fixed', bottom: 28, right: 28, zIndex: 9999,
      background: type === 'error' ? '#1C1917' : '#111',
      color: '#fff', borderRadius: 14, padding: '14px 22px',
      display: 'flex', alignItems: 'center', gap: 10,
      boxShadow: '0 8px 32px rgba(0,0,0,0.25)',
      border: type === 'error' ? '1px solid #EF4444' : '1px solid rgba(249,115,22,0.4)',
      animation: 'slideIn 0.3s ease',
      fontFamily: 'Rajdhani, sans-serif', fontSize: 15, fontWeight: 600,
    }}>
      <span style={{ fontSize: 18 }}>{type === 'error' ? '⚠️' : '✅'}</span>
      {msg}
    </div>
  )
}
