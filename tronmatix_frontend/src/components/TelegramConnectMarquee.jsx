import { useAuth } from '../context/AuthContext'
import { Link, useLocation } from 'react-router-dom'
import { useLang } from '../context/LanguageContext'
import axiosClient from '../lib/axios'
import { useEffect, useRef, useState } from 'react'

export default function TelegramConnectMarquee() {
  const { user } = useAuth()
  const { isKhmer } = useLang()
  const location = useLocation()
  const [text, setText] = useState(null)
  const [isMobile, setIsMobile] = useState(false)
  const trackRef = useRef(null)

  if (!user || user.telegram_connected) return null

  useEffect(() => {
    const route = location.pathname.replace(/^\//, '') || '/'
    axiosClient.get(`/api/marquees?route=${encodeURIComponent(route)}`)
      .then((res) => {
        const data = res.data?.data
        if (data) {
          setText(isKhmer ? data.text_kh : data.text_en)
        }
      })
      .catch(() => {})
  }, [location.pathname, isKhmer])

  useEffect(() => {
    const checkMobile = () => setIsMobile(window.innerWidth < 640)
    checkMobile()
    window.addEventListener('resize', checkMobile)
    return () => window.removeEventListener('resize', checkMobile)
  }, [])

  const togglePause = () => {
    if (!trackRef.current) return
    const current = trackRef.current.style.animationPlayState
    trackRef.current.style.animationPlayState = current === 'paused' ? 'running' : 'paused'
  }

  const fontFamily = isKhmer
    ? "Kdam Thmor Pro, Rajdhani, sans-serif"
    : undefined

  return (
    <Link
      to="/profile"
      className="block w-full overflow-hidden cursor-pointer select-none"
      style={{
        background: "rgba(249,115,22,0.08)",
        borderBottom: "1px solid rgba(249,115,22,0.18)",
        padding: isMobile ? "8px 0" : "10px 0",
      }}
    >
      <div
        ref={trackRef}
        className="flex whitespace-nowrap"
        style={{
          animation: `marqueeScroll ${isMobile ? 36 : 28}s linear infinite`,
          width: "max-content",
        }}
        onMouseEnter={(e) => (e.currentTarget.style.animationPlayState = 'paused')}
        onMouseLeave={(e) => (e.currentTarget.style.animationPlayState = 'running')}
        onClick={togglePause}
        role="marquee"
        aria-label={text || 'Telegram connect notice'}
      >
        <span
          aria-hidden="false"
          style={{
            fontSize: isMobile ? 13 : 14,
            fontWeight: 600,
            color: "#F97316",
            fontFamily,
            paddingRight: 4,
          }}
        >
          {text}
        </span>
        <span
          aria-hidden="true"
          style={{
            fontSize: isMobile ? 13 : 14,
            fontWeight: 600,
            color: "#F97316",
            fontFamily,
            paddingRight: 4,
          }}
        >
          {text}
        </span>
      </div>

      <style>{`
        @keyframes marqueeScroll {
          0%   { transform: translateX(-50%); }
          100% { transform: translateX(0%); }
        }
        @media (prefers-reduced-motion: reduce) {
          .marquee-track {
            animation: none !important;
          }
        }
      `}</style>
    </Link>
  )
}
