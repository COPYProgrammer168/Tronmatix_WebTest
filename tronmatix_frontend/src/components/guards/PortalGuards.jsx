/**
 * src/components/guards/PortalGuards.jsx
 *
 * Two guards for staff and developer protected routes.
 * Both use existing AuthContext — no new context needed.
 * Staff roles are fetched from the backend so new roles added in
 * dashboard settings are recognized automatically.
 */
import { Navigate } from 'react-router-dom'
import { useAuth } from '../../context/AuthContext'
import { useState, useEffect } from 'react'

// Fallback used while the API call is in flight or if it fails
const DEFAULT_STAFF_ROLES = ['editor', 'seller', 'delivery']

// ── Staff Guard ───────────────────────────────────────────────────────────────
export function StaffGuard({ children }) {
  const { user, ready } = useAuth()
  const [staffRoles, setStaffRoles] = useState<string[]>([])

  useEffect(() => {
    let cancelled = false
    fetch('/api/settings/roles')
      .then(r => r.json())
      .then(json => {
        if (cancelled) return
        const roles: string[] = (json?.data ?? [])
          .filter((r: any) => r.is_staff_portal && r.key !== 'superadmin')
          .map((r: any) => r.key)
        setStaffRoles(roles.length ? roles : DEFAULT_STAFF_ROLES)
      })
      .catch(() => setStaffRoles(DEFAULT_STAFF_ROLES))
    return () => { cancelled = true }
  }, [])

  if (!ready || !staffRoles.length) {
    return <PortalLoader color="#F97316" />
  }

  if (!user || !staffRoles.includes(user.role)) {
    return <Navigate to="/staff/login" replace />
  }

  return children
}

// ── Dev Guard ─────────────────────────────────────────────────────────────────
export function DevGuard({ children }) {
  const { user, ready } = useAuth()
  const [devRoles, setDevRoles] = useState<string[]>([])

  useEffect(() => {
    let cancelled = false
    fetch('/api/settings/roles')
      .then(r => r.json())
      .then(json => {
        if (cancelled) return
        const roles: string[] = (json?.data ?? [])
          .filter((r: any) => r.key === 'developer')
          .map((r: any) => r.key)
        setDevRoles(roles.length ? roles : ['developer'])
      })
      .catch(() => setDevRoles(['developer']))
    return () => { cancelled = true }
  }, [])

  if (!ready || !devRoles.length) {
    return <PortalLoader color="#3b82f6" />
  }

  if (!user || !devRoles.includes(user.role)) {
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