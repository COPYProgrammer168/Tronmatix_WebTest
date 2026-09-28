import { useTheme } from '../context/ThemeContext'
import { useLang } from '../context/LanguageContext'

/**
 * Reusable logout confirmation popup with a scale/fade-in animation.
 * Shows a "Log Out?" confirm modal; on confirm calls `onConfirm()`.
 */
export default function LogoutConfirmModal({ open, onCancel, onConfirm }) {
  const { dark } = useTheme()
  const { t, isKhmer } = useLang()

  const c = {
    overlay: dark ? 'rgba(0,0,0,0.6)' : 'rgba(0,0,0,0.4)',
    cardBg: dark ? '#1f2937' : '#ffffff',
    text: dark ? '#f9fafb' : '#1f2937',
    textMuted: dark ? '#9ca3af' : '#6b7280',
    border: dark ? '#374151' : '#e5e7eb',
    cancelBg: dark ? '#374151' : '#f3f4f6',
    btnText: dark ? '#f9fafb' : '#1f2937',
  }

  const font = isKhmer
    ? "Kdam Thmor Pro, sans-serif"
    : "Rajdhani, sans-serif"

  if (!open) return null

  return (
    <div
      onClick={onCancel}
      style={{
        position: 'fixed', inset: 0, zIndex: 9999,
        background: c.overlay,
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        padding: 20, fontFamily: font,
      }}
    >
      <div
        onClick={e => e.stopPropagation()}
        className="logout-confirm-card"
        style={{
          background: c.cardBg,
          border: `1px solid ${c.border}`,
          borderRadius: 18,
          maxWidth: 380,
          width: '100%',
          padding: '28px 24px',
          textAlign: 'center',
          boxShadow: dark
            ? '0 20px 60px rgba(0,0,0,0.6)'
            : '0 20px 60px rgba(0,0,0,0.15)',
        }}
      >
        {/* Icon */}
        <div
          style={{
            width: 64, height: 64, margin: '0 auto 16px',
            borderRadius: '50%',
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            fontSize: 30,
            background: 'rgba(239,68,68,0.12)',
            border: '1px solid rgba(239,68,68,0.25)',
          }}
        >
          🚪
        </div>

        <h2
          style={{
            fontSize: isKhmer ? 18 : 20, fontWeight: 800,
            color: c.text, marginBottom: 8,
            letterSpacing: isKhmer ? 0 : 1,
          }}
        >
          {t('nav.logoutConfirmTitle')}
        </h2>
        <p style={{ fontSize: 14, color: c.textMuted, lineHeight: 1.6, marginBottom: 22 }}>
          {t('nav.logoutConfirmMessage')}
        </p>

        <div style={{ display: 'flex', gap: 10 }}>
          {/* Cancel */}
          <button
            onClick={onCancel}
            style={{
              flex: 1, padding: '12px 0', borderRadius: 10,
              background: c.cancelBg, border: `1px solid ${c.border}`,
              color: c.btnText, fontSize: 15, fontWeight: 700,
              cursor: 'pointer', fontFamily: font,
              transition: 'all 0.15s',
            }}
            onMouseEnter={e => { e.currentTarget.style.opacity = 0.85 }}
            onMouseLeave={e => { e.currentTarget.style.opacity = 1 }}
          >
            {t('nav.logoutConfirmNo')}
          </button>
          {/* Confirm */}
          <button
            onClick={onConfirm}
            style={{
              flex: 1, padding: '12px 0', borderRadius: 10,
              background: 'linear-gradient(135deg, #ef4444, #dc2626)',
              border: 'none', color: '#fff', fontSize: 15, fontWeight: 800,
              cursor: 'pointer', fontFamily: font,
              boxShadow: '0 4px 16px rgba(239,68,68,0.35)',
              transition: 'all 0.15s',
            }}
            onMouseEnter={e => { e.currentTarget.style.transform = 'translateY(-1px)' }}
            onMouseLeave={e => { e.currentTarget.style.transform = 'translateY(0)' }}
          >
            {t('nav.logoutConfirmYes')}
          </button>
        </div>
      </div>

      <style>{`
        @keyframes logoutConfirmPop {
          0%   { opacity: 0; transform: scale(0.85) translateY(12px); }
          60%  { opacity: 1; transform: scale(1.02) translateY(0); }
          100% { opacity: 1; transform: scale(1) translateY(0); }
        }
        @keyframes logoutConfirmOverlay {
          from { opacity: 0; }
          to   { opacity: 1; }
        }
        .logout-confirm-card {
          animation: logoutConfirmPop 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
      `}</style>
    </div>
  )
}
