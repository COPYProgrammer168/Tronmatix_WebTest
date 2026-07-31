/**
 * src/pages/StaffDashboard.jsx
 *
 * Staff Dashboard — connects to real Laravel API.
 * Professional redesign with clear visual hierarchy.
 *
 * Endpoints:
 *   GET /api/admin/stats         → OverviewTab
 *   GET /api/orders              → OrdersTab
 *   GET /api/products            → ProductsTab
 *   GET /api/admin/users         → UsersTab
 *   GET /api/delivery-schedules  → DeliveryTab
 */
import { useState, useEffect, useCallback, useRef } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import api from '../lib/axios'

// ── Design tokens ────────────────────────────────────────────────────────────
const O = '#F97316'
const G = '#22C55E'
const B = '#3B82F6'
const Y = '#EAB308'
const R = '#EF4444'
const P = '#A855F7'

// ── Theme-aware CSS variables (react to data-theme attribute) ───────────────
// These map to CSS variables set via .staff-light overrides.
// Use 'var(--t-bg)' etc. in inline styles so changing data-theme updates everything.
const SURFACE = 'var(--t-surface)'
const SURFACE_2 = 'var(--t-surface2)'
const BG = 'var(--t-bg)'
const BORDER = 'var(--t-border)'
const BORDER_INPUT = 'var(--t-border-input)'
const TEXT_MUTED = 'var(--t-text-muted)'
const TEXT_FAINT = 'var(--t-text-faint)'
const TEXT_XFAINT = 'var(--t-text-xfaint)'
const TEXT_PRIMARY = 'var(--t-text)'

const CARD_RADIUS = 14
const transition = 'all 0.2s'

// ── Constants ─────────────────────────────────────────────────────────────────
// 'activity' is filtered out for non-admin roles in StaffDashboard — its API
// endpoint (/api/activity-logs) is admin/superadmin only.
const NAV_SECTIONS = [
  {
    label: 'MAIN',
    items: [
      { id: 'overview',  label: 'Dashboard',  icon: '▦', tab: 'overview' },
    ],
  },
  {
    label: 'SALES',
    items: [
      { id: 'orders',    label: 'Orders',    icon: '📦', tab: 'orders' },
      { id: 'products',  label: 'Products',  icon: '🖥️', tab: 'products' },
      { id: 'delivery',  label: 'Delivery',  icon: '🚚', tab: 'delivery' },
    ],
  },
  {
    label: 'USERS',
    items: [
      { id: 'users',     label: 'Users',     icon: '👥', tab: 'users' },
    ],
  },
  {
    label: 'REPORTS',
    items: [
      { id: 'report',    label: 'Reports',   icon: '📊', tab: 'report' },
    ],
  },
  {
    label: 'AUDIT',
    items: [
      { id: 'activity',  label: 'Activity',  icon: '📋', tab: 'activity' },
    ],
  },
]

// Roles allowed to see the Activity tab (matches routes/api.php role:admin,superadmin)
const ADMIN_ROLES = ['admin', 'superadmin']

const STATUS_COLORS = {
  pending:    { bg: 'rgba(234,179,8,0.15)',  color: Y, border: 'rgba(234,179,8,0.3)' },
  confirmed:  { bg: 'rgba(34,197,94,0.12)',  color: G, border: 'rgba(34,197,94,0.3)' },
  processing: { bg: 'rgba(59,130,246,0.15)', color: B, border: 'rgba(59,130,246,0.3)' },
  shipped:    { bg: 'rgba(59,130,246,0.15)', color: B, border: 'rgba(59,130,246,0.3)' },
  delivered:  { bg: 'rgba(249,115,22,0.15)', color: O, border: 'rgba(249,115,22,0.3)' },
  cancelled:  { bg: 'rgba(239,68,68,0.15)',  color: R, border: 'rgba(239,68,68,0.3)' },
  scheduled:  { bg: 'rgba(249,115,22,0.15)', color: O, border: 'rgba(249,115,22,0.3)' },
  en_route:   { bg: 'rgba(59,130,246,0.15)', color: B, border: 'rgba(59,130,246,0.3)' },
}

const ROLE_COLORS = {
  customer:   { bg: 'rgba(156,163,175,0.15)', color: '#9ca3af', border: 'rgba(156,163,175,0.3)' },
  admin:      { bg: 'rgba(249,115,22,0.15)',  color: O, border: 'rgba(249,115,22,0.3)' },
  staff:      { bg: 'rgba(59,130,246,0.15)',  color: B, border: 'rgba(59,130,246,0.3)' },
  superadmin: { bg: 'rgba(168,85,247,0.15)',  color: P, border: 'rgba(168,85,247,0.3)' },
  delivery:   { bg: 'rgba(34,197,94,0.15)',   color: G, border: 'rgba(34,197,94,0.3)' },
}

// ── Shared UI ─────────────────────────────────────────────────────────────────
function Spinner({ color = O, size = 28 }) {
  return (
    <div style={{ padding: 48, display: 'flex', justifyContent: 'center' }}>
      <div style={{ width: size, height: size, border: `3px solid ${color}33`, borderTopColor: color, borderRadius: '50%', animation: 'spin .7s linear infinite' }} />
    </div>
  )
}

function ErrorState({ message, onRetry }) {
  return (
    <div style={{ padding: 40, textAlign: 'center' }}>
      <div style={{ fontSize: 28, marginBottom: 10 }}>⚠️</div>
      <div style={{ fontSize: 14, color: R, fontWeight: 700, marginBottom: 8 }}>Failed to load</div>
      <div style={{ fontSize: 13, color: TEXT_FAINT, marginBottom: 18, maxWidth: 300, margin: '0 auto 18px' }}>{message}</div>
      {onRetry && (
        <button onClick={onRetry} style={{
          padding: '7px 20px', background: 'rgba(239,68,68,0.12)', border: '1px solid rgba(239,68,68,0.3)',
          borderRadius: 8, color: R, fontSize: 12, fontWeight: 700, cursor: 'pointer', letterSpacing: '1px',
        }}>↻ RETRY</button>
      )}
    </div>
  )
}

function EmptyState({ label = 'No data found' }) {
  return (
    <div style={{ padding: 48, textAlign: 'center', fontSize: 14, color: TEXT_FAINT, fontWeight: 600 }}>
      📭 {label}
    </div>
  )
}

function SkeletonRows({ cols = 5, rows = 4 }) {
  return (
    <>
      {Array.from({ length: rows }).map((_, ri) => (
        <tr key={ri}>
          {Array.from({ length: cols }).map((_, ci) => (
            <td key={ci} style={{ padding: '14px 16px' }}>
              <div style={{ height: 12, borderRadius: 5, background: 'rgba(249,115,22,0.1)', animation: `shimmer 1.4s ${ci * 0.1}s ease-in-out infinite` }} />
            </td>
          ))}
        </tr>
      ))}
    </>
  )
}

// ── useFetch hook ─────────────────────────────────────────────────────────────
function useFetch(endpoint) {
  const [data, setData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  const load = useCallback(async () => {
    const ctrl = new AbortController()
    setLoading(true)
    setError(null)
    try {
      const res = await api.get(endpoint, { signal: ctrl.signal })
      const raw = res.data?.data ?? res.data
      setData(raw)
    } catch (err) {
      if (err.name === 'CanceledError' || err.name === 'AbortError') return
      setError(err.response?.data?.message || err.message || 'Request failed.')
    } finally {
      setLoading(false)
    }
    return () => ctrl.abort()
  }, [endpoint])

  useEffect(() => { load() }, [load])
  return { data, loading, error, refetch: load }
}

// ── Badge ─────────────────────────────────────────────────────────────────────
function Badge({ status, map }) {
  const s = map?.[status] || { bg: 'rgba(75,85,99,0.2)', color: '#6B7280', border: 'rgba(75,85,99,0.3)' }
  return (
    <span style={{
      display: 'inline-block', padding: '3px 10px', borderRadius: 20,
      fontSize: 11, fontWeight: 600, letterSpacing: '0.5px', whiteSpace: 'nowrap',
      background: s.bg, color: s.color, border: `1px solid ${s.border}`,
    }}>
      {status?.replace(/_/g, ' ').toUpperCase()}
    </span>
  )
}

// ── Table wrapper ─────────────────────────────────────────────────────────────
function TableBox({ headers, children }) {
  return (
    <div style={{ background: SURFACE, border: `1px solid ${BORDER}`, borderRadius: CARD_RADIUS, overflow: 'hidden' }}>
      <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 14 }}>
        <thead>
          <tr style={{ borderBottom: `1px solid ${BORDER}` }}>
            {headers.map(h => (
              <th key={h} style={{
                padding: '12px 16px', textAlign: 'left', fontSize: 14, color: TEXT_MUTED,
                fontWeight: 700, letterSpacing: '1px', whiteSpace: 'nowrap',
              }}>{h}</th>
            ))}
          </tr>
        </thead>
        <tbody>{children}</tbody>
      </table>
    </div>
  )
}

// ── Modal helpers ─────────────────────────────────────────────────────────────
function ModalOverlay({ onClose, children }) {
  return (
    <div style={{
      position: 'fixed', inset: 0, zIndex: 99000,
      background: 'rgba(0,0,0,0.75)', backdropFilter: 'blur(4px)',
      display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 16,
    }} onClick={e => { if (e.target === e.currentTarget) onClose() }}>
      {children}
    </div>
  )
}

function ModalBox({ children, wide }) {
  return (
    <div style={{
      background: '#141414', border: `1px solid ${BORDER}`, borderRadius: 20,
      width: '100%', maxWidth: wide ? 720 : 520, maxHeight: '90vh', overflow: 'auto',
      padding: '28px 32px', position: 'relative',
    }}>
      {children}
    </div>
  )
}

function Toast({ msg, color }) {
  return (
    <div style={{
      position: 'sticky', top: 0, zIndex: 10,
      padding: '10px 18px', borderRadius: 10, marginBottom: 16,
      background: '#141414', border: `1px solid ${color}55`,
      color, fontSize: 13, fontWeight: 700, letterSpacing: '1px',
      animation: 'slideDown .25s ease',
    }}>✓ {msg}</div>
  )
}

// ── Trend helpers for weekly chart ──────────────────────────────────────────
function getWeeklyTrend(weekly) {
  if (!weekly || weekly.length < 2) return { dir: 'flat', pct: 0 }
  const first = weekly.slice(0, 3).reduce((s, d) => s + d.count, 0) / 3
  const last = weekly.slice(-3).reduce((s, d) => s + d.count, 0) / 3
  if (first === 0) return { dir: 'up', pct: 100 }
  const pct = Math.round(((last - first) / first) * 100)
  return { dir: pct >= 0 ? 'up' : 'down', pct: Math.abs(pct) }
}
function getTrendBg(weekly) { const t = getWeeklyTrend(weekly); return t.dir === 'up' ? 'rgba(34,197,94,0.12)' : 'rgba(239,68,68,0.12)' }
function getTrendColor(weekly) { const t = getWeeklyTrend(weekly); return t.dir === 'up' ? '#22C55E' : '#EF4444' }
function getTrendLabel(weekly) { const t = getWeeklyTrend(weekly); return `${t.dir === 'up' ? '▲' : '▼'} ${t.pct}%` }

function formatTimeAgo(dateStr) {
  const d = new Date(dateStr)
  const now = new Date()
  const diffMs = now - d
  const diffMins = Math.floor(diffMs / 60000)
  if (diffMins < 1) return 'just now'
  if (diffMins < 60) return `${diffMins}m ago`
  const diffHours = Math.floor(diffMins / 60)
  if (diffHours < 24) return `${diffHours}h ago`
  const diffDays = Math.floor(diffHours / 24)
  if (diffDays < 7) return `${diffDays}d ago`
  return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })
}

// ═════════════════════════════════════════════════════════════════════════════
// OVERVIEW TAB
// ═════════════════════════════════════════════════════════════════════════════
function OverviewTab({ setTab }) {
  const { data: stats, loading, error, refetch } = useFetch('/api/admin/stats')
  const chartRef = useRef(null)
  const chartInstanceRef = useRef(null)

  // Load Chart.js from CDN once
  useEffect(() => {
    if (!window.Chart) {
      const script = document.createElement('script')
      script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js'
      script.onload = () => setChartsReady(true)
      document.head.appendChild(script)
    } else {
      setChartsReady(true)
    }
  }, [])

  const [chartsReady, setChartsReady] = useState(false)

  // Weekly Orders chart
  useEffect(() => {
    if (!chartsReady || !stats?.weekly_orders?.length) return
    const canvas = document.getElementById('weeklyChart')
    if (!canvas) return

    if (chartInstanceRef.current) chartInstanceRef.current.destroy()

    const ctx = canvas.getContext('2d')
    const isLight = document.documentElement.getAttribute('data-theme') === 'light'
    const gridColor = isLight ? 'rgba(15,23,42,0.06)' : 'rgba(255,255,255,0.06)'
    const textColor = isLight ? 'rgba(15,23,42,0.45)' : 'rgba(255,255,255,0.35)'

    const labels = stats.weekly_orders.map(d => d.day)
    const counts = stats.weekly_orders.map(d => d.count)

    chartInstanceRef.current = new window.Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Orders',
          data: counts,
          backgroundColor: 'rgba(249,115,22,0.6)',
          borderColor: '#F97316',
          borderWidth: 1,
          borderRadius: 4,
          borderSkipped: false,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: isLight ? '#fff' : '#1A1A1A',
            borderColor: 'rgba(249,115,22,0.4)',
            borderWidth: 1,
            titleColor: '#F97316',
            bodyColor: isLight ? 'rgba(15,23,42,0.75)' : 'rgba(255,255,255,0.8)',
            padding: 10,
            displayColors: false,
            callbacks: { label: (c) => `${c.parsed.y} orders` },
          },
        },
        scales: {
          x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } },
          y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 }, stepSize: 1 }, beginAtZero: true },
        },
      },
    })

    return () => { if (chartInstanceRef.current) chartInstanceRef.current.destroy() }
  }, [stats, chartsReady])

  // User Registrations chart (line)
  const userChartRef = useRef(null)
  useEffect(() => {
    if (!chartsReady || !stats?.monthly_users?.length) return
    const canvas = document.getElementById('userChart')
    if (!canvas) return

    if (userChartRef.current) userChartRef.current.destroy()

    const ctx = canvas.getContext('2d')
    const isLight = document.documentElement.getAttribute('data-theme') === 'light'
    const gridColor = isLight ? 'rgba(15,23,42,0.06)' : 'rgba(255,255,255,0.06)'
    const textColor = isLight ? 'rgba(15,23,42,0.45)' : 'rgba(255,255,255,0.35)'

    userChartRef.current = new window.Chart(ctx, {
      type: 'line',
      data: {
        labels: stats.monthly_labels,
        datasets: [{
          label: 'New Users',
          data: stats.monthly_users,
          borderColor: '#3B82F6',
          backgroundColor: isLight ? 'rgba(59,130,246,0.1)' : 'rgba(59,130,246,0.15)',
          borderWidth: 2,
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#3B82F6',
          pointRadius: 3,
          pointHoverRadius: 6,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: isLight ? '#fff' : '#1A1A1A',
            borderColor: 'rgba(59,130,246,0.4)',
            borderWidth: 1,
            titleColor: '#3B82F6',
            bodyColor: isLight ? 'rgba(15,23,42,0.75)' : 'rgba(255,255,255,0.8)',
            padding: 10,
            displayColors: false,
            callbacks: { label: (c) => `${c.parsed.y} new users` },
          },
        },
        scales: {
          x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 10 } } },
          y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 10 }, stepSize: 1 }, beginAtZero: true },
        },
      },
    })

    return () => { if (userChartRef.current) userChartRef.current.destroy() }
  }, [stats, chartsReady])

  const STAT_DEFS = [
    { key: 'total_orders',   label: 'Total Orders',   deltaKey: 'orders_delta',  color: O, icon: '📦' },
    { key: 'revenue',        label: 'Revenue',        deltaKey: 'revenue_delta', color: G, icon: '💰' },
    { key: 'active_users',   label: 'Active Users',   deltaKey: 'users_delta',   color: B, icon: '👥' },
    { key: 'pending_orders', label: 'Pending Orders', deltaKey: 'pending_delta', color: Y, icon: '⏳' },
  ]

  const QUICK_ACTIONS = [
    { label: 'New Product', icon: '➕', tab: 'products', color: O },
    { label: 'View Orders', icon: '📦', tab: 'orders', color: B },
    { label: 'Manage Users', icon: '👥', tab: 'users', color: P },
  ]

  return (
    <div>
      {/* ── Stat Cards ────────────────────────────────────────────────────── */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(210px, 1fr))', gap: 14, marginBottom: 20 }}>
        {error ? (
          <div style={{ gridColumn: '1/-1' }}><ErrorState message={error} onRetry={refetch} /></div>
        ) : STAT_DEFS.map(s => (
          <div key={s.key} style={{
            background: SURFACE, border: `1px solid ${BORDER}`, borderRadius: CARD_RADIUS,
            padding: '20px 20px', display: 'flex', alignItems: 'center', gap: 16,
            position: 'relative', overflow: 'hidden', transition,
          }}
            onMouseEnter={e => { e.currentTarget.style.borderColor = s.color; e.currentTarget.style.transform = 'translateY(-2px)' }}
            onMouseLeave={e => { e.currentTarget.style.borderColor = ''; e.currentTarget.style.transform = '' }}
          >
            <div style={{
              position: 'absolute', top: 0, left: 0, width: 3, height: '100%',
              background: s.color, borderRadius: '3px 0 0 3px', opacity: 0.6,
            }} />
            <div style={{
              width: 48, height: 48, background: `${s.color}1a`, border: `1px solid ${s.color}33`,
              borderRadius: 12, display: 'flex', alignItems: 'center', justifyContent: 'center',
              flexShrink: 0, fontSize: 22,
            }}>
              {s.icon}
            </div>
            <div style={{ minWidth: 0, flex: 1 }}>
              <div style={{ fontSize: 26, fontWeight: 700, lineHeight: 1.2, whiteSpace: 'nowrap', color: 'var(--t-text)' }}>
                {loading ? (
                  <div style={{ height: 24, width: '60%', background: `${s.color}22`, borderRadius: 5, animation: 'shimmer 1.4s ease-in-out infinite' }} />
                ) : (
                  stats?.[s.key] ?? '—'
                )}
              </div>
              <div style={{ fontSize: 13, fontWeight: 600, letterSpacing: '0.5px', color: TEXT_MUTED, marginTop: 2 }}>
                {s.label}
              </div>
              {!loading && stats?.[s.deltaKey] && (
                <div style={{
                  display: 'inline-flex', alignItems: 'center', gap: 4, marginTop: 4,
                  padding: '1px 8px', borderRadius: 4, fontSize: 11, fontWeight: 700,
                  background: stats[s.deltaKey].startsWith('+') ? 'rgba(34,197,94,0.12)' : 'rgba(239,68,68,0.12)',
                  color: stats[s.deltaKey].startsWith('+') ? G : R,
                }}>
                  {stats[s.deltaKey]} vs last month
                </div>
              )}
            </div>
          </div>
        ))}
      </div>

      {/* ── Quick Actions ─────────────────────────────────────────────────── */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))', gap: 12, marginBottom: 24 }}>
        {QUICK_ACTIONS.map(a => (
          <div key={a.label} onClick={() => setTab(a.tab)} style={{
            background: SURFACE, border: `1px solid ${BORDER}`, borderRadius: CARD_RADIUS,
            padding: '16px 18px', display: 'flex', alignItems: 'center', gap: 12, cursor: 'pointer',
            transition,
          }}
            onMouseEnter={e => { e.currentTarget.style.borderColor = a.color; e.currentTarget.style.background = `${a.color}08` }}
            onMouseLeave={e => { e.currentTarget.style.borderColor = ''; e.currentTarget.style.background = SURFACE }}
          >
            <span style={{ fontSize: 20 }}>{a.icon}</span>
            <span style={{ fontSize: 14, fontWeight: 600, color: '#fff' }}>{a.label}</span>
            <span style={{ marginLeft: 'auto', color: TEXT_FAINT, fontSize: 14 }}>→</span>
          </div>
        ))}
      </div>

      {/* ── Weekly Chart ──────────────────────────────────────────────────── */}
      <div style={{ background: SURFACE, border: `1px solid ${BORDER}`, borderRadius: CARD_RADIUS, padding: '20px 22px' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 18, flexWrap: 'wrap', gap: 8 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <span style={{ fontSize: 15, fontWeight: 700, color: '#fff', letterSpacing: '0.5px' }}>📊 Weekly Orders</span>
            {!loading && stats?.weekly_orders?.length > 1 && (
              <span style={{
                display: 'inline-flex', alignItems: 'center', gap: 3,
                padding: '1px 8px', borderRadius: 4, fontSize: 11, fontWeight: 700,
                background: getTrendBg(stats.weekly_orders), color: getTrendColor(stats.weekly_orders),
              }}>
                {getTrendLabel(stats.weekly_orders)} vs last week
              </span>
            )}
          </div>
          <span style={{ fontSize: 11, color: TEXT_FAINT }}>Last 7 days</span>
        </div>
        {loading ? (
          <Spinner />
        ) : !stats?.weekly_orders?.length ? (
          <EmptyState label="No weekly data" />
        ) : (
          <div style={{ position: 'relative', width: '100%', height: 220 }}>
            <canvas id="weeklyChart" style={{ width: '100%', height: '100%' }} />
          </div>
        )}
      </div>

      {/* ── User Registrations Chart ─────────────────────────────────────── */}
      <div style={{ background: SURFACE, border: `1px solid ${BORDER}`, borderRadius: CARD_RADIUS, padding: '20px 22px', marginTop: 16 }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 18 }}>
          <span style={{ fontSize: 15, fontWeight: 700, color: '#fff', letterSpacing: '0.5px' }}>👤 New User Registrations</span>
          <span style={{ fontSize: 11, color: TEXT_FAINT }}>Last 12 months</span>
        </div>
        {loading ? (
          <Spinner />
        ) : !stats?.monthly_users?.length ? (
          <EmptyState label="No registration data" />
        ) : (
          <div style={{ position: 'relative', width: '100%', height: 200 }}>
            <canvas id="userChart" style={{ width: '100%', height: '100%' }} />
          </div>
        )}
      </div>
    </div>
  )
}

// ═════════════════════════════════════════════════════════════════════════════
// ORDER DETAIL MODAL
// ═════════════════════════════════════════════════════════════════════════════
function OrderDetailModal({ orderId, onClose, onUpdated }) {
  const { data: order, loading, error, refetch } = useFetch(`/api/orders/${orderId}`)
  const [toast, setToast] = useState(null)

  const showToast = (msg, color) => { setToast({ msg, color }); setTimeout(() => setToast(null), 3500) }

  const updateStatus = async (status) => {
    try {
      await api.put(`/api/orders/${orderId}/status`, { status })
      showToast(`Status → ${status.toUpperCase()} ✅`, G)
      refetch(); onUpdated?.()
    } catch (err) { showToast(err.response?.data?.message || 'Failed to update status.', R) }
  }

  const verifyPayment = async () => {
    try {
      await api.post(`/api/orders/${orderId}/verify-payment`)
      showToast('Payment verified ✅', G)
      refetch(); onUpdated?.()
    } catch (err) { showToast(err.response?.data?.message || 'Failed to verify payment.', R) }
  }

  const confirmDelivery = async () => {
    try {
      await api.post(`/api/orders/${orderId}/staff-confirm-delivery`)
      showToast('Delivery confirmed ✅', O)
      refetch(); onUpdated?.()
    } catch (err) { showToast(err.response?.data?.message || 'Failed to confirm delivery.', R) }
  }

  if (!order && loading) return null
  if (error) return <ModalOverlay onClose={onClose}><ModalBox><ErrorState message={error} onRetry={refetch} /></ModalBox></ModalOverlay>
  if (!order) return null

  const o = order
  const isPickup = o.fulfillment_type === 'pickup'
  const ship = o.shipping || o.location || {}
  const deliveryFlow = ['pending', 'confirmed', 'processing', 'shipped', 'delivered']
  const pickupFlow = ['pending', 'confirmed', 'processing', 'delivered']
  const statusFlow = isPickup ? pickupFlow : deliveryFlow
  const currentIdx = statusFlow.indexOf(o.status)
  const canUpdate = currentIdx >= 0 && currentIdx < statusFlow.length - 1
  const getNextStatus = () => canUpdate ? statusFlow[currentIdx + 1] : null

  return (
    <ModalOverlay onClose={onClose}>
      <ModalBox wide>
        {toast && <Toast msg={toast.msg} color={toast.color} />}

        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 20, flexWrap: 'wrap', gap: 8 }}>
          <div>
            <div style={{ display: 'flex', alignItems: 'center', gap: 10, flexWrap: 'wrap' }}>
              <span style={{ fontSize: 22, fontWeight: 800, color: O, fontFamily: 'monospace', letterSpacing: 1 }}>
                {o.order_id ?? '#' + o.id}
              </span>
              <Badge status={o.status} map={STATUS_COLORS} />
              <span style={{
                display: 'inline-block', padding: '2px 10px', borderRadius: 20,
                fontSize: 11, fontWeight: 600,
                background: isPickup ? 'rgba(99,102,241,0.12)' : 'rgba(34,197,94,0.12)',
                color: isPickup ? '#6366F1' : G,
                border: `1px solid ${isPickup ? 'rgba(99,102,241,0.3)' : 'rgba(34,197,94,0.3)'}`,
              }}>{isPickup ? 'PICKUP' : 'DELIVERY'}</span>
              {o.payment_status === 'paid' && (
                <span style={{ display: 'inline-block', padding: '2px 10px', borderRadius: 20, fontSize: 11, fontWeight: 600, background: 'rgba(34,197,94,0.12)', color: G, border: '1px solid rgba(34,197,94,0.3)' }}>PAID</span>
              )}
            </div>
            <div style={{ fontSize: 12, color: TEXT_FAINT, marginTop: 4 }}>Created {new Date(o.created_at).toLocaleString()}</div>
          </div>
          <button onClick={onClose} style={{ width: 36, height: 36, borderRadius: 10, border: `1px solid ${BORDER_INPUT}`, background: 'transparent', color: TEXT_FAINT, fontSize: 18, cursor: 'pointer', flexShrink: 0 }}>×</button>
        </div>

        {/* Status progress */}
        <div style={{ display: 'flex', gap: 0, marginBottom: 24, overflow: 'hidden', borderRadius: 10, border: `1px solid ${BORDER}`, flexWrap: 'wrap' }}>
          {statusFlow.map((s, i) => {
            const done = i <= currentIdx
            return (
              <div key={s} style={{
                flex: 1, padding: '8px 6px', textAlign: 'center', minWidth: 60,
                background: done ? 'rgba(249,115,22,0.12)' : 'transparent',
                borderRight: i < statusFlow.length - 1 ? `1px solid ${BORDER}` : 'none',
              }}>
                <div style={{ fontSize: 10, color: done ? O : TEXT_XFAINT, fontWeight: 700, letterSpacing: '0.5px' }}>
                  {isPickup && s === 'delivered' ? 'PICKED UP' : s.toUpperCase()}
                </div>
              </div>
            )
          })}
        </div>

        {/* Info cards */}
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14, marginBottom: 20 }}>
          <InfoCard title="CUSTOMER">
            <div style={{ fontSize: 14, color: 'var(--t-text)', fontWeight: 600 }}>{o.user?.name ?? o.user?.username ?? '—'}</div>
            <div style={{ fontSize: 12, color: TEXT_MUTED }}>{o.user?.email ?? '—'}</div>
            <div style={{ fontSize: 12, color: TEXT_FAINT, marginTop: 4 }}>Phone: {ship.phone ?? '—'}</div>
          </InfoCard>
          <InfoCard title="PAYMENT">
            <div style={{ fontSize: 14, color: 'var(--t-text)' }}>{o.payment_method?.toUpperCase() ?? '—'}</div>
            <div style={{ fontSize: 12, color: TEXT_MUTED }}>Status: <span style={{ color: o.payment_status === 'paid' ? G : Y }}>{o.payment_status?.toUpperCase()}</span></div>
            <div style={{ fontSize: 12, color: TEXT_FAINT, marginTop: 4 }}>{isPickup ? 'In-store pickup' : 'Delivery order'}</div>
          </InfoCard>
          {!isPickup && ship.name ? (
            <InfoCard title="SHIPPING ADDRESS">
              <div style={{ fontSize: 13, color: 'var(--t-text-muted)' }}>{ship.name}</div>
              <div style={{ fontSize: 12, color: TEXT_MUTED }}>{ship.address ?? ''}{ship.city ? ', ' + ship.city : ''}</div>
              {ship.note && <div style={{ fontSize: 12, color: TEXT_FAINT, fontStyle: 'italic', marginTop: 4 }}>Note: {ship.note}</div>}
            </InfoCard>
          ) : isPickup ? (
            <InfoCard title="PICKUP LOCATION">
              <div style={{ fontSize: 13, color: 'var(--t-text-muted)' }}>Tronmatix Computer</div>
              <div style={{ fontSize: 12, color: TEXT_MUTED }}>Store Pickup — {ship.name || 'Customer'}</div>
            </InfoCard>
          ) : null}
          {!isPickup && (o.delivery_date || o.delivery_time_slot) && (
            <InfoCard title="DELIVERY SCHEDULE">
              {o.delivery_date && <div style={{ fontSize: 13, color: 'var(--t-text-muted)' }}>Date: {o.delivery_date}</div>}
              {o.delivery_time_slot && <div style={{ fontSize: 13, color: 'var(--t-text-muted)' }}>Slot: {o.delivery_time_slot}</div>}
            </InfoCard>
          )}
        </div>

        {/* Items table */}
        <div style={{ background: SURFACE_2, borderRadius: 10, border: `1px solid ${BORDER}`, overflow: 'hidden', marginBottom: 20 }}>
          <div style={{ padding: '12px 14px', borderBottom: `1px solid ${BORDER}`, fontSize: 11, color: TEXT_FAINT, fontWeight: 700, letterSpacing: '1px' }}>ITEMS ({o.items?.length ?? 0})</div>
          <div style={{ overflowX: 'auto' }}>
            <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 13, minWidth: 400 }}>
              <thead>
                <tr style={{ borderBottom: `1px solid ${BORDER}` }}>
                  <th style={{ padding: '10px 14px', textAlign: 'left', color: TEXT_FAINT, fontWeight: 600, fontSize: 11, letterSpacing: '0.5px' }}>PRODUCT</th>
                  <th style={{ padding: '10px 14px', textAlign: 'right', color: TEXT_FAINT, fontWeight: 600, fontSize: 11, letterSpacing: '0.5px' }}>PRICE</th>
                  <th style={{ padding: '10px 14px', textAlign: 'center', color: TEXT_FAINT, fontWeight: 600, fontSize: 11, letterSpacing: '0.5px' }}>QTY</th>
                  <th style={{ padding: '10px 14px', textAlign: 'right', color: TEXT_FAINT, fontWeight: 600, fontSize: 11, letterSpacing: '0.5px' }}>TOTAL</th>
                </tr>
              </thead>
              <tbody>
                {o.items?.map((item, i) => (
                  <tr key={item.id || i} style={{ borderBottom: `1px solid ${BORDER}` }}>
                    <td style={{ padding: '10px 14px', color: 'var(--t-text)' }}>{item.name}</td>
                    <td style={{ padding: '10px 14px', textAlign: 'right', color: TEXT_MUTED }}>${Number(item.price ?? 0).toLocaleString()}</td>
                    <td style={{ padding: '10px 14px', textAlign: 'center', color: TEXT_MUTED }}>{item.qty}</td>
                    <td style={{ padding: '10px 14px', textAlign: 'right', color: G, fontWeight: 700 }}>${Number((item.price ?? 0) * (item.qty ?? 0)).toLocaleString()}</td>
                  </tr>
                ))}
              </tbody>
              <tfoot>
                <tr><td colSpan={3} style={{ padding: '10px 14px', textAlign: 'right', fontSize: 12, color: TEXT_MUTED }}>Subtotal</td><td style={{ padding: '10px 14px', textAlign: 'right', color: 'var(--t-text-muted)' }}>${Number(o.subtotal ?? 0).toLocaleString()}</td></tr>
                {o.discount_amount > 0 && (
                  <tr><td colSpan={3} style={{ padding: '2px 14px', textAlign: 'right', fontSize: 12, color: Y }}>Discount {o.discount_code ? `(${o.discount_code})` : ''}</td><td style={{ padding: '2px 14px', textAlign: 'right', color: Y }}>-${Number(o.discount_amount).toLocaleString()}</td></tr>
                )}
                <tr><td colSpan={3} style={{ padding: '10px 14px', textAlign: 'right', fontSize: 14, color: 'var(--t-text)', fontWeight: 700 }}>TOTAL</td><td style={{ padding: '10px 14px', textAlign: 'right', fontSize: 16, color: O, fontWeight: 800 }}>${Number(o.total ?? 0).toLocaleString()}</td></tr>
              </tfoot>
            </table>
          </div>
        </div>

        {/* Actions */}
        <div style={{ display: 'flex', gap: 10, flexWrap: 'wrap' }}>
          {canUpdate && getNextStatus() && (
            <ActionBtn onClick={() => updateStatus(getNextStatus())} color={O} bg>
              MARK AS {isPickup && getNextStatus() === 'delivered' ? 'PICKED UP' : getNextStatus().toUpperCase()}
            </ActionBtn>
          )}
          {o.payment_status !== 'paid' && (
            <ActionBtn onClick={verifyPayment} color={G}>
              ✓ VERIFY PAYMENT
            </ActionBtn>
          )}
          {!isPickup && o.status !== 'delivered' && o.status !== 'cancelled' && o.payment_status === 'paid' && (
            <ActionBtn onClick={confirmDelivery} color={B}>
              🚚 CONFIRM DELIVERY
            </ActionBtn>
          )}
          {isPickup && o.status === 'processing' && (
            <ActionBtn onClick={() => updateStatus('delivered')} color="#6366F1">
              📦 MARK AS PICKED UP
            </ActionBtn>
          )}
          <button onClick={onClose} style={{
            padding: '9px 20px', borderRadius: 8, border: `1px solid ${BORDER_INPUT}`,
            background: 'transparent', color: TEXT_MUTED, fontSize: 13, fontWeight: 700, cursor: 'pointer', letterSpacing: '1px',
            marginLeft: 'auto',
          }}>CLOSE</button>
        </div>
      </ModalBox>
    </ModalOverlay>
  )
}

function InfoCard({ title, children }) {
  return (
    <div style={{ background: SURFACE_2, borderRadius: 10, padding: 14, border: `1px solid ${BORDER}` }}>
      <div style={{ fontSize: 11, color: TEXT_FAINT, fontWeight: 700, letterSpacing: '1px', marginBottom: 8 }}>{title}</div>
      {children}
    </div>
  )
}

function ActionBtn({ onClick, color, bg, children }) {
  return (
    <button onClick={onClick} style={{
      padding: '9px 20px', borderRadius: 8,
      border: bg ? 'none' : `1px solid ${color}55`,
      background: bg ? color : `${color}15`,
      color: bg ? '#fff' : color,
      fontSize: 13, fontWeight: 700, cursor: 'pointer', letterSpacing: '1px',
    }}>{children}</button>
  )
}

// ═════════════════════════════════════════════════════════════════════════════
// ORDERS TAB
// ═════════════════════════════════════════════════════════════════════════════
function OrdersTab() {
  const [filter, setFilter] = useState('all')
  const [selectedOrderId, setSelectedOrderId] = useState(null)
  const { data: orders, loading, error, refetch } = useFetch('/api/orders?per_page=100')

  const statuses = ['all', 'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled']
  const filtered = !orders ? [] : filter === 'all' ? orders : orders.filter(o => o.status === filter)

  // Status counts for summary strip
  const counts = !orders ? {} : orders.reduce((acc, o) => { acc[o.status] = (acc[o.status] || 0) + 1; return acc }, {})

  return (
    <div style={{ width: '100%', minWidth: 0 }}>
      {selectedOrderId && (
        <OrderDetailModal orderId={selectedOrderId} onClose={() => setSelectedOrderId(null)} onUpdated={refetch} />
      )}

      {/* Status summary strip */}
      <div style={{
        display: 'flex', gap: 12, marginBottom: 14, flexWrap: 'wrap',
        padding: '10px 14px', background: SURFACE, border: `1px solid ${BORDER}`, borderRadius: 10,
      }}>
        {statuses.filter(s => s !== 'all').map(s => {
          const sc = STATUS_COLORS[s] || { color: TEXT_MUTED }
          const count = counts[s] ?? 0
          return (
            <div key={s} style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
              <span style={{
                width: 8, height: 8, borderRadius: '50%',
                background: sc.color, opacity: 0.6,
              }} />
              <span style={{ fontSize: 12, color: TEXT_MUTED, fontWeight: 600 }}>{count}</span>
              <span style={{ fontSize: 11, color: TEXT_FAINT }}>{s}</span>
            </div>
          )
        })}
      </div>

      {/* Filters */}
      <div style={{ display: 'flex', gap: 6, marginBottom: 16, flexWrap: 'wrap', alignItems: 'center' }}>
        <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap', flex: 1 }}>
          {statuses.map(s => {
            const active = filter === s
            return (
              <button key={s} onClick={() => setFilter(s)} style={{
                padding: '4px 12px', borderRadius: 8, border: '1px solid', fontSize: 11, fontWeight: 700, cursor: 'pointer', letterSpacing: '0.5px',
                borderColor: active ? O : BORDER_INPUT,
                background: active ? 'rgba(249,115,22,0.08)' : 'transparent',
                color: active ? O : TEXT_MUTED,
                transition,
              }}>{s.toUpperCase()}</button>
            )
          })}
        </div>
        <button onClick={refetch} style={{ padding: '4px 12px', borderRadius: 8, border: `1px solid ${BORDER_INPUT}`, background: 'transparent', color: TEXT_FAINT, fontSize: 11, cursor: 'pointer' }}>↻</button>
      </div>

      {error ? <ErrorState message={error} onRetry={refetch} /> : (
        <div style={{ overflowX: 'auto', width: '100%' }}>
          <TableBox headers={['ORDER ID','CUSTOMER','ITEMS','TOTAL','STATUS','DATE']}>
            {loading ? <SkeletonRows cols={6} rows={5} /> : filtered.length === 0 ? (
              <tr><td colSpan={6}><EmptyState label={`No ${filter === 'all' ? '' : filter + ' '}orders`} /></td></tr>
            ) : filtered.map((o, i) => (
              <tr key={o.id} onClick={() => setSelectedOrderId(o.id)} style={{
                borderBottom: `1px solid ${BORDER}`, cursor: 'pointer', transition,
                background: i % 2 ? 'rgba(255,255,255,0.015)' : 'transparent',
              }}
                onMouseEnter={e => e.currentTarget.style.background = 'rgba(249,115,22,0.06)'}
                onMouseLeave={e => e.currentTarget.style.background = i % 2 ? 'rgba(255,255,255,0.015)' : 'transparent'}
              >
                <td style={{ padding: '12px 16px', fontSize: 13, color: O, fontWeight: 700, fontFamily: 'monospace' }}>{o.order_id ?? '#' + o.id}</td>
                <td style={{ padding: '12px 16px', fontSize: 14, color: 'var(--t-text)' }}>{o.user?.name ?? o.user?.username ?? '—'}</td>
                <td style={{ padding: '12px 16px', fontSize: 13, color: TEXT_MUTED }}>{o.items?.length ? `${o.items.length} item${o.items.length > 1 ? 's' : ''}` : '—'}</td>
                <td style={{ padding: '12px 16px', fontSize: 14, color: G, fontWeight: 700 }}>${Number(o.total ?? 0).toLocaleString()}</td>
                <td style={{ padding: '12px 16px' }}><Badge status={o.status} map={STATUS_COLORS} /></td>
                <td style={{ padding: '12px 16px', fontSize: 11, color: TEXT_FAINT, whiteSpace: 'nowrap' }}>{o.created_at ? new Date(o.created_at).toLocaleDateString() : '—'}</td>
              </tr>
            ))}
          </TableBox>
        </div>
      )}
    </div>
  )
}

// ═════════════════════════════════════════════════════════════════════════════
// PRODUCTS TAB + FORM
// ═════════════════════════════════════════════════════════════════════════════
const CATEGORIES = [
  { label: 'PC BUILDS', options: ['PC BUILD UNDER 1K', 'PC BUILD UNDER 2K', 'PC BUILD UNDER 3K', 'PC BUILD UNDER 4K', 'PC BUILD UNDER 5K', 'PC BUILD 5K UP'] },
  { label: 'MONITOR', options: ['MONITOR 25INCH', 'MONITOR 27INCH', 'MONITOR 32INCH', 'MONITOR 34INCH', 'MONITOR 39INCH', 'MONITOR 42INCH', 'MONITOR 48INCH', 'MONITOR 49INCH'] },
  { label: 'PC PARTS', options: ['CPU', 'RAM', 'MAINBOARD', 'COOLING', 'M2', 'VGA', 'CASE', 'POWER SUPPLY', 'FAN'] },
  { label: 'HOT ITEM', options: ['BEST PRICE', 'BEST SET'] },
  { label: 'ACCESSORY', options: ['KEYBOARD', 'MOUSE', 'HEADSET', 'EARPHONE', 'MONITOR STAND', 'SPEAKER', 'MICROPHONE', 'WEBCAM', 'MOUSEPAD', 'LIGHTBAR', 'ROUTER'] },
  { label: 'TABLE CHAIR', options: ['DX RACER', 'SECRETLAB', 'RAZER', 'CONSAIR', 'FANTECH', 'COOLER MASTER', 'TTR RACING'] },
  { label: 'RESELL ITEM', options: ['Second hand', 'Used', 'Pre-owned'] },
]
const STOCK_STATUSES = ['Available InStock Now', 'Pre-order']

function ProductFormModal({ product, onClose, onSaved }) {
  const [form, setForm] = useState({
    name: product?.name ?? '', category: product?.category ?? '', brand: product?.brand ?? '',
    brand_pc_part: product?.brand_pc_part ?? '', price: product?.price ?? '', stock: product?.stock ?? '',
    stock_status: product?.stock_status ?? '', stock_details: product?.stock_details ?? '',
    description: product?.description ?? '', caption: product?.caption ?? '', warranty: product?.warranty ?? '',
    is_featured: product?.is_featured ?? false, is_hot: product?.is_hot ?? false,
    images: product?.all_images ?? [],
  })
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState(null)

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target
    setForm(f => ({ ...f, [name]: type === 'checkbox' ? checked : value }))
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setSaving(true)
    setError(null)
    try {
      const payload = { ...form }
      if (payload.price) payload.price = parseFloat(payload.price) || 0
      if (payload.stock) payload.stock = parseInt(payload.stock, 10) || 0
      if (!payload.brand_pc_part?.trim()) delete payload.brand_pc_part
      payload.image = payload.images?.[0] || null
      if (product) await api.put(`/api/products/${product.id}`, payload)
      else await api.post('/api/products', payload)
      onSaved()
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to save product.')
    } finally { setSaving(false) }
  }

  const handleImageUpload = (e) => {
    const files = Array.from(e.target.files)
    if (!files.length) return
    const remaining = 8 - form.images.length
    Promise.all(files.slice(0, remaining).map(f => new Promise(r => { const fr = new FileReader(); fr.onload = () => r(fr.result); fr.readAsDataURL(f) }))).then(urls => {
      setForm(f => ({ ...f, images: [...f.images, ...urls] }))
    })
    e.target.value = ''
  }

  const removeImage = (idx) => setForm(f => ({ ...f, images: f.images.filter((_, i) => i !== idx) }))
  const addImageUrl = () => {
    const url = document.getElementById('imgUrlInput')?.value?.trim()
    if (!url) return; setForm(f => ({ ...f, images: [...f.images, url] })); document.getElementById('imgUrlInput').value = ''
  }

  return (
    <ModalOverlay onClose={onClose}>
      <ModalBox wide>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24 }}>
          <div style={{ fontSize: 20, fontWeight: 700, color: '#fff', letterSpacing: '1px' }}>{product ? 'EDIT PRODUCT' : 'ADD PRODUCT'}</div>
          <button onClick={onClose} style={{ width: 36, height: 36, borderRadius: 10, border: `1px solid ${BORDER_INPUT}`, background: 'transparent', color: TEXT_FAINT, fontSize: 18, cursor: 'pointer' }}>×</button>
        </div>
        {error && <div style={{ padding: '10px 14px', background: 'rgba(239,68,68,0.1)', border: '1px solid rgba(239,68,68,0.3)', borderRadius: 8, color: '#fca5a5', fontSize: 13, marginBottom: 18 }}>⚠ {error}</div>}

        <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
            <Field label="NAME *"><input name="name" value={form.name} onChange={handleChange} required style={inp} placeholder="Product name" /></Field>
            <Field label="CATEGORY *">
              <select name="category" value={form.category} onChange={handleChange} required style={sel}>
                <option value="" disabled>— Select Category —</option>
                {CATEGORIES.map(g => (
                  <optgroup key={g.label} label={`─── ${g.label} ───────────────`}>
                    {g.options.map(o => <option key={o} value={o}>{o}</option>)}
                  </optgroup>
                ))}
              </select>
            </Field>
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
            <Field label="PRICE *"><input name="price" value={form.price} onChange={handleChange} required style={inp} placeholder="0.00" type="number" step="0.01" /></Field>
            <Field label="STOCK"><input name="stock" value={form.stock} onChange={handleChange} style={inp} placeholder="0 = Out of stock, blank = Unlimited" type="number" min="0" /></Field>
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
            <Field label="BRAND"><input name="brand" value={form.brand} onChange={handleChange} style={inp} placeholder="e.g. Intel, AMD" /></Field>
            <Field label="WARRANTY"><input name="warranty" value={form.warranty} onChange={handleChange} style={inp} placeholder="e.g. 2 years" /></Field>
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
            <Field label="STOCK STATUS">
              <select name="stock_status" value={form.stock_status} onChange={handleChange} style={sel}>
                <option value="">— Select Status —</option>
                {STOCK_STATUSES.map(s => <option key={s} value={s}>{s}</option>)}
              </select>
            </Field>
            <Field label="STOCK DETAILS"><input name="stock_details" value={form.stock_details} onChange={handleChange} style={inp} placeholder="e.g. Arriving next week" /></Field>
          </div>
          <Field label="CAPTION"><input name="caption" value={form.caption} onChange={handleChange} style={inp} placeholder="Short caption" /></Field>

          {/* Image upload */}
          <Field label={`PRODUCT IMAGES (${form.images?.length ?? 0}/8)`}>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill,minmax(80px,1fr))', gap: 6, marginBottom: 8 }}>
              {form.images?.map((img, idx) => (
                <div key={idx} style={{ position: 'relative', aspectRatio: '1/1', borderRadius: 8, overflow: 'hidden', border: idx === 0 ? '2px solid #F97316' : `1px solid ${BORDER_INPUT}`, background: SURFACE_2 }}>
                  <img src={img} alt="" style={{ width: '100%', height: '100%', objectFit: 'cover' }} onError={e => { e.target.style.display = 'none' }} />
                  {idx === 0 && <span style={{ position: 'absolute', top: 2, left: 2, fontSize: 8, fontWeight: 700, background: O, color: '#fff', padding: '1px 5px', borderRadius: 3 }}>COVER</span>}
                  <button type="button" onClick={() => removeImage(idx)} style={{ position: 'absolute', top: 2, right: 2, width: 18, height: 18, borderRadius: '50%', background: 'rgba(0,0,0,0.6)', color: '#fff', border: 'none', cursor: 'pointer', fontSize: 10, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>×</button>
                </div>
              ))}
              {form.images?.length < 8 && (
                <label style={{ aspectRatio: '1/1', borderRadius: 8, border: '2px dashed rgba(255,255,255,0.12)', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', cursor: 'pointer', fontSize: 11, color: TEXT_XFAINT, background: SURFACE_2, transition }}
                  onMouseEnter={e => e.currentTarget.style.borderColor = 'rgba(249,115,22,0.4)'}
                  onMouseLeave={e => e.currentTarget.style.borderColor = ''}>
                  <input type="file" accept="image/*" onChange={handleImageUpload} style={{ display: 'none' }} />
                  <span style={{ fontSize: 20, lineHeight: 1 }}>+</span>
                  <span style={{ marginTop: 2 }}>Upload</span>
                </label>
              )}
            </div>
            {form.images?.length < 8 && (
              <div style={{ display: 'flex', gap: 6 }}>
                <input id="imgUrlInput" type="text" style={inp} placeholder="https://example.com/image.jpg" />
                <button type="button" onClick={addImageUrl} style={{ padding: '9px 14px', borderRadius: 8, border: `1px solid ${BORDER_INPUT}`, background: 'transparent', color: TEXT_MUTED, fontSize: 12, fontWeight: 700, cursor: 'pointer', whiteSpace: 'nowrap' }}>+ URL</button>
              </div>
            )}
          </Field>

          <Field label="DESCRIPTION"><textarea name="description" value={form.description} onChange={handleChange} style={{ ...inp, minHeight: 80, resize: 'vertical' }} placeholder="Product description..." /></Field>

          <div style={{ display: 'flex', gap: 24, padding: '8px 0' }}>
            <label style={{ display: 'flex', alignItems: 'center', gap: 10, fontSize: 13, color: TEXT_MUTED, cursor: 'pointer' }}>
              <input type="checkbox" name="is_featured" checked={form.is_featured} onChange={handleChange} style={{ width: 16, height: 16, accentColor: O }} />
              ⭐ Featured Product
            </label>
            <label style={{ display: 'flex', alignItems: 'center', gap: 10, fontSize: 13, color: TEXT_MUTED, cursor: 'pointer' }}>
              <input type="checkbox" name="is_hot" checked={form.is_hot} onChange={handleChange} style={{ width: 16, height: 16, accentColor: O }} />
              🔥 Hot Item
            </label>
          </div>

          <div style={{ display: 'flex', gap: 12, justifyContent: 'flex-end', marginTop: 8, borderTop: `1px solid ${BORDER}`, paddingTop: 16 }}>
            <button type="button" onClick={onClose} style={{ padding: '10px 24px', borderRadius: 8, border: `1px solid ${BORDER_INPUT}`, background: 'transparent', color: TEXT_MUTED, fontSize: 13, fontWeight: 700, cursor: 'pointer', letterSpacing: '1px' }}>CANCEL</button>
            <button type="submit" disabled={saving} style={{ padding: '10px 24px', borderRadius: 8, border: 'none', background: O, color: '#fff', fontSize: 13, fontWeight: 700, cursor: 'pointer', letterSpacing: '1px', opacity: saving ? 0.6 : 1 }}>{saving ? 'SAVING...' : product ? 'UPDATE' : 'CREATE'}</button>
          </div>
        </form>
      </ModalBox>
    </ModalOverlay>
  )
}

function Field({ label, children }) {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
      <label style={{ fontSize: 12, color: TEXT_FAINT, fontWeight: 600, letterSpacing: '0.5px' }}>{label}</label>
      {children}
    </div>
  )
}

const inp = {
  width: '100%', padding: '9px 12px', background: SURFACE_2,
  border: `1px solid ${BORDER_INPUT}`, borderRadius: 8, color: '#fff',
  fontSize: 13, outline: 'none', fontFamily: "'Rajdhani', sans-serif", fontWeight: 500,
  boxSizing: 'border-box',
}
const sel = { ...inp, cursor: 'pointer' }

function ProductsTab() {
  const [search, setSearch] = useState('')
  const [modal, setModal] = useState(null)
  const { data: products, loading, error, refetch } = useFetch('/api/products?per_page=100')

  const filtered = !products ? [] : products.filter(p => p.name?.toLowerCase().includes(search.toLowerCase()))
  const handleDelete = async (id, name) => {
    if (!window.confirm(`Delete "${name}"?`)) return
    try { await api.delete(`/api/products/${id}`); refetch() } catch (err) { alert(err.response?.data?.message || 'Failed.') }
  }

  return (
    <div>
      {modal && <ProductFormModal product={modal.type === 'edit' ? modal.product : null} onClose={() => setModal(null)} onSaved={() => { setModal(null); refetch() }} />}

      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 14, flexWrap: 'wrap', gap: 8 }}>
        <span style={{ fontSize: 13, color: TEXT_FAINT }}>{!loading && products ? `${products.length} products` : ''}</span>
        <div style={{ display: 'flex', gap: 10, flex: 1, maxWidth: 400 }}>
          <input value={search} onChange={e => setSearch(e.target.value)} placeholder="Search products..." style={{ flex: 1, padding: '8px 12px', background: SURFACE_2, border: `1px solid ${BORDER_INPUT}`, borderRadius: 8, color: '#fff', fontSize: 13, outline: 'none', fontFamily: "'Rajdhani', sans-serif", fontWeight: 500 }} />
          <button onClick={refetch} style={{ padding: '8px 12px', borderRadius: 8, border: `1px solid ${BORDER_INPUT}`, background: 'transparent', color: TEXT_FAINT, fontSize: 13, cursor: 'pointer' }}>↻</button>
          <button onClick={() => setModal({ type: 'add' })} style={{ padding: '8px 16px', borderRadius: 8, border: 'none', background: O, color: '#fff', fontSize: 13, cursor: 'pointer', letterSpacing: '1px', whiteSpace: 'nowrap' }}>+ ADD</button>
        </div>
      </div>

      {error ? <ErrorState message={error} onRetry={refetch} /> : (
        <div style={{ overflowX: 'auto' }}>
          <TableBox headers={['IMAGE','NAME','CATEGORY','PRICE','STOCK','UPDATED','ACTIONS']}>
            {loading ? <SkeletonRows cols={7} rows={4} /> : filtered.length === 0 ? (
              <tr><td colSpan={7}><EmptyState label="No products found" /></td></tr>
            ) : filtered.map((p, i) => {
              const thumb = p.all_images?.[0] || p.image
              return (
              <tr key={p.id} style={{ borderBottom: `1px solid ${BORDER}`, transition, background: i % 2 ? 'rgba(255,255,255,0.015)' : 'transparent' }}
                onMouseEnter={e => e.currentTarget.style.background = 'rgba(255,255,255,0.04)'}
                onMouseLeave={e => e.currentTarget.style.background = i % 2 ? 'rgba(255,255,255,0.015)' : 'transparent'}
              >
                <td style={{ padding: '12px 16px' }}>
                  {thumb ? (
                    <img src={thumb} alt="" style={{ width: 42, height: 42, borderRadius: 8, objectFit: 'cover', background: SURFACE_2, border: `1px solid ${BORDER}` }}
                      onError={e => { e.target.style.display = 'none'; e.target.nextElementSibling.style.display = 'flex' }} />
                  ) : (
                    <div style={{ width: 42, height: 42, borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 16, background: SURFACE_2, border: `1px solid ${BORDER}` }}>📦</div>
                  )}
                </td>
                <td style={{ padding: '12px 16px', fontSize: 14, color: 'var(--t-text)', fontWeight: 600 }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 6, flexWrap: 'wrap' }}>
                    {p.name}
                    {p.is_hot && <BadgePill color={R} label="HOT" />}
                    {p.is_featured && <BadgePill color={O} label="FEATURED" />}
                  </div>
                </td>
                <td style={{ padding: '12px 16px' }}><Badge status={p.category} map={null} /></td>
                <td style={{ padding: '12px 16px', fontSize: 14, color: G, fontWeight: 700 }}>${Number(p.price ?? 0).toLocaleString()}</td>
                <td style={{ padding: '12px 16px' }}>
                  <div style={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                    <span style={{
                      display: 'inline-block', padding: '2px 8px', borderRadius: 4, fontSize: 12, fontWeight: 700,
                      background: (p.stock ?? 0) <= 0 ? 'rgba(239,68,68,0.12)' : (p.stock ?? 0) <= 5 ? 'rgba(234,179,8,0.12)' : 'rgba(34,197,94,0.12)',
                      color: (p.stock ?? 0) <= 0 ? R : (p.stock ?? 0) <= 5 ? Y : G,
                      alignSelf: 'flex-start',
                    }}>
                      {p.stock ?? '∞'} {(p.stock ?? 0) === 1 ? 'unit' : 'units'}
                    </span>
                    {p.stock_status && <span style={{ fontSize: 10, color: TEXT_FAINT }}>{p.stock_status}</span>}
                  </div>
                </td>
                <td style={{ padding: '12px 16px', fontSize: 11, color: TEXT_FAINT, whiteSpace: 'nowrap' }}>
                  {p.updated_at ? formatTimeAgo(p.updated_at) : '—'}
                </td>
                <td style={{ padding: '12px 16px', display: 'flex', gap: 6 }}>
                  <button onClick={() => setModal({ type: 'edit', product: p })} style={{ padding: '4px 10px', borderRadius: 6, border: '1px solid rgba(59,130,246,0.25)', background: 'rgba(59,130,246,0.12)', color: B, fontSize: 11, fontWeight: 700, cursor: 'pointer' }}>EDIT</button>
                  <button onClick={() => handleDelete(p.id, p.name)} style={{ padding: '4px 10px', borderRadius: 6, border: '1px solid rgba(239,68,68,0.2)', background: 'rgba(239,68,68,0.1)', color: R, fontSize: 11, fontWeight: 700, cursor: 'pointer' }}>DEL</button>
                </td>
              </tr>
            )})}
          </TableBox>
        </div>
      )}
    </div>
  )
}

function BadgePill({ color, label }) {
  return (
    <span style={{ display: 'inline-block', padding: '1px 7px', borderRadius: 4, fontSize: 10, fontWeight: 700, background: `${color}15`, color, border: `1px solid ${color}44`, lineHeight: '14px' }}>
      {label}
    </span>
  )
}

// ═════════════════════════════════════════════════════════════════════════════
// USERS TAB
// ═════════════════════════════════════════════════════════════════════════════
function UsersTab() {
  const { data: users, loading, error, refetch } = useFetch('/api/admin/users')
  const roles = ['all', 'customer']
  const [roleFilter, setRoleFilter] = useState('customer')
  const [search, setSearch] = useState('')

  const filtered = !users ? [] : users.filter(u => {
    const matchSearch = !search || u.name?.toLowerCase().includes(search.toLowerCase()) || u.email?.toLowerCase().includes(search.toLowerCase())
    const matchRole = roleFilter === 'all' || u.role === roleFilter
    return matchSearch && matchRole
  })

  const roleCounts = !users ? {} : users.reduce((acc, u) => { acc[u.role] = (acc[u.role] || 0) + 1; return acc }, {})
  const totalUsers = users?.length ?? 0

  return (
    <div>
      {/* Role summary chips */}
      <div style={{ display: 'flex', gap: 10, marginBottom: 14, flexWrap: 'wrap' }}>
        <RoleChip label="all" count={users?.length} active={roleFilter === 'all'} onClick={() => setRoleFilter('all')} color={O} />
        <RoleChip label="customer" count={roleCounts.customer} active={roleFilter === 'customer'} onClick={() => setRoleFilter('customer')} color="#9ca3af" />
      </div>

      {/* Search + refresh */}
      <div style={{ display: 'flex', gap: 10, marginBottom: 16 }}>
        <input value={search} onChange={e => setSearch(e.target.value)} placeholder="Search by name or email..." style={{ flex: 1, maxWidth: 320, padding: '8px 12px', background: SURFACE_2, border: `1px solid ${BORDER_INPUT}`, borderRadius: 8, color: '#fff', fontSize: 13, outline: 'none', fontFamily: "'Rajdhani', sans-serif", fontWeight: 500 }} />
        <button onClick={refetch} style={{ padding: '8px 12px', borderRadius: 8, border: `1px solid ${BORDER_INPUT}`, background: 'transparent', color: TEXT_FAINT, fontSize: 13, cursor: 'pointer' }}>↻</button>
      </div>

      {error ? <ErrorState message={error} onRetry={refetch} /> : (
        <div style={{ overflowX: 'auto' }}>
          <TableBox headers={['ID','NAME','EMAIL','ROLE','STATUS','JOINED','ACTIONS']}>
            {loading ? <SkeletonRows cols={7} rows={5} /> : filtered.length === 0 ? (
              <tr><td colSpan={7}><EmptyState label="No users match" /></td></tr>
            ) : filtered.map((u, i) => (
              <tr key={u.id} style={{ borderBottom: `1px solid ${BORDER}`, transition, background: i % 2 ? 'rgba(255,255,255,0.015)' : 'transparent' }}
                onMouseEnter={e => e.currentTarget.style.background = 'rgba(255,255,255,0.04)'}
                onMouseLeave={e => e.currentTarget.style.background = i % 2 ? 'rgba(255,255,255,0.015)' : 'transparent'}
              >
                <td style={{ padding: '12px 16px' }}><span style={{ fontSize: 11, color: TEXT_FAINT, fontFamily: 'monospace', background: SURFACE_2, padding: '2px 8px', borderRadius: 4 }}>#{u.id}</span></td>
                <td style={{ padding: '12px 16px', fontSize: 14, color: 'var(--t-text)', fontWeight: 600 }}>{u.name ?? u.username}</td>
                <td style={{ padding: '12px 16px', fontSize: 12, color: TEXT_MUTED, fontFamily: 'monospace' }}>{u.email}</td>
                <td style={{ padding: '12px 16px' }}><Badge status={u.role} map={ROLE_COLORS} /></td>
                <td style={{ padding: '12px 16px' }}>
                  <span style={{ display: 'inline-block', padding: '2px 8px', borderRadius: 20, fontSize: 11, fontWeight: 600, background: u.email_verified_at ? 'rgba(34,197,94,0.12)' : 'rgba(239,68,68,0.12)', color: u.email_verified_at ? G : R, border: `1px solid ${u.email_verified_at ? 'rgba(34,197,94,0.3)' : 'rgba(239,68,68,0.3)'}` }}>
                    {u.email_verified_at ? 'ACTIVE' : 'UNVERIFIED'}
                  </span>
                </td>
                <td style={{ padding: '12px 16px', fontSize: 12, color: TEXT_FAINT }}>{u.created_at?.slice(0, 10) ?? '—'}</td>
                <td style={{ padding: '12px 16px' }}>
                  <button style={{ padding: '4px 10px', background: 'rgba(249,115,22,0.12)', border: '1px solid rgba(249,115,22,0.25)', borderRadius: 6, color: O, fontSize: 11, fontWeight: 700, cursor: 'pointer' }}>VIEW</button>
                </td>
              </tr>
            ))}
          </TableBox>
        </div>
      )}
    </div>
  )
}

function RoleChip({ label, count, active, onClick, color }) {
  return (
    <button onClick={onClick} style={{
      padding: '4px 12px', borderRadius: 8, border: '1px solid', fontSize: 11, fontWeight: 700, cursor: 'pointer',
      borderColor: active ? color : BORDER_INPUT,
      background: active ? `${color}18` : 'transparent',
      color: active ? color : TEXT_MUTED,
      display: 'flex', alignItems: 'center', gap: 6, transition,
    }}>
      {label === 'all' ? 'ALL' : label.toUpperCase()}
      {count !== undefined && <span style={{ fontSize: 10, opacity: 0.7 }}>({count})</span>}
    </button>
  )
}

// ═════════════════════════════════════════════════════════════════════════════
// DELIVERY TAB
// ═════════════════════════════════════════════════════════════════════════════
function DeliveryTab() {
  const { data: deliveries, loading, error, refetch } = useFetch('/api/delivery-schedules')

  const counts = (deliveries ?? []).reduce((acc, d) => {
    acc[d.status] = (acc[d.status] || 0) + 1; return acc
  }, {})

  return (
    <div>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(180px,1fr))', gap: 14, marginBottom: 22 }}>
        {[['Scheduled','scheduled',O],['En Route','en_route',B],['Delivered','delivered',G]].map(([label, key, color]) => (
          <div key={key} style={{
            background: SURFACE, border: `1px solid ${BORDER}`, borderRadius: CARD_RADIUS,
            padding: '18px 20px', position: 'relative', overflow: 'hidden', transition,
          }}
            onMouseEnter={e => { e.currentTarget.style.borderColor = color; e.currentTarget.style.transform = 'translateY(-2px)' }}
            onMouseLeave={e => { e.currentTarget.style.borderColor = ''; e.currentTarget.style.transform = '' }}
          >
            <div style={{ position: 'absolute', top: 0, left: 0, width: 3, height: '100%', background: color, borderRadius: '3px 0 0 3px', opacity: 0.5 }} />
            <div style={{ fontSize: 28, fontWeight: 800, color, fontFamily: "'Rajdhani', sans-serif" }}>
              {loading ? '—' : (counts[key] ?? 0)}
            </div>
            <div style={{ fontSize: 13, color: TEXT_MUTED, marginTop: 4, fontWeight: 600, letterSpacing: '0.5px' }}>{label}</div>
          </div>
        ))}
      </div>

      {error ? <ErrorState message={error} onRetry={refetch} /> : (
        <div style={{ overflowX: 'auto' }}>
          <TableBox headers={['ORDER','CUSTOMER','AREA','DRIVER','TIME','STATUS']}>
            {loading ? <SkeletonRows cols={6} rows={3} /> : !deliveries?.length ? (
              <tr><td colSpan={6}><EmptyState label="No deliveries scheduled today" /></td></tr>
            ) : deliveries.map((d, i) => (
              <tr key={d.id} style={{ borderBottom: `1px solid ${BORDER}`, background: i % 2 ? 'rgba(255,255,255,0.015)' : 'transparent', transition }}
                onMouseEnter={e => e.currentTarget.style.background = 'rgba(255,255,255,0.04)'}
                onMouseLeave={e => e.currentTarget.style.background = i % 2 ? 'rgba(255,255,255,0.015)' : 'transparent'}
              >
                <td style={{ padding: '12px 16px', fontSize: 13, color: O, fontWeight: 700, fontFamily: 'monospace' }}>#{d.order_id ?? d.id}</td>
                <td style={{ padding: '12px 16px', fontSize: 14, color: 'var(--t-text)' }}>{d.order?.user?.name ?? d.customer_name ?? '—'}</td>
                <td style={{ padding: '12px 16px', fontSize: 14, color: TEXT_MUTED }}>{d.area ?? d.address ?? '—'}</td>
                <td style={{ padding: '12px 16px', fontSize: 14, color: 'var(--t-text)' }}>{d.driver_name ?? '—'}</td>
                <td style={{ padding: '12px 16px', fontSize: 14, color: TEXT_MUTED }}>{d.scheduled_time ?? d.time ?? '—'}</td>
                <td style={{ padding: '12px 16px' }}><Badge status={d.status} map={STATUS_COLORS} /></td>
              </tr>
            ))}
          </TableBox>
        </div>
      )}
    </div>
  )
}

// ═════════════════════════════════════════════════════════════════════════════
// REPORT TAB
// ═════════════════════════════════════════════════════════════════════════════
function ReportTab() {
  const [month, setMonth] = useState(() => new Date().toISOString().slice(0, 7)) // "YYYY-MM"
  const { data: allOrders } = useFetch('/api/orders?per_page=500')
  const { data: users } = useFetch('/api/admin/users')
  const { data: products } = useFetch('/api/products?per_page=500')
  const { data: stats } = useFetch('/api/admin/stats')
  const statusChartRef = useRef(null)
  const revenueChartRef = useRef(null)
  const [chartsReady, setChartsReady] = useState(false)

  // Load Chart.js once
  useEffect(() => {
    if (!window.Chart) {
      const s = document.createElement('script')
      s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js'
      s.onload = () => setChartsReady(true)
      document.head.appendChild(s)
    } else setChartsReady(true)
  }, [])

  // Filter orders by selected month
  const filteredOrders = !allOrders ? [] : allOrders.filter(o => {
    return o.created_at && o.created_at.startsWith(month)
  })

  const prevMonth = month ? (() => {
    const d = new Date(month + '-01')
    d.setMonth(d.getMonth() - 1)
    return d.toISOString().slice(0, 7)
  })() : null

  const prevOrders = !allOrders ? [] : allOrders.filter(o => {
    return prevMonth && o.created_at && o.created_at.startsWith(prevMonth)
  })

  // Compute KPIs for current vs previous month
  const currentRevenue = filteredOrders.filter(o => !['cancelled'].includes(o.status)).reduce((s, o) => s + Number(o.total ?? 0), 0)
  const prevRevenue = prevOrders.filter(o => !['cancelled'].includes(o.status)).reduce((s, o) => s + Number(o.total ?? 0), 0)
  const currentOrdersCount = filteredOrders.length
  const prevOrdersCount = prevOrders.length
  const currentCustomers = new Set(filteredOrders.filter(o => o.user_id).map(o => o.user_id)).size

  const calcDelta = (cur, prev) => {
    if (prev === 0) return cur > 0 ? '+100%' : null
    const pct = Math.round(((cur - prev) / prev) * 100)
    return (pct >= 0 ? '+' : '') + pct + '%'
  }

  // Order status breakdown for donut
  const statusCounts = Object.entries(
    filteredOrders.reduce((acc, o) => { acc[o.status] = (acc[o.status] || 0) + 1; return acc }, {})
  ).sort((a, b) => b[1] - a[1])

  // Top products
  const topProducts = !allOrders ? [] : Object.entries(
    allOrders.reduce((acc, o) => {
      o.items?.forEach(item => {
        acc[item.name] = (acc[item.name] || 0) + item.qty
      }); return acc
    }, {})
  ).sort((a, b) => b[1] - a[1]).slice(0, 10)

  // Weekly revenue for the selected month
  const weeklyRevenue = filteredOrders.length ? (() => {
    const weeks = {}
    filteredOrders.forEach(o => {
      if (!o.created_at) return
      const d = new Date(o.created_at)
      const weekStart = new Date(d)
      weekStart.setDate(d.getDate() - d.getDay())
      const key = weekStart.toISOString().slice(0, 10)
      weeks[key] = (weeks[key] || 0) + Number(o.total ?? 0)
    })
    return Object.entries(weeks).sort((a, b) => a[0].localeCompare(b[0])).map(([w, rev]) => ({
      week: w.slice(5), // "MM-DD"
      revenue: Math.round(rev * 100) / 100,
    }))
  })() : []

  // Status donut chart
  useEffect(() => {
    if (!chartsReady || !statusCounts.length) return
    const canvas = document.getElementById('statusDonut')
    if (!canvas) return
    if (statusChartRef.current) statusChartRef.current.destroy()

    const ctx = canvas.getContext('2d')
    const isLight = document.documentElement.getAttribute('data-theme') === 'light'

    const colorMap = {
      pending: '#EAB308', confirmed: '#22C55E', processing: '#3B82F6',
      shipped: '#F97316', delivered: '#22C55E', cancelled: '#EF4444',
    }

    statusChartRef.current = new window.Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: statusCounts.map(([s]) => s.toUpperCase()),
        datasets: [{
          data: statusCounts.map(([, c]) => c),
          backgroundColor: statusCounts.map(([s]) => `${colorMap[s] || '#6B7280'}88`),
          borderColor: statusCounts.map(([s]) => colorMap[s] || '#6B7280'),
          borderWidth: 2,
        }],
      },
      options: {
        responsive: true, maintainAspectRatio: false, cutout: '60%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color: isLight ? 'rgba(15,23,42,0.7)' : 'rgba(255,255,255,0.7)', padding: 12, font: { size: 11 } },
          },
          tooltip: {
            backgroundColor: isLight ? '#fff' : '#1A1A1A',
            borderColor: 'rgba(249,115,22,0.4)', borderWidth: 1,
            bodyColor: isLight ? 'rgba(15,23,42,0.75)' : 'rgba(255,255,255,0.8)',
            padding: 10,
            callbacks: { label: (c) => ` ${c.label}: ${c.parsed} orders` },
          },
        },
      },
    })
    return () => { if (statusChartRef.current) statusChartRef.current.destroy() }
  }, [statusCounts, chartsReady])

  // Revenue trend chart
  useEffect(() => {
    if (!chartsReady || !weeklyRevenue.length) return
    const canvas = document.getElementById('revenueChartReport')
    if (!canvas) return
    if (revenueChartRef.current) revenueChartRef.current.destroy()

    const ctx = canvas.getContext('2d')
    const isLight = document.documentElement.getAttribute('data-theme') === 'light'
    const gridColor = isLight ? 'rgba(15,23,42,0.06)' : 'rgba(255,255,255,0.06)'
    const textColor = isLight ? 'rgba(15,23,42,0.45)' : 'rgba(255,255,255,0.35)'

    revenueChartRef.current = new window.Chart(ctx, {
      type: 'line',
      data: {
        labels: weeklyRevenue.map(w => w.week),
        datasets: [{
          label: 'Revenue',
          data: weeklyRevenue.map(w => w.revenue),
          borderColor: '#22C55E',
          backgroundColor: isLight ? 'rgba(34,197,94,0.1)' : 'rgba(34,197,94,0.15)',
          fill: true, tension: 0.4,
          borderWidth: 2,
          pointBackgroundColor: '#22C55E',
          pointRadius: 4, pointHoverRadius: 7,
        }],
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: isLight ? '#fff' : '#1A1A1A',
            borderColor: 'rgba(34,197,94,0.4)', borderWidth: 1,
            titleColor: '#22C55E',
            bodyColor: isLight ? 'rgba(15,23,42,0.75)' : 'rgba(255,255,255,0.8)',
            padding: 10,
            callbacks: { label: (c) => ` $${c.parsed.y.toLocaleString()}` },
          },
        },
        scales: {
          x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } },
          y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 }, callback: v => '$' + v }, beginAtZero: true },
        },
      },
    })
    return () => { if (revenueChartRef.current) revenueChartRef.current.destroy() }
  }, [weeklyRevenue, chartsReady])

  const exportCSV = () => {
    if (!allOrders?.length && !users?.length && !products?.length) return
    let csv = 'TRONMATIX REPORT — ' + month + '\n\n'
    csv += 'Generated,' + new Date().toISOString().slice(0, 19) + '\n\n'
    csv += 'SUMMARY\n'
    csv += `Orders (${month}),${currentOrdersCount}\n`
    csv += `Revenue (${month}),$${currentRevenue.toLocaleString()}\n`
    csv += `Revenue Delta,${calcDelta(currentRevenue, prevRevenue) || '—'}\n`
    csv += `Unique Customers,${currentCustomers}\n`
    csv += `Total Products,${products?.length ?? 0}\n\n`
    if (filteredOrders.length) {
      csv += 'ORDERS (' + month + ')\n'
      csv += 'Order ID,Customer,Status,Total,Date\n'
      filteredOrders.forEach(o => {
        csv += `${o.order_id || o.id},"${o.user?.name ?? o.user?.username ?? 'Guest'}",${o.status},$${o.total},${o.created_at?.slice(0, 10)}\n`
      })
      csv += '\n'
    }
    if (users?.length) {
      csv += 'USERS\n'
      csv += 'ID,Name,Email,Role,Joined\n'
      users.forEach(u => csv += `${u.id},"${u.name ?? u.username}","${u.email}",${u.role},${u.created_at?.slice(0, 10)}\n`)
      csv += '\n'
    }
    if (products?.length) {
      csv += 'PRODUCTS\n'
      csv += 'ID,Name,Category,Price,Stock\n'
      products.forEach(p => csv += `${p.id},"${p.name}","${p.category ?? ''}",$${p.price},${p.stock ?? 0}\n`)
    }
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url; a.download = `tronmatix-report-${month}.csv`
    a.click(); URL.revokeObjectURL(url)
  }

  return (
    <div>
      {/* Header with month selector + export */}
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20, flexWrap: 'wrap', gap: 12 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
          <div>
            <div style={{ fontSize: 20, fontWeight: 700, color: '#fff' }}>📊 Reports</div>
            <div style={{ fontSize: 12, color: TEXT_FAINT, marginTop: 2 }}>Business overview & period comparison</div>
          </div>
          <div style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
            <button onClick={() => {
              const d = new Date(month + '-01'); d.setMonth(d.getMonth() - 1)
              setMonth(d.toISOString().slice(0, 7))
            }} style={{ padding: '6px 10px', borderRadius: 6, border: `1px solid ${BORDER_INPUT}`, background: SURFACE_2, color: TEXT_MUTED, cursor: 'pointer', fontSize: 14 }}>◀</button>
            <input type="month" value={month} onChange={e => setMonth(e.target.value)}
              style={{ padding: '6px 10px', borderRadius: 6, border: `1px solid ${BORDER_INPUT}`, background: SURFACE_2, color: '#fff', fontSize: 13, outline: 'none', fontFamily: "'Rajdhani', sans-serif", fontWeight: 500 }} />
            <button onClick={() => {
              const d = new Date(month + '-01'); d.setMonth(d.getMonth() + 1)
              if (d <= new Date()) setMonth(d.toISOString().slice(0, 7))
            }} style={{ padding: '6px 10px', borderRadius: 6, border: `1px solid ${BORDER_INPUT}`, background: SURFACE_2, color: TEXT_MUTED, cursor: 'pointer', fontSize: 14 }}>▶</button>
          </div>
        </div>
        <button onClick={exportCSV} style={{
          padding: '10px 22px', borderRadius: 8, border: 'none',
          background: O, color: '#fff', fontSize: 13, fontWeight: 700, cursor: 'pointer', letterSpacing: '1px',
          display: 'flex', alignItems: 'center', gap: 8,
        }}>⬇ EXPORT CSV</button>
      </div>

      {/* KPI Cards with trends */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(210px, 1fr))', gap: 14, marginBottom: 24 }}>
        {[
          { label: 'Orders', value: currentOrdersCount.toLocaleString(), delta: calcDelta(currentOrdersCount, prevOrdersCount), color: O, icon: '📦' },
          { label: 'Revenue', value: '$' + currentRevenue.toLocaleString(), delta: calcDelta(currentRevenue, prevRevenue), color: G, icon: '💰' },
          { label: 'Active Customers', value: stats?.active_users ?? '—', delta: null, color: B, icon: '👥' },
          { label: 'Total Products', value: products?.length?.toLocaleString() ?? '—', delta: null, color: P, icon: '🖥️' },
        ].map(s => (
          <div key={s.label} style={{
            background: SURFACE, border: `1px solid ${BORDER}`, borderRadius: CARD_RADIUS,
            padding: '18px 20px', display: 'flex', alignItems: 'center', gap: 14, position: 'relative', overflow: 'hidden',
          }}>
            <div style={{ position: 'absolute', top: 0, left: 0, width: 3, height: '100%', background: s.color, borderRadius: '3px 0 0 3px', opacity: 0.5 }} />
            <div style={{ width: 42, height: 42, background: `${s.color}18`, border: `1px solid ${s.color}33`, borderRadius: 10, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 18, flexShrink: 0 }}>{s.icon}</div>
            <div style={{ flex: 1 }}>
              <div style={{ fontSize: 24, fontWeight: 800, color: s.color }}>{s.value}</div>
              <div style={{ fontSize: 12, color: TEXT_MUTED, marginTop: 1 }}>{s.label}</div>
              {s.delta && (
                <span style={{
                  display: 'inline-flex', alignItems: 'center', gap: 3, marginTop: 3,
                  padding: '1px 7px', borderRadius: 4, fontSize: 10, fontWeight: 700,
                  background: s.delta.startsWith('+') ? 'rgba(34,197,94,0.12)' : 'rgba(239,68,68,0.12)',
                  color: s.delta.startsWith('+') ? G : R,
                }}>{s.delta} vs last month</span>
              )}
            </div>
          </div>
        ))}
      </div>

      {/* Charts row: Revenue trend + Status donut */}
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16, marginBottom: 20 }}>
        {/* Revenue trend */}
        <div style={{ background: SURFACE, border: `1px solid ${BORDER}`, borderRadius: CARD_RADIUS, padding: '18px 20px' }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 14 }}>
            <span style={{ fontSize: 14, fontWeight: 700, color: '#fff' }}>💰 Revenue Trend</span>
            <span style={{ fontSize: 11, color: TEXT_FAINT }}>Per week — {month}</span>
          </div>
          {filteredOrders.length === 0 ? (
            <EmptyState label="No revenue data for this period" />
          ) : weeklyRevenue.length === 0 ? (
            <div style={{ height: 200, display: 'flex', alignItems: 'center', justifyContent: 'center', color: TEXT_FAINT, fontSize: 13 }}>Insufficient data for chart</div>
          ) : (
            <div style={{ width: '100%', height: 220 }}>
              <canvas id="revenueChartReport" style={{ width: '100%', height: '100%' }} />
            </div>
          )}
        </div>

        {/* Status donut */}
        <div style={{ background: SURFACE, border: `1px solid ${BORDER}`, borderRadius: CARD_RADIUS, padding: '18px 20px' }}>
          <div style={{ fontSize: 14, fontWeight: 700, color: '#fff', marginBottom: 14 }}>📋 Order Status — {month}</div>
          {filteredOrders.length === 0 ? (
            <EmptyState label="No orders for this period" />
          ) : (
            <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
              <div style={{ width: 180, height: 180, flexShrink: 0 }}>
                <canvas id="statusDonut" style={{ width: '100%', height: '100%' }} />
              </div>
              <div style={{ flex: 1, minWidth: 0 }}>
                {statusCounts.map(([status, count]) => {
                  const sc = STATUS_COLORS[status] || { color: TEXT_MUTED }
                  const total = filteredOrders.length
                  const pct = Math.round(count / total * 100)
                  return (
                    <div key={status} style={{ display: 'flex', alignItems: 'center', gap: 8, padding: '4px 0' }}>
                      <span style={{ width: 8, height: 8, borderRadius: '50%', background: sc.color, flexShrink: 0 }} />
                      <span style={{ flex: 1, fontSize: 12, color: TEXT_MUTED }}>{status.toUpperCase()}</span>
                      <span style={{ fontSize: 12, color: '#fff', fontWeight: 600 }}>{count}</span>
                      <span style={{ fontSize: 11, color: TEXT_FAINT }}>({pct}%)</span>
                    </div>
                  )
                })}
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Bottom row: Top products + Export info */}
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
        <div style={{ background: SURFACE, border: `1px solid ${BORDER}`, borderRadius: CARD_RADIUS, padding: '18px 20px' }}>
          <div style={{ fontSize: 14, fontWeight: 700, color: '#fff', marginBottom: 12 }}>🏆 Top Selling Products</div>
          {topProducts.length === 0 ? (
            <EmptyState label="No sales data" />
          ) : (
            topProducts.map(([name, qty], i) => (
              <div key={name} style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '6px 0', borderBottom: i < topProducts.length - 1 ? `1px solid ${BORDER}` : 'none' }}>
                <span style={{ fontSize: 11, color: TEXT_FAINT, fontWeight: 700, width: 20 }}>#{i + 1}</span>
                <span style={{ flex: 1, fontSize: 13, color: 'rgba(255,255,255,0.8)', fontWeight: 600 }}>{name}</span>
                <span style={{ fontSize: 13, color: O, fontWeight: 700 }}>{qty} sold</span>
              </div>
            ))
          )}
        </div>
        <div style={{
          background: 'rgba(249,115,22,0.05)', border: '1px solid rgba(249,115,22,0.15)',
          borderRadius: CARD_RADIUS, padding: '18px 20px', display: 'flex', flexDirection: 'column', justifyContent: 'center',
        }}>
          <div style={{ fontSize: 14, fontWeight: 700, color: '#fff', marginBottom: 8 }}>📄 Data Export</div>
          <div style={{ fontSize: 12, color: TEXT_MUTED, lineHeight: 1.6 }}>
            CSV export includes: Summary (with period comparison), Orders for selected month, All Users, and All Products.
            Click <strong style={{ color: O }}>"EXPORT CSV"</strong> to download.
          </div>
        </div>
      </div>
    </div>
  )
}

// ═════════════════════════════════════════════════════════════════════════════
// ACTIVITY LOG TAB
// ═════════════════════════════════════════════════════════════════════════════
const ACTION_LABELS = {
  order_status_update: 'Status Update',
  payment_verified:    'Payment Verified',
  delivery_confirmed:  'Delivery Confirmed',
  order_cancelled:     'Order Cancelled',
  product_create:      'Product Created',
  product_update:      'Product Updated',
  product_delete:      'Product Deleted',
  staff_invited:       'Staff Invited',
  staff_role_changed:  'Role Changed',
  staff_activated:     'Activated',
  staff_deactivated:   'Deactivated',
  staff_deleted:       'Staff Removed',
  login_success:       'Login Success',
  login_failed:        'Login Failed',
  login_rate_limited:  'Rate Limited ❗',
}

const ACTION_COLORS = {
  login_failed:       { bg: 'rgba(239,68,68,0.12)', color: R },
  login_rate_limited: { bg: 'rgba(239,68,68,0.25)', color: R },
  order_status_update: { bg: 'rgba(59,130,246,0.12)', color: B },
  payment_verified:   { bg: 'rgba(34,197,94,0.12)', color: G },
  delivery_confirmed: { bg: 'rgba(249,115,22,0.12)', color: O },
  product_create:     { bg: 'rgba(34,197,94,0.12)', color: G },
  product_update:     { bg: 'rgba(168,85,247,0.12)', color: P },
  staff_invited:      { bg: 'rgba(59,130,246,0.12)', color: B },
  login_success:      { bg: 'rgba(34,197,94,0.12)', color: G },
}

function ActivityTab() {
  const [logs, setLogs] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [actionFilter, setActionFilter] = useState('')
  const [dateFrom, setDateFrom] = useState('')
  const [dateTo, setDateTo] = useState('')
  const [page, setPage] = useState(1)
  const [totalPages, setTotalPages] = useState(1)

  const fetchLogs = useCallback(async (p = 1) => {
    setLoading(true); setError(null)
    try {
      const params = new URLSearchParams({ per_page: '30', page: String(p) })
      if (actionFilter) params.set('action', actionFilter)
      if (dateFrom) params.set('date_from', dateFrom)
      if (dateTo) params.set('date_to', dateTo)
      const res = await api.get(`/api/activity-logs?${params}`)
      const raw = res.data?.data ?? []
      setLogs(raw)
      const meta = res.data?.meta
      if (meta) setTotalPages(meta.last_page ?? 1)
    } catch (err) {
      setError(err.response?.data?.message || err.message || 'Failed to load activity logs.')
    } finally { setLoading(false) }
  }, [actionFilter, dateFrom, dateTo])

  useEffect(() => { fetchLogs(page) }, [fetchLogs, page])

  return (
    <div>
      {/* Filters */}
      <div style={{ display: 'flex', gap: 12, marginBottom: 18, flexWrap: 'wrap', alignItems: 'end' }}>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
          <label style={{ fontSize: 11, color: TEXT_MUTED, fontWeight: 700, letterSpacing: 1 }}>ACTION</label>
          <select value={actionFilter} onChange={e => { setActionFilter(e.target.value); setPage(1) }}
            style={{ padding: '8px 12px', background: SURFACE, border: `1px solid ${BORDER_INPUT}`, borderRadius: 8, color: TEXT_PRIMARY, fontSize: 13, outline: 'none' }}>
            <option value="">All Actions</option>
            {Object.entries(ACTION_LABELS).map(([k, v]) => <option key={k} value={k}>{v}</option>)}
          </select>
        </div>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
          <label style={{ fontSize: 11, color: TEXT_MUTED, fontWeight: 700, letterSpacing: 1 }}>FROM</label>
          <input type="date" value={dateFrom} onChange={e => { setDateFrom(e.target.value); setPage(1) }}
            style={{ padding: '7px 12px', background: SURFACE, border: `1px solid ${BORDER_INPUT}`, borderRadius: 8, color: TEXT_PRIMARY, fontSize: 13, outline: 'none' }} />
        </div>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
          <label style={{ fontSize: 11, color: TEXT_MUTED, fontWeight: 700, letterSpacing: 1 }}>TO</label>
          <input type="date" value={dateTo} onChange={e => { setDateTo(e.target.value); setPage(1) }}
            style={{ padding: '7px 12px', background: SURFACE, border: `1px solid ${BORDER_INPUT}`, borderRadius: 8, color: TEXT_PRIMARY, fontSize: 13, outline: 'none' }} />
        </div>
        {(actionFilter || dateFrom || dateTo) && (
          <button onClick={() => { setActionFilter(''); setDateFrom(''); setDateTo(''); setPage(1) }}
            style={{ padding: '7px 16px', background: 'rgba(239,68,68,0.1)', border: '1px solid rgba(239,68,68,0.2)', borderRadius: 8, color: R, fontSize: 12, fontWeight: 700, cursor: 'pointer', letterSpacing: 1 }}>
            ✕ CLEAR
          </button>
        )}
        <button onClick={() => fetchLogs(page)}
          style={{ padding: '7px 16px', background: `${O}1a`, border: `1px solid ${O}44`, borderRadius: 8, color: O, fontSize: 12, fontWeight: 700, cursor: 'pointer', letterSpacing: 1 }}>
          ⟳ REFRESH
        </button>
      </div>

      {loading ? <Spinner /> : error ? <ErrorState message={error} onRetry={() => fetchLogs(page)} /> : !logs?.length ? <EmptyState label="No activity found" /> : (
        <>
          <TableBox headers={['TIME', 'ACTOR', 'ACTION', 'ENTITY', 'DETAILS']}>
            {logs.map((log, i) => {
              const ac = ACTION_COLORS[log.action] || { bg: 'rgba(75,85,99,0.15)', color: TEXT_MUTED }
              const details = log.details ? (typeof log.details === 'object' ? Object.entries(log.details).map(([k, v]) => `${k}: ${v}`).join(' | ') : String(log.details)) : ''
              return (
                <tr key={log.id ?? i} style={{ borderBottom: i < logs.length - 1 ? `1px solid ${BORDER}` : 'none' }}>
                  <td style={{ padding: '12px 16px', whiteSpace: 'nowrap', fontSize: 12, color: TEXT_FAINT }}>
                    {new Date(log.created_at).toLocaleString()}
                  </td>
                  <td style={{ padding: '12px 16px', fontSize: 13, color: TEXT_PRIMARY, fontWeight: 600, whiteSpace: 'nowrap' }}>
                    <span style={{ color: log.actor_type === 'Admin' ? O : B, fontSize: 11, fontWeight: 700, letterSpacing: '0.5px' }}>
                      [{log.actor_type}]
                    </span>{' '}
                    {log.actor_name}
                  </td>
                  <td style={{ padding: '12px 16px' }}>
                    <span style={{
                      display: 'inline-block', padding: '2px 8px', borderRadius: 12,
                      fontSize: 11, fontWeight: 600, whiteSpace: 'nowrap',
                      background: ac.bg, color: ac.color, border: `1px solid ${ac.color}33`,
                    }}>
                      {ACTION_LABELS[log.action] || log.action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                    </span>
                  </td>
                  <td style={{ padding: '12px 16px', fontSize: 13, color: TEXT_FAINT, whiteSpace: 'nowrap' }}>
                    {log.entity_type && <><span style={{ fontWeight: 600 }}>{log.entity_type}</span>{log.entity_name && <>: {log.entity_name}</>}</>}
                  </td>
                  <td style={{ padding: '12px 16px', fontSize: 12, color: TEXT_FAINT, maxWidth: 300, overflow: 'hidden', textOverflow: 'ellipsis' }}>
                    {details}
                  </td>
                </tr>
              )
            })}
          </TableBox>

          {/* Pagination */}
          {totalPages > 1 && (
            <div style={{ display: 'flex', justifyContent: 'center', gap: 8, marginTop: 18 }}>
              <button disabled={page <= 1} onClick={() => setPage(p => p - 1)}
                style={{ padding: '6px 14px', background: SURFACE, border: `1px solid ${BORDER}`, borderRadius: 8, color: page <= 1 ? TEXT_XFAINT : TEXT_PRIMARY, fontSize: 13, fontWeight: 600, cursor: page <= 1 ? 'default' : 'pointer', opacity: page <= 1 ? 0.5 : 1 }}>
                ← PREV
              </button>
              <span style={{ padding: '6px 14px', color: TEXT_MUTED, fontSize: 13 }}>
                {page} / {totalPages}
              </span>
              <button disabled={page >= totalPages} onClick={() => setPage(p => p + 1)}
                style={{ padding: '6px 14px', background: SURFACE, border: `1px solid ${BORDER}`, borderRadius: 8, color: page >= totalPages ? TEXT_XFAINT : TEXT_PRIMARY, fontSize: 13, fontWeight: 600, cursor: page >= totalPages ? 'default' : 'pointer', opacity: page >= totalPages ? 0.5 : 1 }}>
                NEXT →
              </button>
            </div>
          )}
        </>
      )}
    </div>
  )
}

// ═════════════════════════════════════════════════════════════════════════════
// MAIN LAYOUT
// ═════════════════════════════════════════════════════════════════════════════
export default function StaffDashboard() {
  const [tab, setTab] = useState('overview')
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [theme, setTheme] = useState(() => localStorage.getItem('tronmatix_theme') || 'dark')
  const { user, logout, startHeartbeat } = useAuth()
  const navigate = useNavigate()

  const toggleTheme = () => {
    const next = theme === 'dark' ? 'light' : 'dark'
    setTheme(next)
    localStorage.setItem('tronmatix_theme', next)
    document.documentElement.setAttribute('data-theme', next)
  }

  // Sync theme on mount
  useEffect(() => {
    document.documentElement.setAttribute('data-theme', theme)
  }, [theme])

  // Start heartbeat when dashboard mounts -- keeps user online while active
  useEffect(() => {
    startHeartbeat()
  }, [startHeartbeat])

  const handleLogout = async () => { await logout(); navigate('/staff/login', { replace: true }) }

  const TAB_CONTENT = {
    overview: <OverviewTab setTab={setTab} />,
    orders:   <OrdersTab />,
    products: <ProductsTab />,
    users:    <UsersTab />,
    delivery: <DeliveryTab />,
    report:   <ReportTab />,
    activity: <ActivityTab />,
  }

  // Hide the Activity tab (and its nav entry) for roles the API denies.
  const isAdmin = ADMIN_ROLES.includes(user?.role)
  const navSections = isAdmin
    ? NAV_SECTIONS
    : NAV_SECTIONS
        .map(sec => ({ ...sec, items: sec.items.filter(n => n.id !== 'activity') }))
        .filter(sec => sec.items.length > 0)

  const flatNav = navSections.flatMap(s => s.items)

  return (
    <div className={theme === 'light' ? 'staff-light' : ''} style={{ minHeight: '100vh', background: BG, display: 'flex', fontFamily: "'Rajdhani', sans-serif" }}>
      <style>{`
        :root {
          --t-bg: #0A0A0A;
          --t-surface: #111111;
          --t-surface2: #1A1A1A;
          --t-border: rgba(255,255,255,0.07);
          --t-border-input: rgba(255,255,255,0.1);
          --t-text: #FFFFFF;
          --t-text-muted: rgba(255,255,255,0.55);
          --t-text-faint: rgba(255,255,255,0.35);
          --t-text-xfaint: rgba(255,255,255,0.2);
        }
        .staff-light {
          --t-bg: #F1F5F9 !important;
          --t-surface: #FFFFFF !important;
          --t-surface2: #F8FAFC !important;
          --t-border: rgba(15,23,42,0.08) !important;
          --t-border-input: rgba(15,23,42,0.15) !important;
          --t-text: #0F172A !important;
          --t-text-muted: rgba(15,23,42,0.60) !important;
          --t-text-faint: rgba(15,23,42,0.40) !important;
          --t-text-xfaint: rgba(15,23,42,0.25) !important;
        }

        @keyframes spin{to{transform:rotate(360deg)}}
        @keyframes shimmer{0%,100%{opacity:.3}50%{opacity:.75}}
        @keyframes slideDown{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:none}}

        .staff-sidebar { width: 240px; }
        .staff-main    { flex: 1; min-width: 0; }
        .staff-content { padding: 24px; }

        @media (max-width: 768px) {
          .staff-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; z-index: 9000;
            transform: translateX(-100%); transition: transform 0.3s ease;
          }
          .staff-sidebar.open { transform: translateX(0); }
          .staff-sidebar-overlay {
            display: none; position: fixed; inset: 0; z-index: 8999;
            background: rgba(0,0,0,0.6); backdrop-filter: blur(2px);
          }
          .staff-sidebar-overlay.active { display: block; }
          .staff-content { padding: 14px; }
          .staff-topbar  { padding: 0 14px !important; height: 56px !important; }
          .staff-topbar-title { font-size: 17px !important; }
          .hamburger-btn { display: flex !important; }
          .staff-date { display: none !important; }
        }

        @media (max-width: 480px) {
          .staff-content { padding: 10px; }
        }
      `}</style>

      {/* Mobile overlay */}
      <div className={`staff-sidebar-overlay${sidebarOpen ? ' active' : ''}`} onClick={() => setSidebarOpen(false)} />

      {/* ── Sidebar ──────────────────────────────────────────────────────── */}
      <div className={`staff-sidebar${sidebarOpen ? ' open' : ''}`} style={{
        width: 240, background: SURFACE, borderRight: `1px solid ${BORDER}`,
        display: 'flex', flexDirection: 'column', flexShrink: 0,
      }}>
        {/* Logo */}
        <div style={{ display: 'flex', alignItems: 'center', gap: 12, padding: 20, borderBottom: `1px solid ${BORDER}`, flexShrink: 0 }}>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="38" height="38">
            <defs><linearGradient id="stg" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style={{ stopColor: '#FFB020' }}/><stop offset="100%" style={{ stopColor: '#F97316' }}/></linearGradient></defs>
            <polygon points="50,4 90,26 90,74 50,96 10,74 10,26" fill="#1e1e1e" stroke="#F97316" strokeWidth="4"/>
            <polygon points="54,18 32,54 48,54 44,82 68,46 52,46" fill="url(#stg)"/>
          </svg>
          <div>
            <div style={{ fontSize: 15, fontWeight: 700, letterSpacing: 2, color: '#fff' }}>TRONMATIX</div>
            <div style={{ fontSize: 10, fontWeight: 700, letterSpacing: 4, color: O }}>COMPUTER</div>
          </div>
        </div>

        {/* Nav */}
        <nav style={{ flex: 1, padding: '6px 0', overflowY: 'auto' }}>
          {navSections.map(section => (
            <div key={section.label}>
              <div style={{ fontSize: 14, color: TEXT_XFAINT, fontWeight: 700, padding: '10px 20px 4px', textTransform: 'uppercase', letterSpacing: 1, marginTop: 2 }}>
                {section.label}
              </div>
              {section.items.map(n => {
                const active = tab === n.id
                return (
                  <button key={n.id} onClick={() => { setTab(n.id); setSidebarOpen(false) }} style={{
                    width: '100%', display: 'flex', alignItems: 'center', gap: 10,
                    padding: '7px 20px', border: 'none', cursor: 'pointer', fontFamily: "'Rajdhani', sans-serif",
                    background: active ? 'rgba(249,115,22,0.08)' : 'transparent',
                    color: active ? O : TEXT_MUTED, fontSize: 16, fontWeight: 600,
                    borderLeft: active ? '3px solid #F97316' : '3px solid transparent',
                    transition, position: 'relative',
                  }}
                    onMouseEnter={e => { if (!active) { e.currentTarget.style.background = 'rgba(255,255,255,0.04)'; e.currentTarget.style.color = '#fff'; } }}
                    onMouseLeave={e => { if (!active) { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.color = TEXT_MUTED; } }}
                  >
                    <span style={{ fontSize: 15, width: 24, textAlign: 'center' }}>{n.icon}</span>
                    {n.label}
                    {active && <span style={{ marginLeft: 'auto', fontSize: 10, color: O, opacity: 0.5 }}>◀</span>}
                  </button>
                )
              })}
            </div>
          ))}
        </nav>

        {/* User footer */}
        <div style={{ padding: '14px 20px', borderTop: `1px solid ${BORDER}`, flexShrink: 0 }}>
          <div style={{ fontSize: 13, fontWeight: 700, color: 'var(--t-text)', marginBottom: 2 }}>
            {user?.name ?? user?.username ?? 'Staff'}
          </div>
          <div style={{ fontSize: 11, color: O, fontWeight: 700, letterSpacing: 1, marginBottom: 10 }}>
            {user?.role?.toUpperCase()}
          </div>
          <button onClick={handleLogout} style={{
            width: '100%', padding: '6px 0', background: 'rgba(239,68,68,0.1)',
            border: '1px solid rgba(239,68,68,0.2)', borderRadius: 8, color: R,
            fontSize: 12, fontWeight: 700, cursor: 'pointer', fontFamily: "'Rajdhani', sans-serif", letterSpacing: 1,
          }} onMouseEnter={e => e.currentTarget.style.background = 'rgba(239,68,68,0.2)'}
            onMouseLeave={e => e.currentTarget.style.background = 'rgba(239,68,68,0.1)'}>
            🚪 LOGOUT
          </button>
        </div>
      </div>

      {/* ── Main Content ─────────────────────────────────────────────────── */}
      <div className="staff-main" style={{ flex: 1, display: 'flex', flexDirection: 'column', overflow: 'auto', minWidth: 0 }}>
        {/* Topbar */}
        <div className="staff-topbar" style={{
          height: 60, background: SURFACE, borderBottom: `1px solid ${BORDER}`,
          display: 'flex', alignItems: 'center', justifyContent: 'space-between',
          padding: '0 24px', position: 'sticky', top: 0, zIndex: 100,
        }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <button onClick={() => setSidebarOpen(true)} className="hamburger-btn" style={{
              display: 'none', background: 'none', border: `1px solid ${BORDER_INPUT}`,
              borderRadius: 8, padding: 6, cursor: 'pointer', color: TEXT_MUTED,
            }}>☰</button>
            <div>
              <div className="staff-topbar-title" style={{ fontSize: 20, fontWeight: 700, color: '#fff', lineHeight: 1.2 }}>
                {flatNav.find(n => n.id === tab)?.label ?? 'Dashboard'}
              </div>
              <div className="staff-date" style={{ fontSize: 11, color: TEXT_FAINT, lineHeight: 1 }}>
                {new Date().toLocaleDateString('en-GB', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
              </div>
            </div>
          </div>
          <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
            {/* Online status indicator */}
            <div style={{
              display: 'inline-flex', alignItems: 'center', gap: 5,
              padding: '3px 10px', borderRadius: 20,
              fontSize: 11, fontWeight: 700, letterSpacing: '0.5px',
              background: 'rgba(34,197,94,0.10)',
              color: G, border: '1px solid rgba(34,197,94,0.25)',
            }}>
              <span style={{ width: 7, height: 7, borderRadius: '50%', background: G, display: 'inline-block' }} />
              ONLINE
            </div>
            {/* Theme toggle */}
            <button onClick={toggleTheme} style={{
              width: 34, height: 34, borderRadius: 8, border: `1px solid rgba(255,255,255,0.1)`,
              background: 'rgba(255,255,255,0.04)', color: 'var(--t-text-muted)',
              fontSize: 16, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center',
              transition: 'all 0.2s',
            }} title="Toggle theme">
              {theme === 'dark' ? '☀️' : '🌙'}
            </button>
            <span style={{
              display: 'inline-block', padding: '3px 10px', borderRadius: 20,
              fontSize: 11, fontWeight: 700, background: 'rgba(249,115,22,0.12)',
              color: O, border: '1px solid rgba(249,115,22,0.3)', letterSpacing: '1px',
            }}>
              {user?.role?.toUpperCase() || 'STAFF'}
            </span>
            <div style={{
              width: 34, height: 34, borderRadius: '50%', background: O,
              display: 'flex', alignItems: 'center', justifyContent: 'center',
              color: '#fff', fontWeight: 800, fontSize: 15,
            }}>
              {(user?.name ?? user?.username ?? 'S')[0].toUpperCase()}
            </div>
            <button onClick={handleLogout} style={{
              padding: '6px 14px', borderRadius: 8, border: '1px solid rgba(239,68,68,0.2)',
              background: 'rgba(239,68,68,0.08)', color: R, fontSize: 12, fontWeight: 700, cursor: 'pointer',
              letterSpacing: '0.5px', fontFamily: "'Rajdhani', sans-serif",
            }}>LOG OUT</button>
          </div>
        </div>

        {/* Content */}
        <div className="staff-content" style={{ flex: 1, padding: 24 }}>
          {TAB_CONTENT[tab]}
        </div>
      </div>
    </div>
  )
}
