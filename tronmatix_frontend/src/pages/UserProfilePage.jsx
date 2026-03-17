// src/pages/UserProfilePage.jsx
import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { useTheme } from '../context/ThemeContext'
import axiosClient from '../lib/axios'

import Toast         from '../components/profile/Toast'
import ProfileHeader from '../components/profile/ProfileHeader'
import ProfileTab    from '../components/profile/ProfileTab'
import LocationsTab  from '../components/profile/LocationsTab'

const VIP_GOAL = 1000

export default function UserProfilePage() {
  const { user: authUser, refreshUser } = useAuth()
  const { dark } = useTheme()
  const navigate = useNavigate()

  const [tab,        setTab]        = useState('profile')
  const [toast,      setToast]      = useState({ msg: '', type: 'success' })
  const [totalSpent, setTotalSpent] = useState(null)

  // ── Fresh user data fetched directly from API ─────────────────────────────
  // This ensures username/email are ALWAYS populated even if AuthContext
  // user object was loaded from cache with missing fields.
  const [profileUser, setProfileUser] = useState(authUser)

  const notify = (msg, type = 'success') => setToast({ msg, type })

  // Redirect if not logged in
  useEffect(() => { if (!authUser) navigate('/') }, [authUser])

  // Fetch fresh profile from API on mount — fixes blank username/email
  useEffect(() => {
    if (!authUser) return
    axiosClient.get('/api/user/profile')
      .then(res => {
        const fresh = res.data?.data ?? res.data
        if (fresh && fresh.id) {
          setProfileUser(fresh)
        }
      })
      .catch(() => {
        // Fallback to auth user if profile API fails
        setProfileUser(authUser)
      })
  }, [authUser?.id])

  // Fetch spending stats
  useEffect(() => {
    if (!authUser) return
    axiosClient.get('/api/user/stats')
      .then(async res => {
        const stats = res.data?.data || {}
        setTotalSpent(stats.total_spent ?? 0)
        if (stats.role === 'vip' && (authUser.role === 'customer' || !authUser.role)) {
          await refreshUser?.()
        }
      })
      .catch(() => {
        axiosClient.get('/api/orders')
          .then(res => {
            const orders = Array.isArray(res.data?.data) ? res.data.data : []
            const spent = orders
              .filter(o => o.status !== 'cancelled')
              .reduce((sum, o) => sum + parseFloat(o.total || 0), 0)
            setTotalSpent(spent)
          })
          .catch(() => setTotalSpent(0))
      })
  }, [authUser?.id])

  const handleProfileSaved = async () => {
    const fresh = await refreshUser?.()
    if (fresh) setProfileUser(fresh)
    // Also re-fetch from /api/user/profile to ensure all fields up to date
    axiosClient.get('/api/user/profile')
      .then(res => {
        const data = res.data?.data ?? res.data
        if (data && data.id) setProfileUser(data)
      })
      .catch(() => {})
  }

  if (!authUser) return null

  // Use profileUser (fresh from API) with authUser as fallback
  const displayUser = profileUser || authUser

  const bg      = dark ? '#0f172a' : '#F5F5F5'
  const cardBg  = dark ? '#1f2937' : '#fff'
  const border  = dark ? '#374151' : '#f3f4f6'
  const tabText = dark ? '#9ca3af' : '#6B7280'

  return (
    <>
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&display=swap');
        @keyframes slideIn   { from { opacity:0; transform:translateX(20px) } to { opacity:1; transform:none } }
        @keyframes modalIn   { from { opacity:0; transform:scale(0.95) translateY(10px) } to { opacity:1; transform:none } }
        @keyframes fadeUp    { from { opacity:0; transform:translateY(16px) } to { opacity:1; transform:none } }
        @keyframes spin      { from { transform:rotate(0deg) } to { transform:rotate(360deg) } }
        @keyframes popIn     { 0% { transform:scale(0) } 60% { transform:scale(1.15) } 100% { transform:scale(1) } }
        @keyframes fieldSlide { from { opacity:0.6; transform:translateY(-4px) } to { opacity:1; transform:none } }
        .tab-btn:hover { color: #F97316 !important; }
        .loc-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08) !important; }
      `}</style>

      <Toast msg={toast.msg} type={toast.type} onDone={() => setToast({ msg: '', type: 'success' })} />

      <div style={{ minHeight: '100vh', background: bg, fontFamily: 'Rajdhani, sans-serif', paddingBottom: 60 }}>
        <ProfileHeader user={displayUser} totalSpent={totalSpent} VIP_GOAL={VIP_GOAL} />

        <div style={{ maxWidth: 720, margin: '15px auto 0', padding: '0 16px' }}>
          <div style={{
            background: cardBg,
            borderRadius: 20,
            boxShadow: dark ? '0 8px 40px rgba(0,0,0,0.4)' : '0 8px 40px rgba(0,0,0,0.1)',
            overflow: 'hidden'
          }}>
            {/* Tab bar */}
            <div style={{ display: 'flex', borderBottom: `1px solid ${border}` }}>
              {[
                { key: 'profile',   label: '👤 My Profile' },
                { key: 'locations', label: '📍 My Locations' },
              ].map(t => (
                <button key={t.key} className="tab-btn" onClick={() => setTab(t.key)} style={{
                  flex: 1, padding: '18px 0', border: 'none', cursor: 'pointer',
                  background: 'none', fontFamily: 'Rajdhani, sans-serif',
                  fontSize: 16, fontWeight: 700, letterSpacing: 1,
                  color: tab === t.key ? '#F97316' : tabText,
                  borderBottom: tab === t.key ? '2.5px solid #F97316' : '2.5px solid transparent',
                  transition: 'all 0.2s',
                }}>{t.label}</button>
              ))}
            </div>

            {tab === 'profile'   && (
              <ProfileTab
                user={displayUser}
                totalSpent={totalSpent}
                VIP_GOAL={VIP_GOAL}
                onSaved={handleProfileSaved}
                notify={notify}
              />
            )}
            {tab === 'locations' && <LocationsTab notify={notify} />}
          </div>
        </div>
      </div>
    </>
  )
}
