// src/pages/UserProfilePage.jsx
import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { useTheme } from '../context/ThemeContext'
import { useLang } from '../context/LanguageContext'
import axiosClient from '../lib/axios'

import Toast         from '../components/profile/Toast'
import ProfileHeader from '../components/profile/ProfileHeader'
import ProfileTab    from '../components/profile/ProfileTab'
import LocationsTab  from '../components/profile/LocationsTab'

const VIP_GOAL = 1000

export default function UserProfilePage() {
  const { user: authUser, refreshUser } = useAuth()
  const { dark } = useTheme()
  const { t }   = useLang()
  const navigate = useNavigate()

  const [tab,        setTab]        = useState('profile')
  const [toast,      setToast]      = useState({ msg: '', type: 'success' })
  const [totalSpent, setTotalSpent] = useState(null)
  const [profileUser, setProfileUser] = useState(authUser)

  const notify = (msg, type = 'success') => setToast({ msg, type })

  useEffect(() => { if (!authUser) navigate('/') }, [authUser])

  useEffect(() => {
    if (!authUser) return
    axiosClient.get('/api/user/profile')
      .then(res => {
        const fresh = res.data?.data ?? res.data
        if (fresh && fresh.id) setProfileUser(fresh)
      })
      .catch(() => setProfileUser(authUser))
  }, [authUser?.id])

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

  const handleProfileSaved = async (updatedFields) => {
    if (updatedFields) setProfileUser(prev => ({ ...prev, ...updatedFields }))
    const fresh = await refreshUser?.()
    if (fresh) setProfileUser(fresh)
    axiosClient.get('/api/user/profile')
      .then(res => {
        const data = res.data?.data ?? res.data
        if (data && data.id) setProfileUser(data)
      })
      .catch(() => {})
  }

  if (!authUser) return null

  const displayUser = profileUser || authUser

  const bg      = dark
    ? '#07090f'
    : '#f0f4ff'
  const cardBg  = dark ? 'rgba(31,41,55,0.85)' : 'rgba(255,255,255,0.90)'
  const border  = dark ? 'rgba(55,65,81,0.7)' : 'rgba(229,231,235,0.8)'
  const tabText = dark ? '#9ca3af' : '#6B7280'

  const TABS = [
    { key: 'profile',   label: t('profile.myProfile') },
    { key: 'locations', label: t('profile.myLocations') },
  ]

  return (
    <>
      <style>{`
        @keyframes slideIn    { from { opacity:0; transform:translateX(20px) } to { opacity:1; transform:none } }
        @keyframes modalIn    { from { opacity:0; transform:scale(0.95) translateY(10px) } to { opacity:1; transform:none } }
        @keyframes fadeUp     { from { opacity:0; transform:translateY(16px) } to { opacity:1; transform:none } }
        @keyframes spin       { from { transform:rotate(0deg) } to { transform:rotate(360deg) } }
        @keyframes popIn      { 0% { transform:scale(0) } 60% { transform:scale(1.15) } 100% { transform:scale(1) } }
        @keyframes fieldSlide { from { opacity:0.6; transform:translateY(-4px) } to { opacity:1; transform:none } }
        @keyframes floatOrb1  { 0%,100% { transform:translate(0,0) scale(1) } 50% { transform:translate(40px,-30px) scale(1.12) } }
        @keyframes floatOrb2  { 0%,100% { transform:translate(0,0) scale(1) } 50% { transform:translate(-30px,25px) scale(1.08) } }
        @keyframes floatOrb3  { 0%,100% { transform:translate(0,0) scale(1) } 50% { transform:translate(20px,40px) scale(1.1) } }
        .tab-btn:hover { color: #F97316 !important; }
        .loc-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08) !important; }
      `}</style>

      <Toast msg={toast.msg} type={toast.type} onDone={() => setToast({ msg: '', type: 'success' })} />

      <div style={{ minHeight: '100vh', background: bg, fontFamily: 'Kdam Thmor Pro, Rajdhani, sans-serif', paddingBottom: 60, position: 'relative', overflow: 'hidden' }}>

        {/* ── Texture: Aurora Mesh Gradient orbs ── */}
        <div style={{ position: 'fixed', inset: 0, pointerEvents: 'none', zIndex: 0 }}>
          {/* Orb 1 — orange brand */}
          <div style={{
            position: 'absolute', top: '10%', left: '5%',
            width: 520, height: 520,
            background: dark
              ? 'radial-gradient(circle, rgba(249,115,22,0.10) 0%, transparent 68%)'
              : 'radial-gradient(circle, rgba(249,115,22,0.08) 0%, transparent 68%)',
            borderRadius: '50%',
            animation: 'floatOrb1 14s ease-in-out infinite',
          }} />
          {/* Orb 2 — indigo accent */}
          <div style={{
            position: 'absolute', top: '40%', right: '8%',
            width: 440, height: 440,
            background: dark
              ? 'radial-gradient(circle, rgba(99,102,241,0.10) 0%, transparent 68%)'
              : 'radial-gradient(circle, rgba(99,102,241,0.07) 0%, transparent 68%)',
            borderRadius: '50%',
            animation: 'floatOrb2 18s ease-in-out infinite',
          }} />
          {/* Orb 3 — teal */}
          <div style={{
            position: 'absolute', bottom: '10%', left: '30%',
            width: 360, height: 360,
            background: dark
              ? 'radial-gradient(circle, rgba(20,184,166,0.08) 0%, transparent 68%)'
              : 'radial-gradient(circle, rgba(20,184,166,0.06) 0%, transparent 68%)',
            borderRadius: '50%',
            animation: 'floatOrb3 22s ease-in-out infinite',
          }} />
          {/* Dot grid overlay */}
          <div style={{
            position: 'absolute', inset: 0,
            backgroundImage: dark
              ? 'radial-gradient(circle, rgba(249,115,22,0.18) 1px, transparent 1px)'
              : 'radial-gradient(circle, rgba(99,102,241,0.15) 1px, transparent 1px)',
            backgroundSize: '28px 28px',
            opacity: 0.5,
          }} />
        </div>

        {/* Page content sits above texture */}
        <div style={{ position: 'relative', zIndex: 1 }}>
        <ProfileHeader user={displayUser} totalSpent={totalSpent} VIP_GOAL={VIP_GOAL} />

        <div style={{ maxWidth: 720, margin: '15px auto 0', padding: '0 16px' }}>
          <div style={{
            background: cardBg,
            backdropFilter: 'blur(16px) saturate(1.6)',
            WebkitBackdropFilter: 'blur(16px) saturate(1.6)',
            borderRadius: 20,
            border: `1px solid ${border}`,
            boxShadow: dark
              ? '0 8px 40px rgba(0,0,0,0.5), 0 0 0 1px rgba(249,115,22,0.06)'
              : '0 8px 40px rgba(99,102,241,0.08), 0 0 0 1px rgba(255,255,255,0.8)',
            overflow: 'hidden',
          }}>
            {/* Tab bar */}
            <div style={{ display: 'flex', borderBottom: `1px solid ${border}` }}>
              {TABS.map(tb => (
                <button key={tb.key} className="tab-btn" onClick={() => setTab(tb.key)} style={{
                  flex: 1, padding: '18px 0', border: 'none', cursor: 'pointer',
                  background: 'none',
                  fontFamily: 'Kdam Thmor Pro, Rajdhani, sans-serif',
                  fontSize: 16, fontWeight: 400, letterSpacing: 0,
                  color: tab === tb.key ? '#F97316' : tabText,
                  borderBottom: tab === tb.key ? '2.5px solid #F97316' : '2.5px solid transparent',
                  transition: 'all 0.2s',
                }}>{tb.label}</button>
              ))}
            </div>

            {tab === 'profile' && (
              <ProfileTab
                user={displayUser}
                totalSpent={totalSpent}
                VIP_GOAL={VIP_GOAL}
                onSaved={handleProfileSaved}
                notify={notify}
                dark={dark}
              />
            )}
            {tab === 'locations' && <LocationsTab notify={notify} />}
          </div>
        </div>
        </div> {/* end zIndex wrapper */}
      </div>
    </>
  )
}
