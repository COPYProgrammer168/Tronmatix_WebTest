/**
 * src/pages/DevDashboard.jsx
 *
 * Developer Dashboard — connects to real Laravel API.
 * Tabs: System Health | API Logs | All Users | Env Info
 *
 * Endpoints used:
 *   GET /api/dev/health   → SystemTab  (new — add to api.php)
 *   GET /api/dev/logs     → ApiLogsTab (new — add to api.php)
 *   GET /api/admin/users  → UsersTab   (shared with staff)
 *   GET /api/dev/env      → EnvTab     (new — add to api.php)
 */
import { useState, useEffect, useCallback, useRef, memo } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import api from '../lib/axios'

// ── Nav ───────────────────────────────────────────────────────────────────────
const NAV = [
  { id: 'system', label: 'System',   icon: '⚙️'  },
  { id: 'logs',   label: 'API Logs', icon: '📋'  },
  { id: 'users',  label: 'Users',    icon: '👥'  },
  { id: 'env',    label: 'Env Info', icon: '🔑'  },
]

const METHOD_STYLE = {
  GET:    { bg: 'rgba(34,197,94,0.2)',   color: '#22c55e' },
  POST:   { bg: 'rgba(59,130,246,0.2)',  color: '#3b82f6' },
  PUT:    { bg: 'rgba(168,85,247,0.2)',  color: '#a855f7' },
  PATCH:  { bg: 'rgba(245,158,11,0.2)', color: '#f59e0b' },
  DELETE: { bg: 'rgba(239,68,68,0.2)',  color: '#ef4444' },
}

const STATUS_STYLE = (s) => {
  if (s >= 500) return '#ef4444'
  if (s >= 400) return '#f59e0b'
  if (s >= 300) return '#3b82f6'
  return '#22c55e'
}

const ROLE_COLORS = {
  customer:   { bg: 'rgba(156,163,175,0.15)', color: '#9ca3af' },
  admin:      { bg: 'rgba(249,115,22,0.15)',  color: '#F97316' },
  staff:      { bg: 'rgba(59,130,246,0.15)',  color: '#3b82f6' },
  superadmin: { bg: 'rgba(168,85,247,0.15)',  color: '#a855f7' },
  delivery:   { bg: 'rgba(34,197,94,0.15)',   color: '#22c55e' },
  developer:  { bg: 'rgba(99,102,241,0.15)',  color: '#6366f1' },
}

// ── useFetch — abort-safe, memoized ──────────────────────────────────────────
function useFetch(endpoint, { skip = false, refreshInterval = 0 } = {}) {
  const [data,    setData]    = useState(null)
  const [loading, setLoading] = useState(!skip)
  const [error,   setError]   = useState(null)
  const ctrlRef = useRef(null)

  const load = useCallback(async () => {
    if (ctrlRef.current) ctrlRef.current.abort()
    ctrlRef.current = new AbortController()
    setLoading(true)
    setError(null)
    try {
      const res = await api.get(endpoint, { signal: ctrlRef.current.signal })
      setData(res.data?.data ?? res.data)
    } catch (err) {
      if (err.name === 'CanceledError' || err.name === 'AbortError') return
      setError(err.response?.data?.message || err.message || 'Request failed.')
    } finally {
      setLoading(false)
    }
  }, [endpoint])

  useEffect(() => {
    if (skip) return
    load()
    if (refreshInterval > 0) {
      const t = setInterval(load, refreshInterval)
      return () => { clearInterval(t); ctrlRef.current?.abort() }
    }
    return () => ctrlRef.current?.abort()
  }, [load, skip, refreshInterval])

  return { data, loading, error, refetch: load }
}

// ── Shared UI ─────────────────────────────────────────────────────────────────
const Spinner = memo(({ color = '#3b82f6', size = 28 }) => (
  <div style={{ padding: 48, display: 'flex', justifyContent: 'center' }}>
    <div style={{ width: size, height: size, border: `3px solid ${color}33`, borderTopColor: color, borderRadius: '50%', animation: 'spin .7s linear infinite' }} />
  </div>
))

const ErrorState = memo(({ message, onRetry }) => (
  <div style={{ padding: '40px', textAlign: 'center' }}>
    <div style={{ fontSize: 28, marginBottom: 10 }}>⚠️</div>
    <div style={{ fontSize: 13, color: '#ef4444', fontWeight: 700, marginBottom: 8 }}>Failed to load</div>
    <div style={{ fontSize: 12, color: '#4b5563', marginBottom: 18, maxWidth: 280, margin: '0 auto 18px' }}>{message}</div>
    {onRetry && (
      <button onClick={onRetry} style={{ padding: '7px 20px', background: 'rgba(239,68,68,0.12)', border: '1px solid rgba(239,68,68,0.3)', borderRadius: 7, color: '#ef4444', fontSize: 12, fontWeight: 700, cursor: 'pointer' }}>
        ↻ Retry
      </button>
    )}
  </div>
))

const EmptyState = memo(({ label = 'No data found' }) => (
  <div style={{ padding: 48, textAlign: 'center', fontSize: 13, color: '#374151', fontWeight: 600 }}>
    📭 {label}
  </div>
))

function SkeletonRows({ cols = 5, rows = 4, color = '#3b82f6' }) {
  return (
    <>
      {Array.from({ length: rows }).map((_, ri) => (
        <tr key={ri}>
          {Array.from({ length: cols }).map((_, ci) => (
            <td key={ci} style={{ padding: '13px 16px' }}>
              <div style={{ height: 11, borderRadius: 5, background: `${color}18`, animation: `shimmer 1.4s ${ci * 0.08}s ease-in-out infinite` }} />
            </td>
          ))}
        </tr>
      ))}
    </>
  )
}

const Badge = memo(({ status, map }) => {
  const s = map?.[status] || { bg: 'rgba(75,85,99,0.2)', color: '#6b7280' }
  return (
    <span style={{ background: s.bg, color: s.color, borderRadius: 6, padding: '3px 10px', fontSize: 11, fontWeight: 700, letterSpacing: '0.05em', whiteSpace: 'nowrap' }}>
      {status?.replace(/_/g, ' ').toUpperCase()}
    </span>
  )
})

function TableBox({ headers, children, mono = false }) {
  return (
    <div style={{ background: '#0d1117', border: '1px solid #1e2a3a', borderRadius: 12, overflow: 'hidden' }}>
      <table style={{ width: '100%', borderCollapse: 'collapse', fontFamily: mono ? 'monospace' : 'inherit' }}>
        <thead>
          <tr style={{ borderBottom: '1px solid #1e2a3a' }}>
            {headers.map(h => (
              <th key={h} style={{ padding: '11px 16px', textAlign: 'left', fontSize: 10, color: '#374151', fontWeight: 700, letterSpacing: '0.1em' }}>{h}</th>
            ))}
          </tr>
        </thead>
        <tbody>{children}</tbody>
      </table>
    </div>
  )
}

function SectionBox({ title, right, children }) {
  return (
    <div style={{ background: '#0d1117', border: '1px solid #1e2a3a', borderRadius: 12, overflow: 'hidden', marginBottom: 16 }}>
      <div style={{ padding: '13px 18px', borderBottom: '1px solid #1e2a3a', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <span style={{ fontSize: 13, fontWeight: 700, color: '#f0f6ff' }}>{title}</span>
        {right}
      </div>
      {children}
    </div>
  )
}

// ── SYSTEM TAB ────────────────────────────────────────────────────────────────
function SystemTab() {
  // Auto-refresh health every 30s
  const { data, loading, error, refetch } = useFetch('/api/dev/health', { refreshInterval: 30000 })

  const STACK = [
    { label: 'Laravel',  key: 'laravel_version',  color: '#F97316' },
    { label: 'PHP',      key: 'php_version',       color: '#6366f1' },
    { label: 'Database', key: 'db_driver',         color: '#3b82f6' },
    { label: 'Cache',    key: 'cache_driver',      color: '#22c55e' },
    { label: 'Queue',    key: 'queue_driver',      color: '#f59e0b' },
    { label: 'Vite',     key: 'vite_version',      color: '#a855f7' },
  ]

  return (
    <div>
      {/* Stack info */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(140px,1fr))', gap: 12, marginBottom: 20 }}>
        {loading ? (
          STACK.map(s => (
            <div key={s.label} style={{ background: '#0d1117', border: `1px solid ${s.color}22`, borderRadius: 10, padding: '14px 16px', textAlign: 'center' }}>
              <div style={{ height: 20, background: `${s.color}18`, borderRadius: 5, marginBottom: 8, animation: 'shimmer 1.4s ease-in-out infinite' }} />
              <div style={{ fontSize: 10, color: '#374151', fontWeight: 700, letterSpacing: '0.08em' }}>{s.label}</div>
            </div>
          ))
        ) : error ? null : (
          STACK.map(s => (
            <div key={s.label} style={{ background: '#0d1117', border: `1px solid ${s.color}33`, borderRadius: 10, padding: '14px 16px', textAlign: 'center' }}>
              <div style={{ fontSize: 18, fontWeight: 800, color: s.color, fontFamily: 'Rajdhani,monospace', marginBottom: 4 }}>
                {data?.[s.key] ?? '—'}
              </div>
              <div style={{ fontSize: 10, color: '#374151', fontWeight: 700, letterSpacing: '0.08em' }}>{s.label}</div>
            </div>
          ))
        )}
      </div>

      {error ? <ErrorState message={error} onRetry={refetch} /> : (
        <SectionBox
          title="Health Checks"
          right={
            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
              <span style={{ fontSize: 11, color: '#374151' }}>Auto-refresh 30s</span>
              <button onClick={refetch} style={{ padding: '4px 12px', background: 'rgba(59,130,246,0.12)', border: '1px solid rgba(59,130,246,0.25)', borderRadius: 6, color: '#3b82f6', fontSize: 11, fontWeight: 700, cursor: 'pointer' }}>↻ Now</button>
            </div>
          }
        >
          {loading ? (
            <Spinner color="#3b82f6" />
          ) : (
            (data?.checks ?? []).map((c, i) => (
              <div key={i} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '11px 18px', borderBottom: i < (data.checks.length - 1) ? '1px solid #1e2a3a' : 'none' }}>
                <div>
                  <div style={{ fontSize: 13, color: '#9ca3af', fontWeight: 600 }}>{c.label}</div>
                  {c.detail && <div style={{ fontSize: 11, color: '#374151', marginTop: 2 }}>{c.detail}</div>}
                </div>
                <span style={{
                  background: c.status === 'ok' ? 'rgba(34,197,94,0.1)' : 'rgba(239,68,68,0.1)',
                  color:      c.status === 'ok' ? '#22c55e' : '#ef4444',
                  border:     `1px solid ${c.status === 'ok' ? 'rgba(34,197,94,0.25)' : 'rgba(239,68,68,0.25)'}`,
                  borderRadius: 6, padding: '3px 12px', fontSize: 11, fontWeight: 700, letterSpacing: '0.06em',
                }}>
                  {c.status === 'ok' ? 'OK' : c.status?.toUpperCase() ?? 'UNKNOWN'}
                </span>
              </div>
            ))
          )}
        </SectionBox>
      )}
    </div>
  )
}

// ── API LOGS TAB ──────────────────────────────────────────────────────────────
function ApiLogsTab() {
  const [methodFilter, setMethodFilter] = useState('ALL')
  const [statusFilter, setStatusFilter] = useState('ALL')
  const { data: logs, loading, error, refetch } = useFetch('/api/dev/logs')

  const methods = ['ALL', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE']
  const statusGroups = ['ALL', '2xx', '4xx', '5xx']

  const filtered = (logs ?? []).filter(l => {
    const methodOk = methodFilter === 'ALL' || l.method === methodFilter
    const statusOk = statusFilter === 'ALL'
      || (statusFilter === '2xx' && l.status >= 200 && l.status < 300)
      || (statusFilter === '4xx' && l.status >= 400 && l.status < 500)
      || (statusFilter === '5xx' && l.status >= 500)
    return methodOk && statusOk
  })

  return (
    <div>
      {/* Filters */}
      <div style={{ display: 'flex', gap: 8, marginBottom: 16, flexWrap: 'wrap', alignItems: 'center' }}>
        <div style={{ display: 'flex', gap: 6 }}>
          {methods.map(m => {
            const ms = METHOD_STYLE[m] ?? { bg: 'rgba(249,115,22,0.15)', color: '#F97316' }
            const active = methodFilter === m
            return (
              <button key={m} onClick={() => setMethodFilter(m)} style={{
                padding: '5px 12px', borderRadius: 6, border: '1px solid', fontSize: 11, fontWeight: 800, cursor: 'pointer', fontFamily: 'monospace',
                borderColor: active ? (m === 'ALL' ? '#F97316' : ms.color) : '#1e2a3a',
                background:  active ? (m === 'ALL' ? 'rgba(249,115,22,0.15)' : ms.bg) : 'transparent',
                color:       active ? (m === 'ALL' ? '#F97316' : ms.color) : '#374151',
              }}>{m}</button>
            )
          })}
        </div>
        <div style={{ width: 1, height: 20, background: '#1e2a3a' }} />
        <div style={{ display: 'flex', gap: 6 }}>
          {statusGroups.map(s => (
            <button key={s} onClick={() => setStatusFilter(s)} style={{
              padding: '5px 12px', borderRadius: 6, border: '1px solid', fontSize: 11, fontWeight: 700, cursor: 'pointer', fontFamily: 'monospace',
              borderColor: statusFilter === s ? '#3b82f6' : '#1e2a3a',
              background:  statusFilter === s ? 'rgba(59,130,246,0.15)' : 'transparent',
              color:       statusFilter === s ? '#3b82f6' : '#374151',
            }}>{s}</button>
          ))}
        </div>
        <button onClick={refetch} style={{ marginLeft: 'auto', padding: '5px 14px', border: '1px solid #1e2a3a', borderRadius: 6, background: 'transparent', color: '#374151', fontSize: 11, cursor: 'pointer' }}>
          ↻ Refresh
        </button>
      </div>

      {error ? <ErrorState message={error} onRetry={refetch} /> : (
        <TableBox headers={['METHOD','ENDPOINT','STATUS','TIME','IP','AT']} mono>
          {loading ? <SkeletonRows cols={6} rows={8} color="#3b82f6" /> : filtered.length === 0 ? (
            <tr><td colSpan={6}><EmptyState label="No logs match the current filter" /></td></tr>
          ) : filtered.map((l, i) => {
            const ms = METHOD_STYLE[l.method] ?? { bg: 'rgba(75,85,99,0.2)', color: '#6b7280' }
            return (
              <tr key={i} style={{ borderBottom: '1px solid #1e2a3a', background: i % 2 ? 'rgba(255,255,255,0.01)' : 'transparent' }}>
                <td style={{ padding: '10px 16px' }}>
                  <span style={{ background: ms.bg, color: ms.color, borderRadius: 4, padding: '2px 8px', fontSize: 11, fontWeight: 800 }}>{l.method}</span>
                </td>
                <td style={{ padding: '10px 16px', fontSize: 12, color: '#9ca3af', maxWidth: 220, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{l.endpoint}</td>
                <td style={{ padding: '10px 16px', fontSize: 12, fontWeight: 700, color: STATUS_STYLE(l.status) }}>{l.status}</td>
                <td style={{ padding: '10px 16px', fontSize: 12, color: l.duration_ms > 500 ? '#f59e0b' : '#4b5563' }}>{l.duration_ms}ms</td>
                <td style={{ padding: '10px 16px', fontSize: 12, color: '#374151' }}>{l.ip}</td>
                <td style={{ padding: '10px 16px', fontSize: 11, color: '#374151' }}>{l.created_at?.slice(11, 19) ?? '—'}</td>
              </tr>
            )
          })}
        </TableBox>
      )}
    </div>
  )
}

// ── USERS TAB ─────────────────────────────────────────────────────────────────
function UsersTab() {
  const [search, setSearch] = useState('')
  const [roleFilter, setRoleFilter] = useState('all')
  const { data: users, loading, error, refetch } = useFetch('/api/admin/users')

  const roles = ['all', 'customer', 'staff', 'admin', 'superadmin', 'developer', 'delivery']

  const filtered = (users ?? []).filter(u => {
    const matchSearch = !search || u.name?.toLowerCase().includes(search.toLowerCase()) || u.email?.toLowerCase().includes(search.toLowerCase())
    const matchRole = roleFilter === 'all' || u.role === roleFilter
    return matchSearch && matchRole
  })

  return (
    <div>
      <div style={{ display: 'flex', gap: 10, marginBottom: 16, flexWrap: 'wrap' }}>
        <input
          value={search} onChange={e => setSearch(e.target.value)}
          placeholder="Search by name or email..."
          style={{ flex: 1, minWidth: 200, padding: '8px 14px', background: '#0d1117', border: '1px solid #1e2a3a', borderRadius: 8, color: '#f0f6ff', fontSize: 13, outline: 'none', fontFamily: 'monospace' }}
        />
        <select value={roleFilter} onChange={e => setRoleFilter(e.target.value)}
          style={{ padding: '8px 14px', background: '#0d1117', border: '1px solid #1e2a3a', borderRadius: 8, color: '#9ca3af', fontSize: 13, outline: 'none', cursor: 'pointer' }}>
          {roles.map(r => <option key={r} value={r}>{r.toUpperCase()}</option>)}
        </select>
        <button onClick={refetch} style={{ padding: '8px 14px', border: '1px solid #1e2a3a', borderRadius: 8, background: 'transparent', color: '#374151', fontSize: 13, cursor: 'pointer' }}>↻</button>
      </div>

      {error ? <ErrorState message={error} onRetry={refetch} /> : (
        <TableBox headers={['ID','NAME','EMAIL','ROLE','STATUS','JOINED','ACTIONS']}>
          {loading ? <SkeletonRows cols={7} rows={6} color="#3b82f6" /> : filtered.length === 0 ? (
            <tr><td colSpan={7}><EmptyState label="No users match your search" /></td></tr>
          ) : filtered.map((u, i) => (
            <tr key={u.id} style={{ borderBottom: '1px solid #1e2a3a', background: i % 2 ? 'rgba(255,255,255,0.01)' : 'transparent' }}>
              <td style={{ padding: '12px 16px', fontSize: 12, color: '#374151', fontFamily: 'monospace' }}>#{u.id}</td>
              <td style={{ padding: '12px 16px', fontSize: 13, color: '#f0f6ff', fontWeight: 600 }}>{u.name ?? u.username}</td>
              <td style={{ padding: '12px 16px', fontSize: 12, color: '#4b5563', fontFamily: 'monospace' }}>{u.email}</td>
              <td style={{ padding: '12px 16px' }}><Badge status={u.role} map={ROLE_COLORS} /></td>
              <td style={{ padding: '12px 16px' }}>
                <span style={{ background: u.email_verified_at ? 'rgba(34,197,94,0.1)' : 'rgba(239,68,68,0.1)', color: u.email_verified_at ? '#22c55e' : '#ef4444', borderRadius: 5, padding: '2px 8px', fontSize: 11, fontWeight: 700 }}>
                  {u.email_verified_at ? 'ACTIVE' : 'UNVERIFIED'}
                </span>
              </td>
              <td style={{ padding: '12px 16px', fontSize: 12, color: '#374151' }}>{u.created_at?.slice(0, 10) ?? '—'}</td>
              <td style={{ padding: '12px 16px', display: 'flex', gap: 8 }}>
                <button style={{ padding: '4px 10px', background: 'rgba(59,130,246,0.12)', border: '1px solid rgba(59,130,246,0.25)', borderRadius: 5, color: '#3b82f6', fontSize: 11, fontWeight: 700, cursor: 'pointer' }}>Edit</button>
                <button style={{ padding: '4px 10px', background: 'rgba(239,68,68,0.1)', border: '1px solid rgba(239,68,68,0.2)', borderRadius: 5, color: '#ef4444', fontSize: 11, fontWeight: 700, cursor: 'pointer' }}>Ban</button>
              </td>
            </tr>
          ))}
        </TableBox>
      )}
    </div>
  )
}

// ── ENV TAB ───────────────────────────────────────────────────────────────────
function EnvTab() {
  const [revealed, setRevealed] = useState(false)
  const { data, loading, error, refetch } = useFetch('/api/dev/env')

  // Mask sensitive values unless revealed
  const display = (item) => {
    if (!item.sensitive || revealed) return item.value
    return '•'.repeat(Math.min(item.value?.length ?? 8, 16))
  }

  const VALUE_COLOR = (item) => {
    if (item.sensitive && !revealed) return '#374151'
    if (item.value === 'true' || item.value === 'production') return '#22c55e'
    if (item.value === 'false' || item.value === 'local') return '#f59e0b'
    return '#9ca3af'
  }

  return (
    <div>
      <div style={{ background: 'rgba(239,68,68,0.06)', border: '1px solid rgba(239,68,68,0.15)', borderRadius: 8, padding: '10px 16px', marginBottom: 16, fontSize: 12, color: '#fca5a5', display: 'flex', alignItems: 'center', gap: 8 }}>
        ⚠️ Sensitive values masked by default. Never share this page with non-developers.
      </div>

      {error ? <ErrorState message={error} onRetry={refetch} /> : (
        <SectionBox
          title="Environment Variables"
          right={
            <button
              onClick={() => setRevealed(r => !r)}
              style={{
                padding: '5px 14px', borderRadius: 6, fontSize: 12, fontWeight: 700, cursor: 'pointer', transition: 'all .15s',
                background: revealed ? 'rgba(239,68,68,0.12)' : 'rgba(59,130,246,0.12)',
                border:     revealed ? '1px solid rgba(239,68,68,0.3)' : '1px solid rgba(59,130,246,0.25)',
                color:      revealed ? '#ef4444' : '#3b82f6',
              }}>
              {revealed ? '🙈 Hide Secrets' : '👁 Reveal All'}
            </button>
          }
        >
          {loading ? <Spinner color="#3b82f6" /> : (
            <table style={{ width: '100%', borderCollapse: 'collapse', fontFamily: 'monospace' }}>
              <thead>
                <tr style={{ borderBottom: '1px solid #1e2a3a' }}>
                  {['KEY','VALUE','SAFE TO SHARE'].map(h => (
                    <th key={h} style={{ padding: '10px 16px', textAlign: 'left', fontSize: 10, color: '#374151', fontWeight: 700, letterSpacing: '0.1em' }}>{h}</th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {(data ?? []).map((item, i) => (
                  <tr key={i} style={{ borderBottom: '1px solid #1e2a3a' }}>
                    <td style={{ padding: '11px 16px', fontSize: 13, color: '#6366f1', fontWeight: 700 }}>{item.key}</td>
                    <td style={{ padding: '11px 16px', fontSize: 13, color: VALUE_COLOR(item), transition: 'color .2s' }}>
                      {display(item)}
                    </td>
                    <td style={{ padding: '11px 16px' }}>
                      <span style={{ background: item.sensitive ? 'rgba(239,68,68,0.1)' : 'rgba(34,197,94,0.1)', color: item.sensitive ? '#ef4444' : '#22c55e', borderRadius: 5, padding: '2px 8px', fontSize: 11, fontWeight: 700 }}>
                        {item.sensitive ? 'NO' : 'YES'}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </SectionBox>
      )}
    </div>
  )
}

// ── Main ──────────────────────────────────────────────────────────────────────
export default function DevDashboard() {
  const [tab, setTab] = useState('system')
  const { user, logout } = useAuth()
  const navigate = useNavigate()

  const handleLogout = async () => {
    await logout()
    navigate('/dev/login', { replace: true })
  }

  const TAB_CONTENT = {
    system: <SystemTab />,
    logs:   <ApiLogsTab />,
    users:  <UsersTab />,
    env:    <EnvTab />,
  }

  return (
    <div style={{ minHeight: '100vh', background: '#060b18', display: 'flex', fontFamily: 'Rajdhani,sans-serif' }}>
      <style>{`
        @keyframes spin { to { transform: rotate(360deg) } }
        @keyframes shimmer { 0%,100%{opacity:.3} 50%{opacity:.75} }
      `}</style>

      {/* Sidebar */}
      <div style={{ width: 220, background: '#0a0f1a', borderRight: '1px solid #1e2a3a', display: 'flex', flexDirection: 'column', flexShrink: 0 }}>
        <div style={{ padding: '24px 20px 20px', borderBottom: '1px solid #1e2a3a' }}>
          <div style={{ fontSize: 22, fontWeight: 900, color: '#f0f6ff', letterSpacing: 1 }}>Tron<span style={{ color: '#3b82f6' }}>matix</span></div>
          <div style={{ fontSize: 11, color: '#1e3a5f', fontWeight: 700, letterSpacing: '0.12em', marginTop: 2 }}>DEV PORTAL</div>
        </div>
        <nav style={{ flex: 1, padding: '12px 10px' }}>
          {NAV.map(n => (
            <button key={n.id} onClick={() => setTab(n.id)} style={{
              width: '100%', display: 'flex', alignItems: 'center', gap: 10, padding: '10px 12px',
              borderRadius: 8, border: 'none', cursor: 'pointer', marginBottom: 4, textAlign: 'left',
              fontFamily: 'Rajdhani,sans-serif', fontWeight: 700, fontSize: 14, letterSpacing: '0.05em',
              background: tab === n.id ? 'rgba(59,130,246,0.12)' : 'transparent',
              color:      tab === n.id ? '#3b82f6' : '#4b5563',
              borderLeft: tab === n.id ? '2px solid #3b82f6' : '2px solid transparent',
            }}>
              <span style={{ fontSize: 16 }}>{n.icon}</span>{n.label}
            </button>
          ))}
        </nav>
        <div style={{ padding: '16px 14px', borderTop: '1px solid #1e2a3a' }}>
          <div style={{ fontSize: 13, fontWeight: 700, color: '#f0f6ff', marginBottom: 2, fontFamily: 'monospace' }}>{user?.name ?? user?.username ?? 'Developer'}</div>
          <div style={{ fontSize: 11, color: '#1e3a5f', marginBottom: 12, fontFamily: 'monospace' }}>{user?.email}</div>
          <button onClick={handleLogout} style={{ width: '100%', padding: '8px 0', background: 'rgba(239,68,68,0.08)', border: '1px solid rgba(239,68,68,0.15)', borderRadius: 7, color: '#ef4444', fontSize: 13, fontWeight: 700, cursor: 'pointer', fontFamily: 'Rajdhani,sans-serif' }}>
            🚪 Logout
          </button>
        </div>
      </div>

      {/* Main */}
      <div style={{ flex: 1, display: 'flex', flexDirection: 'column', overflow: 'auto' }}>
        <div style={{ padding: '18px 28px', borderBottom: '1px solid #1e2a3a', display: 'flex', alignItems: 'center', justifyContent: 'space-between', background: '#0a0f1a' }}>
          <div>
            <div style={{ fontSize: 22, fontWeight: 800, color: '#f0f6ff' }}>{NAV.find(n => n.id === tab)?.label}</div>
            <div style={{ fontSize: 12, color: '#1e3a5f', fontFamily: 'monospace' }}>
              dev@tronmatix — {new Date().toISOString().slice(0, 19).replace('T', ' ')} UTC
            </div>
          </div>
          <div style={{ width: 36, height: 36, borderRadius: '50%', background: 'rgba(59,130,246,0.15)', border: '2px solid #3b82f6', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#3b82f6', fontWeight: 800, fontSize: 16 }}>
            {(user?.name ?? user?.username ?? 'D')[0].toUpperCase()}
          </div>
        </div>
        <div style={{ flex: 1, padding: '24px 28px' }}>
          {TAB_CONTENT[tab]}
        </div>
      </div>
    </div>
  )
}