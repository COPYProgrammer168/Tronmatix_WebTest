/**
 * src/components/guards/PortalGuards.jsx
 *
 * Two guards for staff and developer protected routes.
 * Both use existing AuthContext — no new context needed.
 */
import { Navigate } from 'react-router-dom'
import { useAuth } from '../../context/AuthContext'

const STAFF_ROLES = ['admin', 'superadmin', 'staff', 'delivery']

// ── Staff Guard ───────────────────────────────────────────────────────────────
export function StaffGuard({ children }) {
  const { user, ready } = useAuth()

  if (!ready) return <PortalLoader color="#F97316" />

  if (!user || !STAFF_ROLES.includes(user.role)) {
    return <Navigate to="/staff/login" replace />
  }

  return children
}

// ── Dev Guard ─────────────────────────────────────────────────────────────────
export function DevGuard({ children }) {
  const { user, ready } = useAuth()

  if (!ready) return <PortalLoader color="#3b82f6" />

  if (!user || user.role !== 'developer') {
    return <Navigate to="/dev/login" replace />
  }

  return children
}

// ── Shared loading spinner ────────────────────────────────────────────────────
function PortalLoader({ color }) {
  return (
    <div style={{ minHeight: '100vh', display: 'flex', alignItems: 'center', justifyContent: 'center', background: '#0a0f1a' }}>
      <div style={{
        width: 36, height: 36, borderRadius: '50%',
        border: `3px solid ${color}33`,
        borderTopColor: color,
        animation: 'spin 0.7s linear infinite',
      }} />
      <style>{`@keyframes spin{to{transform:rotate(360deg)}}`}</style>
    </div>
  )
}