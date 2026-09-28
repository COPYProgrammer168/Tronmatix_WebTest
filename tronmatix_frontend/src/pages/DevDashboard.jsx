/**
 * src/pages/DevDashboard.jsx
 *
 * Developer Dashboard — connects to real Laravel API.
 * Redesigned with Blade dashboard visual language + blue accent.
 *
 * Tabs: System Health | API Logs | All Users | Env Info
 *
 * Endpoints:
 *   GET /api/dev/health   → SystemTab
 *   GET /api/dev/logs     → ApiLogsTab
 *   GET /api/admin/users  → UsersTab
 *   GET /api/dev/env      → EnvTab
 */
import { useState, useEffect, useCallback, useRef, memo } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import api from '../lib/axios'

// ── Nav sections (Blade-style) ────────────────────────────────────────────────
const NAV_SECTIONS = [
  {
    label: 'SYSTEM',
    items: [
      { id: 'system', label: 'Health',       icon: '⚙️' },
      { id: 'logs',   label: 'API Logs',     icon: '📋' },
      { id: 'env',    label: 'Environment',  icon: '🔑' },
    ],
  },
  {
    label: 'MONITOR',
    items: [
      { id: 'activity', label: 'Activity',   icon: '📊' },
      { id: 'users',    label: 'All Users',  icon: '👥' },
    ],
  },
]

const ACCENT = '#3B82F6' // blue accent for dev portal

const METHOD_STYLE = {
  GET:      { bg: 'rgba(34,197,94,0.2)',   color: '#22c55e' },
  POST:     { bg: 'rgba(59,130,246,0.2)',  color: '#3b82f6' },
  PUT:      { bg: 'rgba(168,85,247,0.2)',  color: '#a855f7' },
  PATCH:    { bg: 'rgba(245,158,11,0.2)', color: '#f59e0b' },
  DELETE:   { bg: 'rgba(239,68,68,0.2)',  color: '#ef4444' },
  ERROR:    { bg: 'rgba(239,68,68,0.2)',  color: '#ef4444' },
  CRITICAL: { bg: 'rgba(239,68,68,0.3)',  color: '#ff0000' },
  WARNING:  { bg: 'rgba(245,158,11,0.2)', color: '#f59e0b' },
  INFO:     { bg: 'rgba(34,197,94,0.15)', color: '#22c55e' },
  DEBUG:    { bg: 'rgba(99,102,241,0.2)', color: '#6366f1' },
  NOTICE:   { bg: 'rgba(59,130,246,0.15)',color: '#3b82f6' },
  LOG:      { bg: 'rgba(75,85,99,0.2)',   color: '#6b7280' },
}

const STATUS_STYLE = (s) => {
  if (s >= 500) return '#ef4444'
  if (s >= 400) return '#f59e0b'
  if (s >= 300) return '#3b82f6'
  return '#22c55e'
}

const ROLE_COLORS = {
  customer:   { bg: 'rgba(156,163,175,0.15)', color: '#9ca3af', border: 'rgba(156,163,175,0.3)' },
  admin:      { bg: 'rgba(249,115,22,0.15)',  color: '#F97316', border: 'rgba(249,115,22,0.3)' },
  staff:      { bg: 'rgba(59,130,246,0.15)',  color: '#3b82f6', border: 'rgba(59,130,246,0.3)' },
  superadmin: { bg: 'rgba(168,85,247,0.15)',  color: '#a855f7', border: 'rgba(168,85,247,0.3)' },
  delivery:   { bg: 'rgba(34,197,94,0.15)',   color: '#22c55e', border: 'rgba(34,197,94,0.3)' },
  developer:  { bg: 'rgba(99,102,241,0.15)',  color: '#6366f1', border: 'rgba(99,102,241,0.3)' },
}

// ── Shared UI ─────────────────────────────────────────────────────────────────
const Spinner = memo(({ color = ACCENT, size = 28 }) => (
  <div style={{ padding: 48, display: 'flex', justifyContent: 'center' }}>
    <div style={{ width: size, height: size, border: `3px solid ${color}33`, borderTopColor: color, borderRadius: '50%', animation: 'spin .7s linear infinite' }} />
  </div>
))

const ErrorState = memo(({ message, onRetry }) => (
  <div style={{ padding: 40, textAlign: 'center' }}>
    <div style={{ fontSize: 28, marginBottom: 10 }}>⚠️</div>
    <div style={{ fontSize: 13, color: '#EF4444', fontWeight: 700, marginBottom: 8 }}>Failed to load</div>
    <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.45)', marginBottom: 18, maxWidth: 280, margin: '0 auto 18px' }}>{message}</div>
    {onRetry && (
      <button onClick={onRetry} style={{
        padding: '7px 20px', background: 'rgba(239,68,68,0.12)', border: '1px solid rgba(239,68,68,0.3)',
        borderRadius: 8, color: '#EF4444', fontSize: 12, fontWeight: 700, cursor: 'pointer', letterSpacing: '1px',
      }}>↻ RETRY</button>
    )}
  </div>
))

const EmptyState = memo(({ label = 'No data found' }) => (
  <div style={{ padding: 48, textAlign: 'center', fontSize: 14, color: 'rgba(255,255,255,0.35)', fontWeight: 600 }}>
    📭 {label}
  </div>
))

function SkeletonRows({ cols = 5, rows = 4, color = ACCENT }) {
  return (
    <>
      {Array.from({ length: rows }).map((_, ri) => (
        <tr key={ri}>
          {Array.from({ length: cols }).map((_, ci) => (
            <td key={ci} style={{ padding: '14px 16px' }}>
              <div style={{ height: 11, borderRadius: 5, background: `${color}18`, animation: `shimmer 1.4s ${ci * 0.08}s ease-in-out infinite` }} />
            </td>
          ))}
        </tr>
      ))}
    </>
  )
}

const Badge = memo(({ status, map }) => {
  const s = map?.[status] || { bg: 'rgba(75,85,99,0.2)', color: '#6B7280', border: 'rgba(75,85,99,0.3)' }
  return (
    <span style={{
      display: 'inline-block', padding: '3px 10px', borderRadius: 20,
      fontSize: 12, fontWeight: 600, letterSpacing: '0.5px', whiteSpace: 'nowrap',
      background: s.bg, color: s.color, border: `1px solid ${s.border}`,
    }}>
      {status?.replace(/_/g, ' ').toUpperCase()}
    </span>
  )
})

// ── Table wrapper (Blade card style) ──────────────────────────────────────────
function TableBox({ headers, children, mono = false }) {
  return (
    <div style={{ background: '#111111', border: '1px solid rgba(255,255,255,0.07)', borderRadius: 14, overflow: 'hidden' }}>
      <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 14, fontFamily: mono ? "'Rajdhani', monospace" : "'Rajdhani', sans-serif" }}>
        <thead>
          <tr style={{ borderBottom: '1px solid rgba(255,255,255,0.07)' }}>
            {headers.map(h => (
              <th key={h} style={{
                padding: '12px 16px', textAlign: 'left', fontSize: 14, color: 'rgba(255,255,255,0.55)',
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

function SectionBox({ title, right, children }) {
  return (
    <div style={{ background: '#111111', border: '1px solid rgba(255,255,255,0.07)', borderRadius: 14, overflow: 'hidden', marginBottom: 16 }}>
      <div style={{
        padding: '13px 18px', borderBottom: '1px solid rgba(255,255,255,0.07)',
        display: 'flex', justifyContent: 'space-between', alignItems: 'center',
      }}>
        <span style={{ fontSize: 16, fontWeight: 700, color: '#fff' }}>{title}</span>
        {right}
      </div>
      {children}
    </div>
  )
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

// ── SYSTEM TAB ────────────────────────────────────────────────────────────────
function SystemTab() {
  const { data, loading, error, refetch } = useFetch('/api/dev/health', { refreshInterval: 60000 })

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
      {/* Stack info cards */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(140px,1fr))', gap: 12, marginBottom: 20 }}>
        {loading ? (
          STACK.map(s => (
            <div key={s.label} style={{
              background: '#111111', border: `1px solid ${s.color}22`, borderRadius: 14,
              padding: '16px 16px', textAlign: 'center',
            }}>
              <div style={{ height: 20, background: `${s.color}18`, borderRadius: 5, marginBottom: 8, animation: 'shimmer 1.4s ease-in-out infinite' }} />
              <div style={{ fontSize: 10, color: 'rgba(255,255,255,0.35)', fontWeight: 700, letterSpacing: '1px' }}>{s.label}</div>
            </div>
          ))
        ) : error ? null : (
          STACK.map(s => (
            <div key={s.label} style={{
              background: '#111111', border: `1px solid ${s.color}33`, borderRadius: 14,
              padding: '16px 16px', textAlign: 'center',
              transition: 'border-color 0.2s, transform 0.2s',
            }}
              onMouseEnter={e => { e.currentTarget.style.borderColor = s.color; e.currentTarget.style.transform = 'translateY(-2px)' }}
              onMouseLeave={e => { e.currentTarget.style.borderColor = ''; e.currentTarget.style.transform = '' }}
            >
              <div style={{ fontSize: 20, fontWeight: 800, color: s.color, fontFamily: "'Rajdhani', monospace", marginBottom: 4 }}>
                {data?.[s.key] ?? '—'}
              </div>
              <div style={{ fontSize: 10, color: 'rgba(255,255,255,0.35)', fontWeight: 700, letterSpacing: '1px' }}>{s.label}</div>
            </div>
          ))
        )}
      </div>

      {error ? <ErrorState message={error} onRetry={refetch} /> : (
        <SectionBox
          title="Health Checks"
          right={
            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
              <span style={{ fontSize: 11, color: 'rgba(255,255,255,0.35)' }}>Auto-refresh 60s</span>
              <button onClick={refetch} style={{
                padding: '4px 12px', background: 'rgba(59,130,246,0.12)', border: '1px solid rgba(59,130,246,0.25)',
                borderRadius: 6, color: ACCENT, fontSize: 11, fontWeight: 700, cursor: 'pointer', letterSpacing: '0.5px',
              }}>↻ NOW</button>
            </div>
          }
        >
          {loading ? (
            <Spinner color={ACCENT} />
          ) : (
            (data?.checks ?? []).map((c, i) => (
              <div key={i} style={{
                display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                padding: '11px 18px', borderBottom: i < (data.checks.length - 1) ? '1px solid rgba(255,255,255,0.04)' : 'none',
              }}>
                <div>
                  <div style={{ fontSize: 14, color: 'rgba(255,255,255,0.6)', fontWeight: 600 }}>{c.label}</div>
                  {c.detail && <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.35)', marginTop: 2 }}>{c.detail}</div>}
                </div>
                <span style={{
                  display: 'inline-block', padding: '3px 12px', borderRadius: 20,
                  fontSize: 11, fontWeight: 600, letterSpacing: '0.5px',
                  background: c.status === 'ok' ? 'rgba(34,197,94,0.12)' : 'rgba(239,68,68,0.12)',
                  color:      c.status === 'ok' ? '#22C55E' : '#EF4444',
                  border:     `1px solid ${c.status === 'ok' ? 'rgba(34,197,94,0.3)' : 'rgba(239,68,68,0.3)'}`,
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
                borderColor: active ? (m === 'ALL' ? ACCENT : ms.color) : 'rgba(255,255,255,0.1)',
                background:  active ? (m === 'ALL' ? 'rgba(59,130,246,0.12)' : ms.bg) : 'transparent',
                color:       active ? (m === 'ALL' ? ACCENT : ms.color) : 'rgba(255,255,255,0.45)',
                transition: 'all 0.15s',
              }}>{m}</button>
            )
          })}
        </div>
        <div style={{ width: 1, height: 20, background: 'rgba(255,255,255,0.07)' }} />
        <div style={{ display: 'flex', gap: 6 }}>
          {statusGroups.map(s => (
            <button key={s} onClick={() => setStatusFilter(s)} style={{
              padding: '5px 12px', borderRadius: 6, border: '1px solid', fontSize: 11, fontWeight: 700, cursor: 'pointer', fontFamily: 'monospace',
              borderColor: statusFilter === s ? ACCENT : 'rgba(255,255,255,0.1)',
              background:  statusFilter === s ? 'rgba(59,130,246,0.12)' : 'transparent',
              color:       statusFilter === s ? ACCENT : 'rgba(255,255,255,0.45)',
              transition: 'all 0.15s',
            }}>{s}</button>
          ))}
        </div>
        <button onClick={refetch} style={{
          marginLeft: 'auto', padding: '5px 14px', border: '1px solid rgba(255,255,255,0.1)', borderRadius: 6,
          background: 'transparent', color: 'rgba(255,255,255,0.45)', fontSize: 11, cursor: 'pointer',
        }}>↻ Refresh</button>
      </div>

      {error ? <ErrorState message={error} onRetry={refetch} /> : (
        <TableBox headers={['METHOD','ENDPOINT','STATUS','TIME','IP','AT']} mono>
          {loading ? <SkeletonRows cols={6} rows={8} color={ACCENT} /> : filtered.length === 0 ? (
            <tr><td colSpan={6}><EmptyState label="No logs match the current filter" /></td></tr>
          ) : filtered.map((l, i) => {
            const ms = METHOD_STYLE[l.method] ?? { bg: 'rgba(75,85,99,0.2)', color: '#6b7280' }
            return (
              <tr key={i} style={{
                borderBottom: '1px solid rgba(255,255,255,0.04)',
                background: i % 2 ? 'rgba(255,255,255,0.02)' : 'transparent',
                transition: 'background 0.15s',
              }}
                onMouseEnter={e => e.currentTarget.style.background = 'rgba(255,255,255,0.04)'}
                onMouseLeave={e => e.currentTarget.style.background = i % 2 ? 'rgba(255,255,255,0.02)' : 'transparent'}
              >
                <td style={{ padding: '10px 16px' }}>
                  <span style={{
                    background: ms.bg, color: ms.color, borderRadius: 4, padding: '2px 8px',
                    fontSize: 11, fontWeight: 800, fontFamily: 'monospace',
                  }}>{l.method}</span>
                </td>
                <td style={{
                  padding: '10px 16px', fontSize: 12, color: 'rgba(255,255,255,0.55)',
                  maxWidth: 220, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                }}>{l.endpoint}</td>
                <td style={{ padding: '10px 16px', fontSize: 12, fontWeight: 700, color: STATUS_STYLE(l.status) }}>{l.status}</td>
                <td style={{ padding: '10px 16px', fontSize: 12, color: l.duration_ms > 500 ? '#f59e0b' : 'rgba(255,255,255,0.35)' }}>{l.duration_ms}ms</td>
                <td style={{ padding: '10px 16px', fontSize: 12, color: 'rgba(255,255,255,0.35)' }}>{l.ip}</td>
                <td style={{ padding: '10px 16px', fontSize: 11, color: 'rgba(255,255,255,0.35)' }}>{l.created_at?.slice(11, 19) ?? '—'}</td>
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
          style={{
            flex: 1, minWidth: 200, padding: '8px 14px', background: '#1A1A1A',
            border: '1px solid rgba(255,255,255,0.1)', borderRadius: 10, color: '#fff',
            fontSize: 13, outline: 'none', fontFamily: "'Rajdhani', sans-serif", fontWeight: 500,
          }}
        />
        <select value={roleFilter} onChange={e => setRoleFilter(e.target.value)}
          style={{
            padding: '8px 14px', background: '#1A1A1A', border: '1px solid rgba(255,255,255,0.1)',
            borderRadius: 10, color: 'rgba(255,255,255,0.55)', fontSize: 13, outline: 'none', cursor: 'pointer',
          }}>
          {roles.map(r => <option key={r} value={r}>{r.toUpperCase()}</option>)}
        </select>
        <button onClick={refetch} style={{
          padding: '8px 14px', border: '1px solid rgba(255,255,255,0.1)', borderRadius: 10,
          background: 'transparent', color: 'rgba(255,255,255,0.45)', fontSize: 13, cursor: 'pointer',
        }}>↻</button>
      </div>

      {error ? <ErrorState message={error} onRetry={refetch} /> : (
        <TableBox headers={['ID','NAME','EMAIL','ROLE','STATUS','JOINED','ACTIONS']}>
          {loading ? <SkeletonRows cols={7} rows={6} color={ACCENT} /> : filtered.length === 0 ? (
            <tr><td colSpan={7}><EmptyState label="No users match your search" /></td></tr>
          ) : filtered.map((u, i) => (
            <tr key={u.id} style={{
              borderBottom: '1px solid rgba(255,255,255,0.04)',
              background: i % 2 ? 'rgba(255,255,255,0.02)' : 'transparent',
              transition: 'background 0.15s',
            }}
              onMouseEnter={e => e.currentTarget.style.background = 'rgba(255,255,255,0.04)'}
              onMouseLeave={e => e.currentTarget.style.background = i % 2 ? 'rgba(255,255,255,0.02)' : 'transparent'}
            >
              <td style={{ padding: '12px 16px', fontSize: 12, color: 'rgba(255,255,255,0.35)', fontFamily: 'monospace' }}>#{u.id}</td>
              <td style={{ padding: '12px 16px', fontSize: 14, color: 'rgba(255,255,255,0.85)', fontWeight: 600 }}>{u.name ?? u.username}</td>
              <td style={{ padding: '12px 16px', fontSize: 12, color: 'rgba(255,255,255,0.45)', fontFamily: 'monospace' }}>{u.email}</td>
              <td style={{ padding: '12px 16px' }}><Badge status={u.role} map={ROLE_COLORS} /></td>
              <td style={{ padding: '12px 16px' }}>
                <span style={{
                  display: 'inline-block', padding: '2px 8px', borderRadius: 20,
                  fontSize: 11, fontWeight: 600, letterSpacing: '0.5px',
                  background: u.email_verified_at ? 'rgba(34,197,94,0.12)' : 'rgba(239,68,68,0.12)',
                  color: u.email_verified_at ? '#22C55E' : '#EF4444',
                  border: `1px solid ${u.email_verified_at ? 'rgba(34,197,94,0.3)' : 'rgba(239,68,68,0.3)'}`,
                }}>
                  {u.email_verified_at ? 'ACTIVE' : 'UNVERIFIED'}
                </span>
              </td>
              <td style={{ padding: '12px 16px', fontSize: 12, color: 'rgba(255,255,255,0.35)' }}>{u.created_at?.slice(0, 10) ?? '—'}</td>
              <td style={{ padding: '12px 16px', display: 'flex', gap: 8 }}>
                <button style={{
                  padding: '4px 10px', background: 'rgba(59,130,246,0.12)', border: '1px solid rgba(59,130,246,0.25)',
                  borderRadius: 6, color: ACCENT, fontSize: 11, fontWeight: 700, cursor: 'pointer', letterSpacing: '0.5px',
                }}>EDIT</button>
                <button style={{
                  padding: '4px 10px', background: 'rgba(239,68,68,0.1)', border: '1px solid rgba(239,68,68,0.2)',
                  borderRadius: 6, color: '#EF4444', fontSize: 11, fontWeight: 700, cursor: 'pointer', letterSpacing: '0.5px',
                }}>BAN</button>
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

  const display = (item) => {
    if (!item.sensitive || revealed) return item.value
    return '•'.repeat(Math.min(item.value?.length ?? 8, 16))
  }

  const VALUE_COLOR = (item) => {
    if (item.sensitive && !revealed) return 'rgba(255,255,255,0.25)'
    if (item.value === 'true' || item.value === 'production') return '#22C55E'
    if (item.value === 'false' || item.value === 'local') return '#f59e0b'
    return 'rgba(255,255,255,0.55)'
  }

  return (
    <div>
      <div style={{
        background: 'rgba(239,68,68,0.06)', border: '1px solid rgba(239,68,68,0.15)',
        borderRadius: 10, padding: '10px 16px', marginBottom: 16,
        fontSize: 12, color: '#fca5a5', display: 'flex', alignItems: 'center', gap: 8,
      }}>
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
                color:      revealed ? '#EF4444' : ACCENT,
                letterSpacing: '0.5px',
              }}>
              {revealed ? '🙈 Hide Secrets' : '👁 Reveal All'}
            </button>
          }
        >
          {loading ? <Spinner color={ACCENT} /> : (
            <table style={{ width: '100%', borderCollapse: 'collapse', fontFamily: 'monospace' }}>
              <thead>
                <tr style={{ borderBottom: '1px solid rgba(255,255,255,0.07)' }}>
                  {['KEY','VALUE','SAFE TO SHARE'].map(h => (
                    <th key={h} style={{
                      padding: '12px 16px', textAlign: 'left', fontSize: 14,
                      color: 'rgba(255,255,255,0.55)', fontWeight: 700, letterSpacing: '1px',
                    }}>{h}</th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {(data ?? []).map((item, i) => (
                  <tr key={i} style={{
                    borderBottom: '1px solid rgba(255,255,255,0.04)',
                    background: i % 2 ? 'rgba(255,255,255,0.02)' : 'transparent',
                    transition: 'background 0.15s',
                  }}
                    onMouseEnter={e => e.currentTarget.style.background = 'rgba(255,255,255,0.04)'}
                    onMouseLeave={e => e.currentTarget.style.background = i % 2 ? 'rgba(255,255,255,0.02)' : 'transparent'}
                  >
                    <td style={{ padding: '11px 16px', fontSize: 13, color: '#6366f1', fontWeight: 700, fontFamily: 'monospace' }}>{item.key}</td>
                    <td style={{ padding: '11px 16px', fontSize: 13, color: VALUE_COLOR(item), transition: 'color .2s', fontFamily: 'monospace' }}>
                      {display(item)}
                    </td>
                    <td style={{ padding: '11px 16px' }}>
                      <span style={{
                        display: 'inline-block', padding: '2px 8px', borderRadius: 20,
                        fontSize: 11, fontWeight: 600, letterSpacing: '0.5px',
                        background: item.sensitive ? 'rgba(239,68,68,0.12)' : 'rgba(34,197,94,0.12)',
                        color: item.sensitive ? '#EF4444' : '#22C55E',
                        border: `1px solid ${item.sensitive ? 'rgba(239,68,68,0.3)' : 'rgba(34,197,94,0.3)'}`,
                      }}>
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

// ── ACTIVITY TAB ───────────────────────────────────────────────────────────────
function ActivityTab() {
  const [actionFilter, setActionFilter] = useState('ALL')
  const [entityFilter, setEntityFilter] = useState('ALL')
  const { data: logs, loading, error, refetch } = useFetch('/api/dev/activity', { refreshInterval: 30000 })

  const actionGroups = ['ALL', 'login_success', 'login_failed', 'staff_invited', 'staff_role_changed', 'google_login', 'google_register', 'order_status_update', 'payment_verified']
  const entityGroups = ['ALL', 'User', 'Staff', 'Admin', 'Order', 'Product']

  const filtered = (logs ?? []).filter(l => {
    const actionOk = actionFilter === 'ALL' || l.action === actionFilter
    const entityOk = entityFilter === 'ALL' || l.entity_type === entityFilter
    return actionOk && entityOk
  })

  const ACTION_STYLE = {
    login_success:     { bg: 'rgba(34,197,94,0.15)',  color: '#22c55e' },
    login_failed:      { bg: 'rgba(239,68,68,0.15)',  color: '#ef4444' },
    staff_invited:     { bg: 'rgba(59,130,246,0.15)', color: '#3b82f6' },
    staff_role_changed:{ bg: 'rgba(168,85,247,0.15)', color: '#a855f7' },
    google_login:      { bg: 'rgba(99,102,241,0.15)', color: '#6366f1' },
    google_register:   { bg: 'rgba(99,102,241,0.15)', color: '#6366f1' },
    order_status_update:{ bg: 'rgba(245,158,11,0.15)',color: '#f59e0b' },
    payment_verified:  { bg: 'rgba(34,197,94,0.15)',  color: '#22c55e' },
    staff_deleted:     { bg: 'rgba(239,68,68,0.12)',  color: '#ef4444' },
  }

  const getActionStyle = (action) => ACTION_STYLE[action] ?? { bg: 'rgba(75,85,99,0.2)', color: '#6b7280' }

  return (
    <div>
      <div style={{ display: 'flex', gap: 8, marginBottom: 16, flexWrap: 'wrap', alignItems: 'center' }}>
        <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap' }}>
          {actionGroups.map(a => {
            const active = actionFilter === a
            const ms = getActionStyle(a)
            return (
              <button key={a} onClick={() => setActionFilter(a)} style={{
                padding: '5px 12px', borderRadius: 6, border: '1px solid', fontSize: 11, fontWeight: 800, cursor: 'pointer', fontFamily: 'monospace',
                borderColor: active ? (a === 'ALL' ? ACCENT : ms.color) : 'rgba(255,255,255,0.1)',
                background:  active ? (a === 'ALL' ? 'rgba(59,130,246,0.12)' : ms.bg) : 'transparent',
                color:       active ? (a === 'ALL' ? ACCENT : ms.color) : 'rgba(255,255,255,0.45)',
                transition: 'all 0.15s',
              }}>{a.replace(/_/g, ' ')}</button>
            )
          })}
        </div>
        <div style={{ width: 1, height: 20, background: 'rgba(255,255,255,0.07)' }} />
        <div style={{ display: 'flex', gap: 6 }}>
          {entityGroups.map(e => {
            const active = entityFilter === e
            return (
              <button key={e} onClick={() => setEntityFilter(e)} style={{
                padding: '5px 12px', borderRadius: 6, border: '1px solid', fontSize: 11, fontWeight: 700, cursor: 'pointer', fontFamily: 'monospace',
                borderColor: active ? ACCENT : 'rgba(255,255,255,0.1)',
                background:  active ? 'rgba(59,130,246,0.12)' : 'transparent',
                color:       active ? ACCENT : 'rgba(255,255,255,0.45)',
                transition: 'all 0.15s',
              }}>{e}</button>
            )
          })}
        </div>
        <button onClick={refetch} style={{
          marginLeft: 'auto', padding: '5px 14px', border: '1px solid rgba(255,255,255,0.1)', borderRadius: 6,
          background: 'transparent', color: 'rgba(255,255,255,0.45)', fontSize: 11, cursor: 'pointer',
        }}>↻ Refresh</button>
      </div>

      {error ? <ErrorState message={error} onRetry={refetch} /> : (
        <TableBox headers={['ACTION', 'ENTITY', 'ACTOR', 'DETAILS', 'AT']} mono>
          {loading ? <SkeletonRows cols={5} rows={8} color={ACCENT} /> : filtered.length === 0 ? (
            <tr><td colSpan={5}><EmptyState label="No activity logs" /></td></tr>
          ) : filtered.map((l, i) => {
            const ms = getActionStyle(l.action)
            return (
              <tr key={l.id ?? i} style={{
                borderBottom: '1px solid rgba(255,255,255,0.04)',
                background: i % 2 ? 'rgba(255,255,255,0.02)' : 'transparent',
                transition: 'background 0.15s',
              }}
                onMouseEnter={e => e.currentTarget.style.background = 'rgba(255,255,255,0.04)'}
                onMouseLeave={e => e.currentTarget.style.background = i % 2 ? 'rgba(255,255,255,0.02)' : 'transparent'}
              >
                <td style={{ padding: '10px 16px' }}>
                  <span style={{
                    background: ms.bg, color: ms.color, borderRadius: 4, padding: '2px 8px',
                    fontSize: 10, fontWeight: 800, fontFamily: 'monospace', whiteSpace: 'nowrap',
                  }}>{l.action?.replace(/_/g, ' ')}</span>
                </td>
                <td style={{ padding: '10px 16px', fontSize: 12, color: 'rgba(255,255,255,0.55)', whiteSpace: 'nowrap' }}>
                  {l.entity_type}{l.entity_id ? ` #${l.entity_id}` : ''}
                </td>
                <td style={{ padding: '10px 16px', fontSize: 12, color: 'rgba(255,255,255,0.7)', fontWeight: 600, maxWidth: 140, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                  {l.actor_name ?? l.actor_type ?? '—'}
                </td>
                <td style={{ padding: '10px 16px', fontSize: 11, color: 'rgba(255,255,255,0.35)', maxWidth: 200, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                  {l.details ? JSON.stringify(l.details) : '—'}
                </td>
                <td style={{ padding: '10px 16px', fontSize: 11, color: 'rgba(255,255,255,0.35)', fontFamily: 'monospace', whiteSpace: 'nowrap' }}>
                  {l.created_at?.slice(11, 19) ?? '—'}
                </td>
              </tr>
            )
          })}
        </TableBox>
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
    system:   <SystemTab />,
    logs:     <ApiLogsTab />,
    env:      <EnvTab />,
    activity: <ActivityTab />,
    users:    <UsersTab />,
  }

  const flatNav = NAV_SECTIONS.flatMap(s => s.items)
  const [sidebarOpen, setSidebarOpen] = useState(false)

  return (
    <div style={{ minHeight: '100vh', background: '#0A0A0A', display: 'flex', fontFamily: "'Rajdhani', sans-serif" }}>
      <style>{`
        @keyframes spin { to { transform: rotate(360deg) } }
        @keyframes shimmer { 0%,100%{opacity:.3} 50%{opacity:.75} }

        .dev-sidebar { width: 240px; }
        .dev-main    { margin-left: 0; flex: 1; }
        .dev-content { padding: 24px; }

        @media (max-width: 768px) {
          .dev-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; z-index: 9000;
            transform: translateX(-100%); transition: transform 0.3s ease;
          }
          .dev-sidebar.open { transform: translateX(0); }
          .dev-sidebar-overlay {
            display: none; position: fixed; inset: 0; z-index: 8999;
            background: rgba(0,0,0,0.6); backdrop-filter: blur(2px);
          }
          .dev-sidebar-overlay.active { display: block; }
          .dev-content { padding: 14px; }
          .dev-topbar  { padding-left: 14px !important; padding-right: 14px !important; }
          .dev-topbar-title { font-size: 17px !important; }
          .dev-hamburger { display: flex !important; }
        }

        @media (max-width: 480px) {
          .dev-content { padding: 10px; }
        }
      `}</style>

      {/* Mobile sidebar overlay */}
      <div className={`dev-sidebar-overlay${sidebarOpen ? ' active' : ''}`} onClick={() => setSidebarOpen(false)} />

      {/* ── Sidebar ─────────────────────────────────────────────────────── */}
      <div className={`dev-sidebar${sidebarOpen ? ' open' : ''}`} style={{
        width: 240, background: '#111111', borderRight: '1px solid rgba(255,255,255,0.07)',
        display: 'flex', flexDirection: 'column', flexShrink: 0,
      }}>
        {/* Logo */}
        <div style={{
          display: 'flex', alignItems: 'center', gap: 12,
          padding: 20, borderBottom: '1px solid rgba(255,255,255,0.07)', flexShrink: 0,
        }}>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="40" height="40">
            <defs>
              <linearGradient id="dvg" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style={{ stopColor: '#60A5FA' }}/>
                <stop offset="100%" style={{ stopColor: '#3B82F6' }}/>
              </linearGradient>
            </defs>
            <polygon points="50,4 90,26 90,74 50,96 10,74 10,26" fill="#1e1e1e" stroke="#3B82F6" strokeWidth="4"/>
            <polygon points="54,18 32,54 48,54 44,82 68,46 52,46" fill="url(#dvg)"/>
          </svg>
          <div>
            <div style={{ fontSize: 16, fontWeight: 700, letterSpacing: 2, color: '#fff' }}>TRONMATIX</div>
            <div style={{ fontSize: 11, fontWeight: 700, letterSpacing: 4, color: ACCENT }}>DEV PORTAL</div>
          </div>
        </div>

        {/* Nav */}
        <nav style={{ flex: 1, padding: '8px 0', overflowY: 'auto' }}>
          {NAV_SECTIONS.map(section => (
            <div key={section.label}>
              <div style={{
                fontSize: 16, color: 'rgba(255,255,255,0.35)', fontWeight: 700,
                padding: '8px 20px 5px', textTransform: 'uppercase', letterSpacing: 1,
                marginTop: 1,
              }}>
                {section.label}
              </div>
              {section.items.map(n => {
                const active = tab === n.id
                return (
                  <button key={n.id} onClick={() => { setTab(n.id); setSidebarOpen(false) }} style={{
                    width: '100%', display: 'flex', alignItems: 'center', gap: 12,
                    padding: '8px 20px', border: 'none', cursor: 'pointer',
                    background: active ? 'rgba(59,130,246,0.08)' : 'transparent',
                    color: active ? ACCENT : 'rgba(255,255,255,0.55)',
                    fontSize: 18, fontWeight: 600, fontFamily: "'Rajdhani', sans-serif",
                    borderLeft: active ? '3px solid #3B82F6' : '3px solid transparent',
                    transition: 'all 0.2s',
                  }}
                    onMouseEnter={e => { if (!active) { e.currentTarget.style.background = 'rgba(255,255,255,0.04)'; e.currentTarget.style.color = '#fff'; } }}
                    onMouseLeave={e => { if (!active) { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.color = 'rgba(255,255,255,0.55)'; } }}
                  >
                    <span style={{ fontSize: 16 }}>{n.icon}</span>
                    {n.label}
                  </button>
                )
              })}
            </div>
          ))}
        </nav>

        {/* User info & logout */}
        <div style={{
          padding: '14px 20px', borderTop: '1px solid rgba(255,255,255,0.07)',
          fontSize: 11, color: 'rgba(255,255,255,0.2)', flexShrink: 0,
        }}>
          <div style={{ fontSize: 14, fontWeight: 700, color: 'rgba(255,255,255,0.8)', fontFamily: 'monospace', marginBottom: 2 }}>
            {user?.name ?? user?.username ?? 'Developer'}
          </div>
          <div style={{ fontSize: 11, color: ACCENT, fontWeight: 700, letterSpacing: 1, marginBottom: 10, fontFamily: 'monospace' }}>
            {user?.email}
          </div>
          <button onClick={handleLogout} style={{
            width: '100%', padding: '7px 0', background: 'rgba(239,68,68,0.1)',
            border: '1px solid rgba(239,68,68,0.2)', borderRadius: 8,
            color: '#EF4444', fontSize: 13, fontWeight: 700, cursor: 'pointer',
            fontFamily: "'Rajdhani', sans-serif", letterSpacing: 1,
            transition: 'background 0.2s',
          }}
            onMouseEnter={e => e.currentTarget.style.background = 'rgba(239,68,68,0.2)'}
            onMouseLeave={e => e.currentTarget.style.background = 'rgba(239,68,68,0.1)'}
          >
            🚪 LOGOUT
          </button>
        </div>
      </div>

      {/* ── Main ─────────────────────────────────────────────────────────── */}
      <div style={{ flex: 1, display: 'flex', flexDirection: 'column', overflow: 'auto' }}>
        {/* Topbar */}
        <div style={{
          height: 60, background: '#111111', borderBottom: '1px solid rgba(255,255,255,0.07)',
          display: 'flex', alignItems: 'center', justifyContent: 'space-between',
          padding: '10px 24px 5px', position: 'sticky', top: 0, zIndex: 100,
        }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <button onClick={() => setSidebarOpen(true)} style={{
              display: 'none', background: 'none', border: '1px solid rgba(255,255,255,0.1)',
              borderRadius: 8, padding: 6, cursor: 'pointer', color: 'rgba(255,255,255,0.55)',
            }} className="dev-hamburger">☰</button>
            <div>
              <div style={{ fontSize: 22, fontWeight: 700, color: '#fff' }} className="dev-topbar-title">
                {flatNav.find(n => n.id === tab)?.label ?? 'Dashboard'}
              </div>
              <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.35)', fontFamily: 'monospace' }}>
                dev@tronmatix — {new Date().toISOString().slice(0, 19).replace('T', ' ')} UTC
              </div>
            </div>
          </div>
          <div style={{
            width: 36, height: 36, borderRadius: '50%', background: 'rgba(59,130,246,0.15)',
            border: `2px solid ${ACCENT}`, display: 'flex', alignItems: 'center',
            justifyContent: 'center', color: ACCENT, fontWeight: 800, fontSize: 16,
          }}>
            {(user?.name ?? user?.username ?? 'D')[0].toUpperCase()}
          </div>
        </div>

        {/* Content */}
        <div style={{ flex: 1, padding: 24 }}>
          {TAB_CONTENT[tab]}
        </div>
      </div>
    </div>
  )
}
