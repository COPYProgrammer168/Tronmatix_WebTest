import { useAuth } from '../context/AuthContext'
import { Link, useLocation } from 'react-router-dom'
import { useLang } from '../context/LanguageContext'
import axiosClient from '../lib/axios'
import { useEffect, useState } from 'react'

export default function TelegramConnectMarquee() {
  const { user } = useAuth()
  const { isKhmer } = useLang()
  const location = useLocation()
  const [text, setText] = useState(null)

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

  const fontFamily = isKhmer
    ? "Kdam Thmor Pro, Rajdhani, sans-serif"
    : undefined

  return (
    <Link
      to="/profile"
      className="block w-full overflow-hidden cursor-pointer"
      style={{
        background: "rgba(249,115,22,0.08)",
        borderBottom: "1px solid rgba(249,115,22,0.18)",
        padding: "10px 0",
      }}
    >
      <div
        className="flex whitespace-nowrap"
        style={{
          animation: "marqueeScroll 28s linear infinite",
          width: "max-content",
        }}
        onMouseEnter={(e) => (e.currentTarget.style.animationPlayState = 'paused')}
        onMouseLeave={(e) => (e.currentTarget.style.animationPlayState = 'running')}
      >
        <span
          aria-hidden="false"
          style={{
            fontSize: 14,
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
            fontSize: 14,
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
          .telegram-marquee-track {
            animation: none !important;
          }
        }
      `}</style>
    </Link>
  )
}
