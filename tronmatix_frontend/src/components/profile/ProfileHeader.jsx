// src/components/profile/ProfileHeader.jsx
import { useLang } from '../../context/LanguageContext'

const THEMES = {
  vip: {
    bg:      'linear-gradient(135deg, #0f0500 0%, #1e0900 50%, #0f0500 100%)',
    accent:  '#F97316', gold: '#FBBF24',
    glow1:   'rgba(249,115,22,0.4)', glow2: 'rgba(251,191,36,0.25)',
    shimmer: true,
  },
  reseller: {
    bg:      'linear-gradient(135deg, #00060f 0%, #00112b 50%, #00060f 100%)',
    accent:  '#3B82F6', gold: '#60A5FA',
    glow1:   'rgba(59,130,246,0.35)', glow2: 'rgba(96,165,250,0.2)',
    shimmer: false,
  },
  banned: {
    bg:      'linear-gradient(135deg, #0f0000 0%, #1e0000 50%, #0f0000 100%)',
    accent:  '#EF4444', gold: '#F87171',
    glow1:   'rgba(239,68,68,0.3)', glow2: 'rgba(239,68,68,0.15)',
    shimmer: false,
  },
  customer: {
    bg:      'linear-gradient(135deg, #0a0a0a 0%, #181818 50%, #0a0a0a 100%)',
    accent:  '#F97316', gold: '#F97316',
    glow1:   'rgba(249,115,22,0.2)', glow2: 'rgba(249,115,22,0.1)',
    shimmer: false,
  },
}

export default function ProfileHeader({ user, totalSpent, VIP_GOAL }) {
  const { isKhmer } = useLang()
  const headerFont = isKhmer ? 'Kh_Jrung_Thom, Khmer OS, sans-serif' : 'Rajdhani, sans-serif'
  const bodyFont = isKhmer ? 'KantumruyPro, Khmer OS, sans-serif' : 'Rajdhani, sans-serif'
  const role       = user?.role || 'customer'
  const spent      = totalSpent ?? 0
  const pct        = Math.min(100, Math.round((spent / VIP_GOAL) * 100))
  const isVip      = role === 'vip'
  const isReseller = role === 'reseller'
  const isBanned   = role === 'banned'
  const showVip    = isVip || pct >= 100
  const t          = showVip ? THEMES.vip : THEMES[role] || THEMES.customer

  return (
    <div style={{ background: t.bg, position: 'relative', overflow: 'hidden' }}>

      {/* Top shimmer line (VIP) */}
      {t.shimmer && (
        <div style={{
          position: 'absolute', top: 0, left: 0, right: 0, height: 2,
          background: `linear-gradient(90deg, transparent 0%, ${t.accent} 30%, ${t.gold} 50%, ${t.accent} 70%, transparent 100%)`,
        }} />
      )}

      {/* Dot grid */}
      <div style={{
        position: 'absolute', inset: 0, opacity: 0.04,
        backgroundImage: `radial-gradient(circle, ${t.accent} 1px, transparent 1px)`,
        backgroundSize: '24px 24px',
      }} />

      {/* Glow orbs */}
      <div style={{ position: 'absolute', top: -60, left: -60, width: 260, height: 260,
        background: `radial-gradient(circle, ${t.glow1} 0%, transparent 65%)`, pointerEvents: 'none' }} />
      <div style={{ position: 'absolute', bottom: -50, right: -30, width: 220, height: 220,
        background: `radial-gradient(circle, ${t.glow2} 0%, transparent 65%)`, pointerEvents: 'none' }} />

      {/* Content */}
      <div style={{ maxWidth: 720, margin: '0 auto', padding: '28px 24px 24px', position: 'relative' }}>
        <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 16 }}>

          {/* Left: avatar + name + email + badge */}
          <div style={{ display: 'flex', alignItems: 'flex-start', gap: 16 }}>

            {/* Avatar circle */}
            <div style={{
              width: 72, height: 72, borderRadius: '50%', flexShrink: 0,
              overflow: 'hidden',
              border: `2.5px solid ${t.accent}`,
              boxShadow: `0 0 0 3px ${t.glow1}, 0 4px 16px rgba(0,0,0,0.3)`,
              background: `linear-gradient(135deg, ${t.accent}, ${t.gold})`,
              display: 'flex', alignItems: 'center', justifyContent: 'center',
              fontSize: 28, fontWeight: 900, color: '#fff',
              fontFamily: 'Rajdhani, sans-serif',
              transition: 'all 0.3s',
            }}>
              {user?.avatar ? (
                <img
                  src={user.avatar}
                  alt={user?.username}
                  style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }}
                  onError={e => { e.target.style.display = 'none'; e.target.nextSibling.style.display = 'flex' }}
                />
              ) : null}
              <div style={{
                display: user?.avatar ? 'none' : 'flex',
                width: '100%', height: '100%',
                alignItems: 'center', justifyContent: 'center',
              }}>
                {(user?.username || user?.name || '?').charAt(0).toUpperCase()}
              </div>
            </div>

            <div>
            <div style={{
              fontSize: 30, fontWeight: 900, color: '#fff', letterSpacing: 1, lineHeight: 1,
              fontFamily: headerFont,
              textShadow: showVip ? '0 0 24px rgba(249,115,22,0.5)' : 'none',
            }}>
              {user?.username || user?.name}
            </div>
            <div style={{ fontSize: 13, color: 'rgba(255,255,255,0.4)', marginTop: 5 }}>
              {user?.email}
            </div>

            <div style={{ marginTop: 12 }}>
              {showVip ? (
                <div style={{
                  display: 'inline-flex', alignItems: 'center', gap: 6,
                  background: 'linear-gradient(135deg, #F97316 0%, #FBBF24 50%, #F97316 100%)',
                  color: '#fff', fontSize: 12, fontWeight: 900,
                  padding: '5px 16px', borderRadius: 20, letterSpacing: 2,
                  boxShadow: '0 0 20px rgba(249,115,22,0.5), 0 2px 8px rgba(0,0,0,0.3)',
                  border: '1px solid rgba(251,191,36,0.6)', fontFamily: 'Rajdhani, sans-serif',
                }}>⭐ VIP MEMBER</div>
              ) : isReseller ? (
                <div style={{
                  display: 'inline-flex', alignItems: 'center', gap: 6,
                  background: 'linear-gradient(135deg, #1D4ED8, #3B82F6)',
                  color: '#fff', fontSize: 12, fontWeight: 900,
                  padding: '5px 16px', borderRadius: 20, letterSpacing: 2,
                  boxShadow: '0 0 16px rgba(59,130,246,0.4)',
                  border: '1px solid rgba(96,165,250,0.5)', fontFamily: 'Rajdhani, sans-serif',
                }}>🏪 RESELLER</div>
              ) : isBanned ? (
                <div style={{
                  display: 'inline-flex', alignItems: 'center', gap: 6,
                  background: 'rgba(239,68,68,0.15)', color: '#EF4444',
                  fontSize: 12, fontWeight: 900, padding: '5px 16px', borderRadius: 20, letterSpacing: 2,
                  border: '1px solid rgba(239,68,68,0.4)', fontFamily: 'Rajdhani, sans-serif',
                }}>🚫 BANNED</div>
              ) : (
                <div style={{
                  display: 'inline-flex', alignItems: 'center', gap: 6,
                  background: 'rgba(255,255,255,0.08)', color: 'rgba(255,255,255,0.6)',
                  fontSize: 14, fontWeight: 600, padding: '4px 14px', borderRadius: 20, letterSpacing: 2,
                  border: '1px solid rgba(255,255,255,0.12)', fontFamily: bodyFont,
                }}>{isKhmer ? 'សមាជិក' : 'MEMBER'}</div>
              )}
            </div>
            </div>
          </div>

          {/* Right: VIP star OR mini progress */}
          {showVip ? (
            <div style={{
              display: 'flex', flexDirection: 'column', alignItems: 'center',
              justifyContent: 'center', gap: 4, padding: '8px 16px',
              background: 'rgba(249,115,22,0.1)', borderRadius: 16,
              border: '1px solid rgba(249,115,22,0.25)', flexShrink: 0,
            }}>
              <div style={{ fontSize: 32, lineHeight: 1 }}>⭐</div>
              <div style={{ fontSize: 10, color: '#FBBF24', fontWeight: 800, letterSpacing: 2, fontFamily: 'Rajdhani, sans-serif' }}>
                PREMIUM
              </div>
            </div>
          ) : !isBanned && totalSpent !== null && (
            <div style={{
              flexShrink: 0, padding: '10px 14px', minWidth: 140,
              background: 'rgba(255,255,255,0.04)', borderRadius: 14,
              border: '1px solid rgba(255,255,255,0.07)',
            }}>
              <div style={{ fontSize: 10, color: 'rgba(255,255,255,0.4)', letterSpacing: 1, marginBottom: 6, fontFamily: 'Rajdhani, sans-serif' }}>
                VIP PROGRESS
              </div>
              <div style={{ height: 5, borderRadius: 5, background: 'rgba(255,255,255,0.08)', overflow: 'hidden' }}>
                <div style={{
                  height: '100%', borderRadius: 5, width: `${pct}%`,
                  background: 'linear-gradient(90deg, #F97316, #FBBF24)',
                  transition: 'width 1s ease', boxShadow: '0 0 6px rgba(249,115,22,0.6)',
                }} />
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 5 }}>
                <div style={{ fontSize: 11, color: '#F97316', fontWeight: 700 }}>
                  {'$' + spent.toLocaleString('en-US', { maximumFractionDigits: 0 })}
                </div>
                <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.3)', fontWeight: 600 }}>
                  {pct}% · $1,000
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
