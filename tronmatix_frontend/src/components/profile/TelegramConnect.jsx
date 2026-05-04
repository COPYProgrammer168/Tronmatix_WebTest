// src/components/profile/TelegramConnect.jsx
//
// FIX: window.open(oauth.telegram.org) was blocked by browser → about:blank popup.
// oauth.telegram.org sends X-Frame-Options / popup blocker headers.
//
// REAL SOLUTION for switching accounts:
// The ONLY reliable way is to have the user:
//   1. Open the Telegram APP (not web) and switch account there
//   2. OR open web.telegram.org in same tab, log out, log in with other account
//   3. Then come back and refresh the widget
//
// We give a direct t.me link as an <a> tag (not window.open) — browser allows this.

import { useState, useEffect, useCallback } from 'react'
import axiosClient from '../../lib/axios'
import { useLang } from '../../context/LanguageContext'

const BOT_USERNAME = import.meta.env.VITE_TELEGRAM_BOT_USERNAME || ''

export default function TelegramConnect({ user, dark, onUpdate, notify }) {
  const { t, isKhmer } = useLang()
  const tgFont = isKhmer ? 'KantumruyPro, Khmer OS, sans-serif' : 'Rajdhani,sans-serif'
  const [status,     setStatus]     = useState(null)
  const [loading,    setLoading]    = useState(true)
  const [actionBusy, setActionBusy] = useState(false)
  const [testSent,   setTestSent]   = useState(false)
  const [widgetKey,  setWidgetKey]  = useState(0)
  const [showSwitch, setShowSwitch] = useState(false)

  const c = {
    card:       dark ? '#111827' : '#F9FAFB',
    cardBorder: dark ? '#1F2937' : '#F3F4F6',
    text:       dark ? '#F9FAFB' : '#111827',
    muted:      dark ? '#9CA3AF' : '#6B7280',
  }

  const fetchStatus = useCallback(async (silent = false) => {
    if (!silent) setLoading(true)
    try {
      const res = await axiosClient.get('/api/telegram/status')
      setStatus(res.data?.data ?? null)
    } catch { setStatus(null) }
    finally { if (!silent) setLoading(false) }
  }, [])

  useEffect(() => { fetchStatus() }, [fetchStatus])

  // ── Widget injection ──────────────────────────────────────────────────────
  useEffect(() => {
    if (status?.connected || !BOT_USERNAME) return

    window.onTelegramAuth = async (tgUser) => {
      if (!tgUser) return
      setActionBusy(true)
      try {
        const res = await axiosClient.post('/api/telegram/connect', tgUser)
        if (res.data?.success) {
          notify('Telegram connected! ✅', 'success')
          await fetchStatus(true)
          onUpdate?.({ telegram_connected: true })
          setShowSwitch(false)
        } else {
          notify(res.data?.message || 'Connection failed.', 'error')
        }
      } catch (err) {
        notify(err.response?.data?.message || 'Failed to connect.', 'error')
      } finally { setActionBusy(false) }
    }

    const container = document.getElementById('tg-widget-container')
    if (!container) return
    container.innerHTML = ''

    const script = document.createElement('script')
    script.src = 'https://telegram.org/js/telegram-widget.js?22'
    script.async = true
    script.setAttribute('data-telegram-login', BOT_USERNAME)
    script.setAttribute('data-size',           'large')
    script.setAttribute('data-radius',         '10')
    script.setAttribute('data-onauth',         'onTelegramAuth(user)')
    script.setAttribute('data-request-access', 'write')
    container.appendChild(script)

    return () => {
      delete window.onTelegramAuth
      if (container) container.innerHTML = ''
    }
  }, [status?.connected, widgetKey, fetchStatus, notify, onUpdate])

  const handleDisconnect = async () => {
    if (!window.confirm('Disconnect Telegram?')) return
    setActionBusy(true)
    try {
      await axiosClient.post('/api/telegram/disconnect')
      notify('Telegram disconnected.', 'success')
      setTestSent(false)
      setShowSwitch(false)
      setWidgetKey(k => k + 1)   // force widget re-inject after disconnect
      await fetchStatus(true)
      onUpdate?.({ telegram_connected: false })
    } catch { notify('Failed to disconnect.', 'error') }
    finally { setActionBusy(false) }
  }

  const handleTest = async () => {
    setActionBusy(true)
    try {
      const res = await axiosClient.post('/api/telegram/test-message')
      if (res.data?.success) {
        setTestSent(true)
        notify('Test message sent! 📨', 'success')
        setTimeout(() => setTestSent(false), 4000)
      } else notify(res.data?.message || 'Failed.', 'error')
    } catch { notify('Failed.', 'error') }
    finally { setActionBusy(false) }
  }

  const handleRefresh = useCallback(() => {
    setShowSwitch(false)
    setWidgetKey(k => k + 1)
  }, [])

  return (
    <div style={{ border: `1px solid ${c.cardBorder}`, borderRadius: 14, background: c.card, overflow: 'hidden', marginTop: 20 }}>

      {/* Header */}
      <div style={{ padding: '14px 20px', display: 'flex', alignItems: 'center', gap: 12, borderBottom: `1px solid ${c.cardBorder}`, background: dark ? '#0F172A' : '#FFFFFF' }}>
        <div style={{ width: 36, height: 36, borderRadius: 10, background: 'linear-gradient(135deg,#229ED9,#0088cc)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 20, flexShrink: 0 }}>✈️</div>
        <div>
          <div style={{ fontSize: 14, fontWeight: 800, letterSpacing: isKhmer ? 0 : 1, color: c.text, fontFamily: tgFont }}>{isKhmer ? t('telegram.title') : 'TELEGRAM NOTIFICATIONS'}</div>
          <div style={{ fontSize: 12, color: c.muted, marginTop: 1 }}>{isKhmer ? t('telegram.subtitle') : 'Get order updates directly in Telegram'}</div>
        </div>
        <div style={{ marginLeft: 'auto' }}>
          {loading
            ? <span style={{ fontSize: 11, fontWeight: 700, color: c.muted, background: dark ? '#1F2937' : '#F3F4F6', padding: '4px 10px', borderRadius: 20 }}>{isKhmer ? t('common.loading') : 'LOADING...'}</span>
            : <StatusBadge connected={!!status?.connected} isKhmer={isKhmer} t={t} />
          }
        </div>
      </div>

      {/* Body */}
      <div style={{ padding: '18px 20px' }}>
        {loading ? <Skeleton dark={dark} /> : status?.connected
          ? <ConnectedView status={status} dark={dark} c={c} busy={actionBusy} testSent={testSent} onDisconnect={handleDisconnect} onTest={handleTest} isKhmer={isKhmer} t={t} />
          : <NotConnectedView
              dark={dark} c={c} busy={actionBusy} isKhmer={isKhmer} t={t}
              showSwitch={showSwitch}
              onClickSwitch={() => setShowSwitch(true)}
              onRefresh={handleRefresh}
            />
        }
      </div>
    </div>
  )
}

// ── NotConnectedView ──────────────────────────────────────────────────────────
function NotConnectedView({ dark, c, busy, showSwitch, onClickSwitch, onRefresh, isKhmer = false, t = (k) => k }) {
  return (
    <div>
      <p style={{ fontSize: 13, color: c.muted, lineHeight: 1.6, margin: '0 0 16px' }}>
        {isKhmer ? t('telegram.connectDesc') : 'Connect your Telegram to receive real-time order updates, receipts, shipping alerts, and delivery confirmations.'}
      </p>

      {/* Why connect */}
      <div style={{ padding: '12px 14px', borderRadius: 10, marginBottom: 16, background: dark ? 'rgba(34,153,221,0.07)' : 'rgba(34,153,221,0.05)', border: '1px dashed rgba(34,153,221,0.25)' }}>
        <div style={{ fontSize: 11, fontWeight: 700, color: '#229ED9', letterSpacing: isKhmer ? 0 : 2, marginBottom: 8 }}>{isKhmer ? t('telegram.whyConnect') : 'WHY CONNECT?'}</div>
        {(isKhmer
          ? [t('telegram.benefit1'), t('telegram.benefit2'), t('telegram.benefit3'), t('telegram.benefit4')]
          : ['🔔 Instant notifications — no app refresh needed', '🧾 Automatic order receipts after checkout', '🚚 Shipping & delivery alerts', '🔒 Secure — we only send, never read your messages'])
          .map(b => <div key={b} style={{ fontSize: 12, color: c.text, marginBottom: 5, fontWeight: 600 }}>{b}</div>)}
      </div>

      {/* FIX: Switch instructions — use <a> links, NOT window.open() */}
      {showSwitch && (
        <div style={{ padding: '14px 16px', borderRadius: 12, marginBottom: 14, background: dark ? 'rgba(249,115,22,0.08)' : 'rgba(249,115,22,0.05)', border: '1px solid rgba(249,115,22,0.3)' }}>
          <div style={{ fontSize: 12, fontWeight: 800, color: '#F97316', letterSpacing: 1, marginBottom: 12 }}>
            📋 {isKhmer ? t('telegram.howToSwitch') : 'HOW TO SWITCH ACCOUNT'}
          </div>

          {/* Option A — Telegram App (recommended) */}
          <div style={{ marginBottom: 12, padding: '10px 12px', borderRadius: 8, background: dark ? 'rgba(34,197,94,0.06)' : 'rgba(34,197,94,0.04)', border: '1px solid rgba(34,197,94,0.2)' }}>
            <div style={{ fontSize: 11, fontWeight: 800, color: '#16A34A', letterSpacing: isKhmer ? 0 : 1, marginBottom: 6 }}>✅ {isKhmer ? t('telegram.optionA') : 'OPTION A — Telegram App (easiest)'}</div>
            {['1️⃣  Open Telegram on your phone or desktop', '2️⃣  Tap ☰ menu → Switch Account → pick account', '3️⃣  Come back here → click Refresh Widget'].map(s =>
              <div key={s} style={{ fontSize: 12, color: c.text, marginBottom: 4, fontWeight: 600 }}>{s}</div>)}
          </div>

          {/* Option B — web.telegram.org (use <a> tag, never window.open) */}
          <div style={{ marginBottom: 12, padding: '10px 12px', borderRadius: 8, background: dark ? 'rgba(34,158,217,0.06)' : 'rgba(34,158,217,0.04)', border: '1px solid rgba(34,158,217,0.2)' }}>
            <div style={{ fontSize: 11, fontWeight: 800, color: '#229ED9', letterSpacing: isKhmer ? 0 : 1, marginBottom: 6 }}>🌐 {isKhmer ? t('telegram.optionB') : 'OPTION B — Telegram Web'}</div>
            {['1️⃣  Click the link below to open Telegram Web', '2️⃣  Click ☰ → Settings → Log Out', '3️⃣  Log in with your other account', '4️⃣  Come back here → click Refresh Widget'].map(s =>
              <div key={s} style={{ fontSize: 12, color: c.text, marginBottom: 4, fontWeight: 600 }}>{s}</div>)}
            {/* FIX: use <a> not window.open — browser won't block <a> */}
            <a
              href="https://web.telegram.org"
              target="_blank"
              rel="noopener noreferrer"
              style={{
                display: 'inline-flex', alignItems: 'center', gap: 6,
                marginTop: 8, padding: '7px 14px', borderRadius: 8,
                background: 'rgba(34,158,217,0.12)', border: '1px solid rgba(34,158,217,0.3)',
                color: '#229ED9', textDecoration: 'none',
                fontFamily: 'Rajdhani,sans-serif', fontSize: 12, fontWeight: 700, letterSpacing: 0.5,
              }}>
              🌐 Open web.telegram.org →
            </a>
          </div>

          <button onClick={onRefresh} style={{ width: '100%', padding: '10px 0', borderRadius: 10, background: 'linear-gradient(135deg,#F97316,#ea580c)', border: 'none', color: '#fff', fontFamily: isKhmer ? 'KantumruyPro,Khmer OS,sans-serif' : 'Rajdhani,sans-serif', fontSize: 13, fontWeight: 800, letterSpacing: isKhmer ? 0 : 1, cursor: 'pointer' }}>
            🔄 {isKhmer ? t('telegram.refreshWidget') : "Refresh Widget — I've Switched Account"}
          </button>
        </div>
      )}

      {/* Widget */}
      <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 8 }}>
        <div style={{ fontSize: 11, color: c.muted, fontWeight: 600, letterSpacing: isKhmer ? 0 : 1, marginBottom: 4 }}>{isKhmer ? t('telegram.clickToConnect') : 'CLICK BELOW TO CONNECT'}</div>

        <div id="tg-widget-container" style={{ minHeight: 44, display: 'flex', alignItems: 'center', justifyContent: 'center', opacity: busy ? 0.5 : 1, pointerEvents: busy ? 'none' : 'auto' }} />

        <div style={{ display: 'flex', alignItems: 'center', gap: 8, width: '100%', margin: '4px 0' }}>
          <div style={{ flex: 1, height: 1, background: dark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)' }} />
          <span style={{ fontSize: 10, color: c.muted, fontWeight: 700 }}>OR</span>
          <div style={{ flex: 1, height: 1, background: dark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)' }} />
        </div>

        <button onClick={onClickSwitch} disabled={busy} style={{ width: '100%', padding: '9px 0', borderRadius: 10, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 7, background: 'transparent', border: `1px solid ${dark ? 'rgba(255,255,255,0.12)' : 'rgba(34,158,217,0.3)'}`, color: '#229ED9', fontFamily: 'Rajdhani,sans-serif', fontSize: 13, fontWeight: 700, cursor: busy ? 'default' : 'pointer', opacity: busy ? 0.5 : 1 }}>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.3" strokeLinecap="round" strokeLinejoin="round">
            <path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 014-4h14"/>
            <path d="M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 01-4 4H3"/>
          </svg>
          {isKhmer ? t('telegram.loginDifferent') : 'Log in with a different account'}
        </button>
        <p style={{ fontSize: 10, color: c.muted, textAlign: 'center', margin: '2px 0 0' }}>
          {isKhmer ? t('telegram.switchHint') : 'Switch account in Telegram app, then click Refresh'}
        </p>
      </div>
    </div>
  )
}

// ── ConnectedView ─────────────────────────────────────────────────────────────
function ConnectedView({ status, dark, c, busy, testSent, onDisconnect, onTest, isKhmer = false, t = (k) => k }) {
  const connectedAt = status.connected_at
    ? new Date(status.connected_at).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' })
    : null
  return (
    <div>
      <div style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '12px 14px', borderRadius: 10, background: dark ? 'rgba(34,197,94,0.07)' : 'rgba(34,197,94,0.05)', border: '1px solid rgba(34,197,94,0.2)', marginBottom: 14 }}>
        <div style={{ width: 38, height: 38, borderRadius: '50%', background: 'linear-gradient(135deg,#229ED9,#0088cc)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 20 }}>✈️</div>
        <div style={{ flex: 1 }}>
          <div style={{ fontSize: 15, fontWeight: 800, color: c.text, fontFamily: 'Rajdhani,sans-serif' }}>
            {status.telegram_username ? `@${status.telegram_username}` : 'Telegram Account'}
          </div>
          {connectedAt && <div style={{ fontSize: 12, color: c.muted, marginTop: 1 }}>{isKhmer ? `${t('telegram.connectedSince')} ${connectedAt}` : `Connected since ${connectedAt}`}</div>}
        </div>
        <div style={{ fontSize: 11, fontWeight: 700, color: '#16A34A', background: 'rgba(34,197,94,0.12)', padding: '3px 10px', borderRadius: 20, fontFamily: isKhmer ? 'KantumruyPro,Khmer OS,sans-serif' : 'Rajdhani,sans-serif' }}>{isKhmer ? t('telegram.active') : 'ACTIVE'}</div>
      </div>

      <div style={{ marginBottom: 14 }}>
        <div style={{ fontSize: 11, fontWeight: 700, color: c.muted, letterSpacing: isKhmer ? 0 : 2, marginBottom: 8 }}>{isKhmer ? t('telegram.youllReceive') : "YOU'LL RECEIVE"}</div>
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 6 }}>
          {(isKhmer
            ? [['🧾',t('telegram.notif1')],['✅',t('telegram.notif2')],['🚚',t('telegram.notif3')],['🎉',t('telegram.notif4')],['🚫',t('telegram.notif5')],['💳',t('telegram.notif6')]]
            : [['🧾','Order receipts'],['✅','Order confirmed'],['🚚','Shipped alerts'],['🎉','Delivery confirmation'],['🚫','Cancellation updates'],['💳','Payment status']])
            .map(([icon, label]) => (
              <div key={label} style={{ display: 'flex', alignItems: 'center', gap: 7, fontSize: 13, color: c.text }}>
                <span>{icon}</span><span style={{ fontWeight: 600 }}>{label}</span>
              </div>
            ))}
        </div>
      </div>

      <div style={{ display: 'flex', gap: 8 }}>
        <button onClick={onTest} disabled={busy} style={{ flex: 1, padding: '10px 0', borderRadius: 10, background: testSent ? 'rgba(34,197,94,0.1)' : 'linear-gradient(135deg,#229ED9,#0088cc)', border: testSent ? '1px solid rgba(34,197,94,0.3)' : 'none', color: testSent ? '#16A34A' : '#fff', fontFamily: 'Rajdhani,sans-serif', fontSize: 13, fontWeight: 700, cursor: busy ? 'wait' : 'pointer', opacity: busy ? 0.7 : 1 }}>
          {testSent ? `✅ ${isKhmer ? t('telegram.sent') : 'SENT!'}` : busy ? '...' : `📨 ${isKhmer ? t('telegram.testMessage') : 'TEST MESSAGE'}`}
        </button>
        <button onClick={onDisconnect} disabled={busy} style={{ padding: '10px 16px', borderRadius: 10, background: 'transparent', border: '1px solid rgba(239,68,68,0.3)', color: '#EF4444', fontFamily: 'Rajdhani,sans-serif', fontSize: 13, fontWeight: 700, cursor: busy ? 'wait' : 'pointer', opacity: busy ? 0.7 : 1 }}
          onMouseEnter={e => { e.currentTarget.style.background = 'rgba(239,68,68,0.08)' }}
          onMouseLeave={e => { e.currentTarget.style.background = 'transparent' }}>
          {isKhmer ? t('telegram.disconnect') : 'DISCONNECT'}
        </button>
      </div>
    </div>
  )
}

function StatusBadge({ connected, isKhmer = false, t = (k) => k }) {
  return (
    <div style={{ display: 'inline-flex', alignItems: 'center', gap: 5, padding: '4px 12px', borderRadius: 20, fontSize: 11, fontWeight: 700, letterSpacing: isKhmer ? 0 : 1, background: connected ? 'rgba(34,197,94,0.1)' : 'rgba(239,68,68,0.08)', border: `1px solid ${connected ? 'rgba(34,197,94,0.3)' : 'rgba(239,68,68,0.25)'}`, color: connected ? '#16A34A' : '#DC2626', fontFamily: isKhmer ? 'KantumruyPro,Khmer OS,sans-serif' : 'Rajdhani,sans-serif' }}>
      <span style={{ fontSize: 8 }}>●</span>
      {connected ? (isKhmer ? t('telegram.connected') : 'CONNECTED ✅') : (isKhmer ? t('telegram.notConnected') : 'NOT CONNECTED ❌')}
    </div>
  )
}

function Skeleton({ dark }) {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
      {[80, 100, 60].map(w => <div key={w} style={{ height: 14, width: `${w}%`, borderRadius: 7, background: dark ? '#1F2937' : '#F3F4F6' }} />)}
    </div>
  )
}