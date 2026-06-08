/**
 * src/pages/StaffDashboard.jsx
 *
 * Staff Dashboard — connects to real Laravel API.
 * Every tab fetches its own data with loading / error / empty states.
 *
 * Endpoints used (all existing in api.php):
 *   GET /api/orders              → OrdersTab
 *   GET /api/products            → ProductsTab
 *   GET /api/delivery-schedules  → DeliveryTab
 *
 * New endpoints needed (add to api.php):
 *   GET /api/admin/stats         → OverviewTab
 *   GET /api/admin/users         → UsersTab
 */
import { useState, useEffect, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import api from '../lib/axios'

// ── Constants ─────────────────────────────────────────────────────────────────
const NAV = [
  { id: 'overview',  label: 'Overview',  icon: '▦'  },
  { id: 'orders',    label: 'Orders',    icon: '📦' },
  { id: 'products',  label: 'Products',  icon: '🖥️' },
  { id: 'users',     label: 'Users',     icon: '👥' },
  { id: 'delivery',  label: 'Delivery',  icon: '🚚' },
]

const STATUS_COLORS = {
  pending:    { bg: 'rgba(245,158,11,0.15)',  color: '#f59e0b' },
  processing: { bg: 'rgba(59,130,246,0.15)',  color: '#3b82f6' },
  delivered:  { bg: 'rgba(34,197,94,0.15)',   color: '#22c55e' },
  cancelled:  { bg: 'rgba(239,68,68,0.15)',   color: '#ef4444' },
  scheduled:  { bg: 'rgba(249,115,22,0.15)',  color: '#F97316' },
  en_route:   { bg: 'rgba(59,130,246,0.15)',  color: '#3b82f6' },
}

const ROLE_COLORS = {
  customer:   { bg: 'rgba(156,163,175,0.15)', color: '#9ca3af' },
  admin:      { bg: 'rgba(249,115,22,0.15)',  color: '#F97316' },
  staff:      { bg: 'rgba(59,130,246,0.15)',  color: '#3b82f6' },
  superadmin: { bg: 'rgba(168,85,247,0.15)',  color: '#a855f7' },
  delivery:   { bg: 'rgba(34,197,94,0.15)',   color: '#22c55e' },
}

// ── Inline state components ──────────────────────────────────────────────────
function Spinner({ color = '#F97316' }) {
  return (
    <div style={{ padding: '48px', display: 'flex', justifyContent: 'center' }}>
      <div style={{ width: 28, height: 28, border: `3px solid ${color}33`, borderTopColor: color, borderRadius: '50%', animation: 'spin .7s linear infinite' }} />
    </div>
  )
}

function ErrorState({ message, onRetry }) {
  return (
    <div style={{ padding: '40px', textAlign: 'center' }}>
      <div style={{ fontSize: 13, color: '#ef4444', fontWeight: 700, marginBottom: 8 }}>Failed to load</div>
      <div style={{ fontSize: 12, color: '#6b7280', marginBottom: 16 }}>{message}</div>
      {onRetry && (
        <button onClick={onRetry} style={{ padding: '7px 18px', background: 'rgba(239,68,68,0.12)', border: '1px solid rgba(239,68,68,0.3)', borderRadius: 7, color: '#ef4444', fontSize: 12, fontWeight: 700, cursor: 'pointer' }}>
          ↻ Retry
        </button>
      )}
    </div>
  )
}

function EmptyState({ label = 'No data found' }) {
  return (
    <div style={{ padding: '48px', textAlign: 'center', fontSize: 13, color: '#4b5563', fontWeight: 600 }}>
      {label}
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
              <div style={{ height: 11, borderRadius: 5, background: 'rgba(249,115,22,0.1)', animation: `shimmer 1.4s ${ci * 0.1}s ease-in-out infinite` }} />
            </td>
          ))}
        </tr>
      ))}
    </>
  )
}

// ── useFetch hook ─────────────────────────────────────────────────────────────
function useFetch(endpoint) {
  const [data,    setData]    = useState(null)
  const [loading, setLoading] = useState(true)
  const [error,   setError]   = useState(null)

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
  const s = map[status] || { bg: 'rgba(75,85,99,0.2)', color: '#9ca3af' }
  return (
    <span style={{ background: s.bg, color: s.color, borderRadius: 6, padding: '3px 10px', fontSize: 12, fontWeight: 700, letterSpacing: '0.05em', whiteSpace: 'nowrap' }}>
      {status?.replace(/_/g, ' ').toUpperCase()}
    </span>
  )
}

// ── Table wrapper ─────────────────────────────────────────────────────────────
function TableBox({ headers, children }) {
  return (
    <div style={{ background: '#111827', border: '1px solid #1f2937', borderRadius: 12, overflow: 'hidden' }}>
      <table style={{ width: '100%', borderCollapse: 'collapse' }}>
        <thead>
          <tr style={{ borderBottom: '1px solid #1f2937' }}>
            {headers.map(h => (
              <th key={h} style={{ padding: '12px 16px', textAlign: 'left', fontSize: 11, color: '#4b5563', fontWeight: 700, letterSpacing: '0.08em' }}>{h}</th>
            ))}
          </tr>
        </thead>
        <tbody>{children}</tbody>
      </table>
    </div>
  )
}

// ── TABS ──────────────────────────────────────────────────────────────────────

function OverviewTab() {
  const { data: stats, loading, error, refetch } = useFetch('/api/admin/stats')

  const STAT_DEFS = [
    { key: 'total_orders',   label: 'Total Orders',   deltaKey: 'orders_delta',  color: '#F97316' },
    { key: 'revenue',        label: 'Revenue',        deltaKey: 'revenue_delta', color: '#22c55e' },
    { key: 'active_users',   label: 'Active Users',   deltaKey: 'users_delta',   color: '#3b82f6' },
    { key: 'pending_orders', label: 'Pending Orders', deltaKey: 'pending_delta', color: '#f59e0b' },
  ]

  return (
    <div>
      {/* Stat cards */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(200px,1fr))', gap: 16, marginBottom: 28 }}>
        {error ? (
          <div style={{ gridColumn: '1/-1' }}><ErrorState message={error} onRetry={refetch} /></div>
        ) : STAT_DEFS.map(s => (
          <div key={s.key} style={{ background: '#111827', border: '1px solid #1f2937', borderRadius: 12, padding: '20px 22px', position: 'relative', overflow: 'hidden' }}>
            <div style={{ position: 'absolute', top: 0, left: 0, width: 3, height: '100%', background: s.color, borderRadius: 0 }} />
            <div style={{ fontSize: 13, color: '#6b7280', marginBottom: 6 }}>{s.label}</div>
            {loading ? (
              <div style={{ height: 26, width: '70%', background: `${s.color}22`, borderRadius: 5, animation: 'shimmer 1.4s ease-in-out infinite' }} />
            ) : (
              <>
                <div style={{ fontSize: 26, fontWeight: 800, color: '#f9fafb', fontFamily: 'Rajdhani,sans-serif' }}>
                  {stats?.[s.key] ?? '—'}
                </div>
                {stats?.[s.deltaKey] && (
                  <div style={{ fontSize: 12, color: s.color, marginTop: 4, fontWeight: 600 }}>
                    {stats[s.deltaKey]} vs last month
                  </div>
                )}
              </>
            )}
          </div>
        ))}
      </div>

      {/* Bar chart */}
      <div style={{ background: '#111827', border: '1px solid #1f2937', borderRadius: 12, padding: '22px 24px' }}>
        <div style={{ fontSize: 14, fontWeight: 700, color: '#f9fafb', marginBottom: 20 }}>Weekly Orders</div>
        {loading ? (
          <Spinner />
        ) : !stats?.weekly_orders?.length ? (
          <EmptyState label="No weekly data" />
        ) : (
          <div style={{ display: 'flex', alignItems: 'flex-end', gap: 10, height: 120 }}>
            {stats.weekly_orders.map((item, i) => {
              const max = Math.max(...stats.weekly_orders.map(d => d.count), 1)
              const h = Math.round((item.count / max) * 110)
              return (
                <div key={i} style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6 }}>
                  <div title={`${item.count} orders`} style={{ width: '100%', height: Math.max(h, 4), background: '#F97316', borderRadius: '4px 4px 0 0' }} />
                  <span style={{ fontSize: 11, color: '#4b5563' }}>{item.day}</span>
                </div>
              )
            })}
          </div>
        )}
      </div>
    </div>
  )
}

function OrdersTab() {
  const [filter, setFilter] = useState('all')
  const { data: orders, loading, error, refetch } = useFetch('/api/orders')

  const statuses = ['all', 'pending', 'processing', 'delivered', 'cancelled']
  const filtered = !orders ? []
    : filter === 'all' ? orders
    : orders.filter(o => o.status === filter)

  return (
    <div>
      <div style={{ display: 'flex', gap: 8, marginBottom: 18, flexWrap: 'wrap', alignItems: 'center' }}>
        {statuses.map(s => (
          <button key={s} onClick={() => setFilter(s)} style={{
            padding: '6px 16px', borderRadius: 8, border: '1px solid', fontSize: 12, fontWeight: 700, cursor: 'pointer',
            borderColor: filter === s ? '#F97316' : '#1f2937',
            background:  filter === s ? 'rgba(249,115,22,0.15)' : 'transparent',
            color:       filter === s ? '#F97316' : '#6b7280',
          }}>
            {s.toUpperCase()}
          </button>
        ))}
        <button onClick={refetch} style={{ marginLeft: 'auto', padding: '6px 14px', borderRadius: 8, border: '1px solid #1f2937', background: 'transparent', color: '#4b5563', fontSize: 12, cursor: 'pointer' }}>
          ↻ Refresh
        </button>
      </div>

      {error ? (
        <ErrorState message={error} onRetry={refetch} />
      ) : (
        <TableBox headers={['ORDER ID','CUSTOMER','PRODUCT','TOTAL','STATUS','DATE']}>
          {loading ? <SkeletonRows cols={6} rows={5} /> : filtered.length === 0 ? (
            <tr><td colSpan={6}><EmptyState label={`No ${filter === 'all' ? '' : filter + ' '}orders`} /></td></tr>
          ) : filtered.map((o, i) => (
            <tr key={o.id} style={{ borderBottom: '1px solid #1f2937', background: i % 2 ? 'rgba(255,255,255,0.01)' : 'transparent' }}>
              <td style={{ padding: '13px 16px', fontSize: 13, color: '#F97316', fontWeight: 700 }}>#{o.id}</td>
              <td style={{ padding: '13px 16px', fontSize: 13, color: '#f9fafb' }}>{o.user?.name ?? o.user?.username ?? '—'}</td>
              <td style={{ padding: '13px 16px', fontSize: 13, color: '#9ca3af', maxWidth: 180, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                {o.items?.[0]?.product?.name ?? o.note ?? '—'}
              </td>
              <td style={{ padding: '13px 16px', fontSize: 13, color: '#22c55e', fontWeight: 700 }}>
                ${Number(o.total_price ?? o.total ?? 0).toLocaleString()}
              </td>
              <td style={{ padding: '13px 16px' }}><Badge status={o.status} map={STATUS_COLORS} /></td>
              <td style={{ padding: '13px 16px', fontSize: 12, color: '#4b5563' }}>{o.created_at?.slice(0, 10) ?? '—'}</td>
            </tr>
          ))}
        </TableBox>
      )}
    </div>
  )
}

function ProductsTab() {
  const [search, setSearch] = useState('')
  const { data: products, loading, error, refetch } = useFetch('/api/products')

  const filtered = !products ? []
    : products.filter(p => p.name?.toLowerCase().includes(search.toLowerCase()))

  return (
    <div>
      <div style={{ display: 'flex', gap: 12, marginBottom: 18 }}>
        <input value={search} onChange={e => setSearch(e.target.value)} placeholder="Search products..."
          style={{ flex: 1, padding: '9px 14px', background: '#111827', border: '1px solid #1f2937', borderRadius: 8, color: '#f9fafb', fontSize: 13, outline: 'none' }}
        />
        <button onClick={refetch} style={{ padding: '9px 14px', background: 'transparent', border: '1px solid #1f2937', borderRadius: 8, color: '#4b5563', fontSize: 13, cursor: 'pointer' }}>↻</button>
        <button style={{ padding: '9px 18px', background: '#F97316', border: 'none', borderRadius: 8, color: '#fff', fontWeight: 700, fontSize: 13, cursor: 'pointer' }}>+ Add</button>
      </div>

      {error ? (
        <ErrorState message={error} onRetry={refetch} />
      ) : (
        <TableBox headers={['SKU','NAME','CATEGORY','PRICE','STOCK','ACTIONS']}>
          {loading ? <SkeletonRows cols={6} rows={4} /> : filtered.length === 0 ? (
            <tr><td colSpan={6}><EmptyState label="No products found" /></td></tr>
          ) : filtered.map((p, i) => (
            <tr key={p.id} style={{ borderBottom: '1px solid #1f2937', background: i % 2 ? 'rgba(255,255,255,0.01)' : 'transparent' }}>
              <td style={{ padding: '13px 16px', fontSize: 12, color: '#4b5563', fontFamily: 'monospace' }}>{p.sku ?? '—'}</td>
              <td style={{ padding: '13px 16px', fontSize: 13, color: '#f9fafb', fontWeight: 600 }}>{p.name}</td>
              <td style={{ padding: '13px 16px', fontSize: 13, color: '#9ca3af' }}>{p.category?.name ?? p.category ?? '—'}</td>
              <td style={{ padding: '13px 16px', fontSize: 13, color: '#22c55e', fontWeight: 700 }}>${Number(p.price ?? 0).toLocaleString()}</td>
              <td style={{ padding: '13px 16px' }}>
                <span style={{ color: (p.stock ?? 0) <= 5 ? '#ef4444' : '#22c55e', fontWeight: 700, fontSize: 13 }}>{p.stock ?? 0}</span>
                <span style={{ color: '#4b5563', fontSize: 12 }}> units</span>
              </td>
              <td style={{ padding: '13px 16px', display: 'flex', gap: 8 }}>
                <button style={{ padding: '4px 12px', background: 'rgba(59,130,246,0.15)', border: '1px solid rgba(59,130,246,0.3)', borderRadius: 6, color: '#3b82f6', fontSize: 12, fontWeight: 700, cursor: 'pointer' }}>Edit</button>
                <button style={{ padding: '4px 12px', background: 'rgba(239,68,68,0.1)', border: '1px solid rgba(239,68,68,0.2)', borderRadius: 6, color: '#ef4444', fontSize: 12, fontWeight: 700, cursor: 'pointer' }}>Del</button>
              </td>
            </tr>
          ))}
        </TableBox>
      )}
    </div>
  )
}

function UsersTab() {
  const { data: users, loading, error, refetch } = useFetch('/api/admin/users')

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
        <span style={{ fontSize: 13, color: '#6b7280' }}>
          {!loading && users ? `${users.length} users total` : ''}
        </span>
        <button onClick={refetch} style={{ padding: '6px 14px', borderRadius: 7, border: '1px solid #1f2937', background: 'transparent', color: '#4b5563', fontSize: 12, cursor: 'pointer' }}>↻ Refresh</button>
      </div>

      {error ? (
        <ErrorState message={error} onRetry={refetch} />
      ) : (
        <TableBox headers={['#','NAME','EMAIL','ROLE','ORDERS','JOINED','ACTIONS']}>
          {loading ? <SkeletonRows cols={7} rows={5} /> : !users?.length ? (
            <tr><td colSpan={7}><EmptyState label="No users found" /></td></tr>
          ) : users.map((u, i) => (
            <tr key={u.id} style={{ borderBottom: '1px solid #1f2937', background: i % 2 ? 'rgba(255,255,255,0.01)' : 'transparent' }}>
              <td style={{ padding: '13px 16px', fontSize: 12, color: '#4b5563' }}>{u.id}</td>
              <td style={{ padding: '13px 16px', fontSize: 13, color: '#f9fafb', fontWeight: 600 }}>{u.name ?? u.username}</td>
              <td style={{ padding: '13px 16px', fontSize: 13, color: '#9ca3af' }}>{u.email}</td>
              <td style={{ padding: '13px 16px' }}><Badge status={u.role} map={ROLE_COLORS} /></td>
              <td style={{ padding: '13px 16px', fontSize: 13, color: '#f9fafb' }}>{u.orders_count ?? 0}</td>
              <td style={{ padding: '13px 16px', fontSize: 12, color: '#4b5563' }}>{u.created_at?.slice(0, 7) ?? '—'}</td>
              <td style={{ padding: '13px 16px' }}>
                <button style={{ padding: '4px 12px', background: 'rgba(249,115,22,0.12)', border: '1px solid rgba(249,115,22,0.25)', borderRadius: 6, color: '#F97316', fontSize: 12, fontWeight: 700, cursor: 'pointer' }}>View</button>
              </td>
            </tr>
          ))}
        </TableBox>
      )}
    </div>
  )
}

function DeliveryTab() {
  const { data: deliveries, loading, error, refetch } = useFetch('/api/delivery-schedules')

  const counts = (deliveries ?? []).reduce((acc, d) => {
    acc[d.status] = (acc[d.status] || 0) + 1
    return acc
  }, {})

  return (
    <div>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit,minmax(180px,1fr))', gap: 14, marginBottom: 22 }}>
        {[['Scheduled','scheduled','#F97316'],['En Route','en_route','#3b82f6'],['Delivered','delivered','#22c55e']].map(([label, key, color]) => (
          <div key={key} style={{ background: '#111827', border: `1px solid ${color}33`, borderRadius: 10, padding: '16px 20px' }}>
            <div style={{ fontSize: 28, fontWeight: 800, color, fontFamily: 'Rajdhani,sans-serif' }}>
              {loading ? '—' : (counts[key] ?? 0)}
            </div>
            <div style={{ fontSize: 13, color: '#6b7280', marginTop: 2 }}>{label}</div>
          </div>
        ))}
      </div>

      {error ? (
        <ErrorState message={error} onRetry={refetch} />
      ) : (
        <TableBox headers={['ORDER','CUSTOMER','AREA','DRIVER','TIME','STATUS']}>
          {loading ? <SkeletonRows cols={6} rows={3} /> : !deliveries?.length ? (
            <tr><td colSpan={6}><EmptyState label="No deliveries scheduled today" /></td></tr>
          ) : deliveries.map((d, i) => (
            <tr key={d.id} style={{ borderBottom: '1px solid #1f2937', background: i % 2 ? 'rgba(255,255,255,0.01)' : 'transparent' }}>
              <td style={{ padding: '13px 16px', fontSize: 13, color: '#F97316', fontWeight: 700 }}>#{d.order_id ?? d.id}</td>
              <td style={{ padding: '13px 16px', fontSize: 13, color: '#f9fafb' }}>{d.order?.user?.name ?? d.customer_name ?? '—'}</td>
              <td style={{ padding: '13px 16px', fontSize: 13, color: '#9ca3af' }}>{d.area ?? d.address ?? '—'}</td>
              <td style={{ padding: '13px 16px', fontSize: 13, color: '#f9fafb' }}>{d.driver_name ?? '—'}</td>
              <td style={{ padding: '13px 16px', fontSize: 13, color: '#9ca3af' }}>{d.scheduled_time ?? d.time ?? '—'}</td>
              <td style={{ padding: '13px 16px' }}><Badge status={d.status} map={STATUS_COLORS} /></td>
            </tr>
          ))}
        </TableBox>
      )}
    </div>
  )
}

// ── Main ──────────────────────────────────────────────────────────────────────
export default function StaffDashboard() {
  const [tab, setTab] = useState('overview')
  const { user, logout } = useAuth()
  const navigate = useNavigate()

  useEffect(() => {
    console.log('StaffDashboard: user state', user);
  }, [user]);

  const handleLogout = async () => {
    await logout()
    navigate('/staff/login', { replace: true })
  }

  const TAB_CONTENT = {
    overview: <OverviewTab />,
    orders:   <OrdersTab />,
    products: <ProductsTab />,
    users:    <UsersTab />,
    delivery: <DeliveryTab />,
  }

  return (
    <div style={{ minHeight: '100vh', background: '#0a0f1a', display: 'flex', fontFamily: 'Rajdhani,sans-serif' }}>
      <style>{`@keyframes spin{to{transform:rotate(360deg)}}@keyframes shimmer{0%,100%{opacity:.35}50%{opacity:.8}}`}</style>

      {/* Sidebar */}
      <div style={{ width: 220, background: '#0d1117', borderRight: '1px solid #1e2a3a', display: 'flex', flexDirection: 'column', flexShrink: 0 }}>
        <div style={{ padding: '24px 20px 20px', borderBottom: '1px solid #1e2a3a' }}>
          <div style={{ fontSize: 22, fontWeight: 900, color: '#f9fafb', letterSpacing: 1 }}>Tron<span style={{ color: '#F97316' }}>matix</span></div>
          <div style={{ fontSize: 11, color: '#374151', fontWeight: 700, letterSpacing: '0.12em', marginTop: 2 }}>STAFF PORTAL</div>
        </div>
        <nav style={{ flex: 1, padding: '12px 10px' }}>
          {NAV.map(n => (
            <button key={n.id} onClick={() => setTab(n.id)} style={{
              width: '100%', display: 'flex', alignItems: 'center', gap: 10, padding: '10px 12px',
              borderRadius: 8, border: 'none', cursor: 'pointer', marginBottom: 4, textAlign: 'left',
              fontFamily: 'Rajdhani,sans-serif', fontWeight: 700, fontSize: 14, letterSpacing: '0.05em',
              background: tab === n.id ? 'rgba(249,115,22,0.12)' : 'transparent',
              color:      tab === n.id ? '#F97316' : '#6b7280',
              borderLeft: tab === n.id ? '2px solid #F97316' : '2px solid transparent',
            }}>
              <span style={{ fontSize: 16 }}>{n.icon}</span>{n.label}
            </button>
          ))}
        </nav>
        <div style={{ padding: '16px 14px', borderTop: '1px solid #1e2a3a' }}>
          <div style={{ fontSize: 13, fontWeight: 700, color: '#f9fafb', marginBottom: 2 }}>{user?.name ?? user?.username ?? 'Staff'}</div>
          <div style={{ fontSize: 11, color: '#374151', marginBottom: 12 }}>{user?.role?.toUpperCase()}</div>
          <button onClick={handleLogout} style={{ width: '100%', padding: '8px 0', background: 'rgba(239,68,68,0.1)', border: '1px solid rgba(239,68,68,0.2)', borderRadius: 7, color: '#ef4444', fontSize: 13, fontWeight: 700, cursor: 'pointer', fontFamily: 'Rajdhani,sans-serif' }}>
            🚪 Logout
          </button>
        </div>
      </div>

      {/* Main */}
      <div style={{ flex: 1, display: 'flex', flexDirection: 'column', overflow: 'auto' }}>
        <div style={{ padding: '18px 28px', borderBottom: '1px solid #1e2a3a', display: 'flex', alignItems: 'center', justifyContent: 'space-between', background: '#0d1117' }}>
          <div>
            <div style={{ fontSize: 22, fontWeight: 800, color: '#f9fafb' }}>{NAV.find(n => n.id === tab)?.label}</div>
            <div style={{ fontSize: 12, color: '#374151' }}>Tronmatix Admin — {new Date().toLocaleDateString('en-GB', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</div>
          </div>
          <div style={{ width: 36, height: 36, borderRadius: '50%', background: 'rgba(249,115,22,0.15)', border: '2px solid #F97316', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#F97316', fontWeight: 800, fontSize: 16 }}>
            {(user?.name ?? user?.username ?? 'S')[0].toUpperCase()}
          </div>
        </div>
        <div style={{ flex: 1, padding: '24px 28px' }}>{TAB_CONTENT[tab]}</div>
      </div>
    </div>
  )
}