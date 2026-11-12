/**
 * src/components/dashboard/StateViews.jsx
 *
 * Shared loading / error / empty state components
 * used by both StaffDashboard and DevDashboard.
 */

// ── Loading skeleton ──────────────────────────────────────────────────────────
export function LoadingRows({ cols = 5, rows = 4, color = '#F97316' }) {
  return (
    <div style={{ padding: '8px 0' }}>
      <style>{`
        @keyframes shimmer {
          0%   { opacity: 0.4 }
          50%  { opacity: 0.9 }
          100% { opacity: 0.4 }
        }
      `}</style>
      {Array.from({ length: rows }).map((_, ri) => (
        <div key={ri} style={{ display: 'flex', gap: 12, padding: '14px 16px', borderBottom: '1px solid #1f2937' }}>
          {Array.from({ length: cols }).map((_, ci) => (
            <div key={ci} style={{
              flex: ci === 1 ? 2 : 1,
              height: 12,
              borderRadius: 6,
              background: `${color}22`,
              animation: `shimmer 1.4s ease-in-out ${ci * 0.1}s infinite`,
            }} />
          ))}
        </div>
      ))}
    </div>
  )
}

// ── Error state ───────────────────────────────────────────────────────────────
export function ErrorState({ message, onRetry }) {
  return (
    <div style={{ padding: '40px 20px', textAlign: 'center' }}>
      <div style={{ fontSize: 32, marginBottom: 12 }}>⚠️</div>
      <div style={{ fontSize: 14, color: '#ef4444', fontWeight: 700, marginBottom: 6 }}>
        Failed to load data
      </div>
      <div style={{ fontSize: 12, color: '#6b7280', marginBottom: 20, maxWidth: 300, margin: '0 auto 20px' }}>
        {message}
      </div>
      {onRetry && (
        <button onClick={onRetry} style={{
          padding: '8px 20px',
          background: 'rgba(239,68,68,0.12)',
          border: '1px solid rgba(239,68,68,0.3)',
          borderRadius: 8, color: '#ef4444',
          fontSize: 13, fontWeight: 700, cursor: 'pointer',
        }}>
          Try Again
        </button>
      )}
    </div>
  )
}

// ── Empty state ───────────────────────────────────────────────────────────────
export function EmptyState({ label = 'No data found', color = '#4b5563' }) {
  return (
    <div style={{ padding: '48px 20px', textAlign: 'center' }}>
      <div style={{ fontSize: 32, marginBottom: 12, opacity: 0.4 }}>📭</div>
      <div style={{ fontSize: 13, color, fontWeight: 600 }}>{label}</div>
    </div>
  )
}

// ── Stat card skeleton ────────────────────────────────────────────────────────
export function StatSkeleton({ color = '#F97316' }) {
  return (
    <div style={{ background: '#111827', border: '1px solid #1f2937', borderRadius: 12, padding: '20px 22px', position: 'relative', overflow: 'hidden' }}>
      <div style={{ position: 'absolute', top: 0, left: 0, width: 3, height: '100%', background: `${color}44`, borderRadius: '3px 0 0 3px' }} />
      <div style={{ height: 11, width: '60%', background: `${color}22`, borderRadius: 5, marginBottom: 10, animation: 'shimmer 1.4s ease-in-out infinite' }} />
      <div style={{ height: 22, width: '80%', background: `${color}22`, borderRadius: 5, marginBottom: 8, animation: 'shimmer 1.4s ease-in-out 0.2s infinite' }} />
      <div style={{ height: 10, width: '40%', background: `${color}15`, borderRadius: 5, animation: 'shimmer 1.4s ease-in-out 0.4s infinite' }} />
    </div>
  )
}