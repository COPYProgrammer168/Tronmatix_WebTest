// src/pages/ContactPage.jsx
import { useTheme } from '../context/ThemeContext'
import logo from '../assets/logo.png'

const GOOGLE_MAPS_URL = 'https://goo.gl/maps/8q7eeNwZH5uz1YwZ8'
const FACEBOOK_URL    = 'https://www.facebook.com/TronmatixComputer'
const TELEGRAM_URL    = 'https://t.me/+VZScFi_U95PsFk0M'
const TIKTOK_URL      = 'https://www.tiktok.com/@tronmatixcomputer'

/* ── Icons ──────────────────────────────────────────────────────────────────── */
function PhoneIcon({ size = 20 }) {
  return (
    <svg width={size} height={size} fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round"
        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
    </svg>
  )
}
function MapPinIcon({ size = 20 }) {
  return (
    <svg width={size} height={size} fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
      <path strokeLinecap="round" strokeLinejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
  )
}
function ClockIcon({ size = 20 }) {
  return (
    <svg width={size} height={size} fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
      <circle cx="12" cy="12" r="10" />
      <path strokeLinecap="round" strokeLinejoin="round" d="M12 6v6l4 2" />
    </svg>
  )
}
function FacebookIcon() {
  return <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" /></svg>
}
function TelegramIcon() {
  return <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" /></svg>
}
function TikTokIcon() {
  return <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.27 8.27 0 004.84 1.55V6.78a4.85 4.85 0 01-1.07-.09z" /></svg>
}

/* ── Info Card ──────────────────────────────────────────────────────────────── */
function InfoCard({ icon, label, children, dark }) {
  return (
    <div className="flex flex-col gap-3 p-4 rounded-xl h-full"
      style={{ background: dark ? '#1f2937' : '#f8fafc', border: `1px solid ${dark ? '#374151' : '#e2e8f0'}` }}>
      <div className="flex items-center gap-2">
        <div className="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 text-primary"
          style={{ background: 'rgba(249,115,22,0.12)' }}>
          {icon}
        </div>
        <span className="font-black tracking-widest" style={{ fontSize: 10, color: '#F97316', letterSpacing: 2 }}>
          {label}
        </span>
      </div>
      <div>{children}</div>
    </div>
  )
}

/* ── Social Button ──────────────────────────────────────────────────────────── */
function SocialBtn({ href, icon, label, color }) {
  return (
    <a href={href} target="_blank" rel="noopener noreferrer"
      className="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-white transition-all hover:scale-105 hover:shadow-lg active:scale-95"
      style={{ background: color, fontSize: 14 }}>
      {icon}{label}
    </a>
  )
}

/* ── Page ───────────────────────────────────────────────────────────────────── */
export default function ContactPage() {
  const { dark } = useTheme()

  const bg       = dark ? '#111827' : '#f1f5f9'
  const cardBg   = dark ? '#1e293b' : '#ffffff'
  const border   = dark ? '#334155' : '#e2e8f0'
  const textMain = dark ? '#f1f5f9' : '#0f172a'
  const textSub  = dark ? '#94a3b8' : '#64748b'

  return (
    <div style={{ minHeight: '100vh', background: bg, fontFamily: 'Rajdhani, sans-serif' }}>

      {/* ══════════════════════════════════════════
          HERO BANNER — Light theme, 3 columns
      ══════════════════════════════════════════ */}
      <div className="relative overflow-hidden"
        style={{ background: dark ? 'linear-gradient(135deg,#0f172a,#1e293b)' : 'linear-gradient(135deg,#fff7ed 0%,#ffffff 50%,#fff7ed 100%)', minHeight: 280 }}>

        {/* Subtle grid pattern */}
        <div className="absolute inset-0 pointer-events-none"
          style={{
            opacity: dark ? 0.04 : 0.06,
            backgroundImage: 'linear-gradient(#F97316 1px,transparent 1px),linear-gradient(90deg,#F97316 1px,transparent 1px)',
            backgroundSize: '48px 48px'
          }} />

        {/* Glow blobs */}
        <div className="absolute pointer-events-none"
          style={{ top: -60, left: '30%', width: 300, height: 300, borderRadius: '50%', background: 'rgba(249,115,22,0.08)', filter: 'blur(70px)' }} />
        <div className="absolute pointer-events-none"
          style={{ bottom: -40, right: '10%', width: 200, height: 200, borderRadius: '50%', background: 'rgba(249,115,22,0.06)', filter: 'blur(50px)' }} />

        {/* Orange bottom border */}
        <div className="absolute bottom-0 left-0 right-0 h-px"
          style={{ background: 'linear-gradient(to right, transparent, #F97316 30%, #F97316 70%, transparent)' }} />

        <div className="max-w-6xl mx-auto px-6 py-10 relative">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">

            {/* ── COL 1: LOGO (left) ── */}
            <div className="flex flex-col items-center justify-center">
              {/* Glowing ring */}
              <div className="relative flex items-center justify-center mb-4">
                <div className="absolute pointer-events-none"
                  style={{ width: 220, height: 220, borderRadius: '50%', background: 'radial-gradient(circle, rgba(249,115,22,0.15) 0%, transparent 70%)', filter: 'blur(8px)' }} />
                <div className="absolute pointer-events-none rounded-full"
                  style={{ width: 200, height: 200, border: '1px solid rgba(249,115,22,0.12)' }} />
                <div className="relative flex items-center justify-center rounded-full"
                  style={{
                    width: 170, height: 170,
                    background: dark ? 'rgba(249,115,22,0.07)' : 'rgba(249,115,22,0.06)',
                    border: '2px solid rgba(249,115,22,0.35)',
                    boxShadow: '0 8px 40px rgba(249,115,22,0.20), inset 0 0 30px rgba(249,115,22,0.05)',
                  }}>
                  <img src={logo} alt="Tronmatix Computer"
                    style={{ width: 130, height: 130, objectFit: 'contain', filter: 'drop-shadow(0 0 16px rgba(249,115,22,0.50))' }} />
                </div>
              </div>
              {/* Brand */}
              <div className="text-center leading-snug">
                <div className="font-black" style={{ fontSize: 15, letterSpacing: 5, color: dark ? '#fff' : '#111827' }}>
                  TRONMATIX
                </div>
                <div className="font-bold" style={{ fontSize: 10, color: '#F97316', letterSpacing: 7 }}>
                  COMPUTER
                </div>
              </div>
            </div>

            {/* ── COL 2: CENTER — title + desc + buttons ── */}
            <div className="flex flex-col items-center text-center px-2">
              {/* Label */}
              <div className="flex items-center gap-2 mb-3">
                <div className="h-px w-6" style={{ background: '#F97316' }} />
                <span className="font-black" style={{ fontSize: 12, color: '#F97316', letterSpacing: 3 }}>
                  Welcome to Tronmatix Computer
                </span>
                <div className="h-px w-6" style={{ background: '#F97316' }} />
              </div>

              <h1 className="font-black mb-3"
                style={{
                  fontSize: 'clamp(26px, 4vw, 48px)',
                  letterSpacing: 4,
                  lineHeight: 1.05,
                  color: dark ? '#ffffff' : '#111827',
                  textShadow: dark ? '0 0 40px rgba(249,115,22,0.15)' : '0 2px 20px rgba(249,115,22,0.10)',
                }}>
                CONTACT US
              </h1>

              <p style={{ fontSize: 14, color: dark ? '#94a3b8' : '#64748b', maxWidth: 340, lineHeight: 1.8 }}>
                សម្រាប់បងៗដែលកំពុងស្វែងរក Laptop &amp; Desktop សម្រាប់ Design, Rendering, Gaming —
                មានការធានា <span className="font-bold" style={{ color: '#F97316' }}>1–3 ឆ្នាំ</span> នៅរាល់ផលិតផល។
              </p>

              {/* Phone pills */}
              <div className="flex flex-wrap gap-2 mt-5 justify-center">
                <a href="tel:+85596733 3725"
                  className="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full font-black text-white transition-all hover:scale-105 hover:shadow-lg"
                  style={{ background: '#F97316', fontSize: 13, boxShadow: '0 4px 15px rgba(249,115,22,0.35)' }}>
                  <PhoneIcon size={13} /> 096 733 3725
                </a>
                <a href="tel:+85577711126"
                  className="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full font-black transition-all hover:scale-105"
                  style={{
                    background: dark ? 'rgba(249,115,22,0.10)' : 'rgba(249,115,22,0.08)',
                    border: '1.5px solid rgba(249,115,22,0.45)',
                    color: '#F97316', fontSize: 13
                  }}>
                  <PhoneIcon size={13} /> 077 711 126
                </a>
              </div>
            </div>

            {/* ── COL 3: CONTACT INFO (right) ── */}
            <div className="flex flex-col gap-4 pl-0 md:pl-6"
              style={{ borderLeft: '1px solid rgba(249,115,22,0.18)' }}>

              {[
                {
                  icon: <MapPinIcon size={17} />,
                  label: 'ADDRESS',
                  content: (
                    <a href={GOOGLE_MAPS_URL} target="_blank" rel="noopener noreferrer"
                      className="hover:text-primary transition-colors"
                      style={{ fontSize: 13, color: dark ? '#cbd5e1' : '#374151', lineHeight: 1.7 }}>
                      ផ្លូវ 162 | ផ្ទះ 232 | ផ្សារដេប៉ូ១<br />
                      ទួលគោក, ភ្នំពេញ
                      <span className="block mt-1 font-bold text-primary" style={{ fontSize: 11 }}>📍 View on Maps →</span>
                    </a>
                  )
                },
                {
                  icon: <PhoneIcon size={17} />,
                  label: 'PHONE',
                  content: (
                    <div className="flex flex-col gap-0.5">
                      <a href="tel:+85596733 3725"
                        className="font-black hover:text-primary transition-colors"
                        style={{ fontSize: 16, color: dark ? '#f1f5f9' : '#111827' }}>
                        096 733 3725
                      </a>
                      <a href="tel:+85577711126"
                        className="font-black hover:text-primary transition-colors"
                        style={{ fontSize: 16, color: dark ? '#f1f5f9' : '#111827' }}>
                        077 711 126
                      </a>
                    </div>
                  )
                },
                {
                  icon: <ClockIcon size={17} />,
                  label: 'WORKING HOURS',
                  content: (
                    <div>
                      <div className="font-black" style={{ fontSize: 14, color: dark ? '#f1f5f9' : '#111827' }}>Mon – Sun</div>
                      <div className="font-bold text-primary" style={{ fontSize: 14 }}>9:00 AM – 8:00 PM</div>
                    </div>
                  )
                },
              ].map(({ icon, label, content }) => (
                <div key={label} className="flex items-start gap-3">
                  <div className="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 text-primary"
                    style={{ background: 'rgba(249,115,22,0.10)', border: '1px solid rgba(249,115,22,0.22)', marginTop: 2 }}>
                    {icon}
                  </div>
                  <div>
                    <div className="font-black mb-1" style={{ fontSize: 10, color: '#F97316', letterSpacing: 2 }}>
                      {label}
                    </div>
                    {content}
                  </div>
                </div>
              ))}

              {/* Social icons */}
              <div className="flex gap-2 mt-1">
                {[
                  { href: FACEBOOK_URL, bg: '#1877f2', icon: <FacebookIcon /> },
                  { href: TELEGRAM_URL, bg: '#0088cc', icon: <TelegramIcon /> },
                  { href: TIKTOK_URL,   bg: '#111',    icon: <TikTokIcon /> },
                ].map(({ href, bg, icon }) => (
                  <a key={href} href={href} target="_blank" rel="noopener noreferrer"
                    className="w-9 h-9 rounded-lg flex items-center justify-center text-white transition-all hover:scale-110 hover:shadow-md"
                    style={{ background: bg }}>
                    {icon}
                  </a>
                ))}
              </div>
            </div>

          </div>
        </div>
      </div>

      {/* ══════════════════════════════════════════
          MAIN CONTENT
      ══════════════════════════════════════════ */}
      <div className="max-w-5xl mx-auto px-6 py-10">
        <div className="grid lg:grid-cols-2 gap-8 items-start">

          {/* ── LEFT: Contact info ── */}
          <div className="flex flex-col gap-5">

            {/* 3 info cards in a row */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <InfoCard dark={dark} label="ADDRESS" icon={<MapPinIcon size={20} />}>
                <a href={GOOGLE_MAPS_URL} target="_blank" rel="noopener noreferrer"
                  className="font-semibold hover:text-primary transition-colors block"
                  style={{ fontSize: 16, color: textMain, lineHeight: 1.65 }}>
                  ផ្លូវ 162 | ផ្ទះ 232 | ផ្សារដេប៉ូ១<br />
                  ទួលគោក, ភ្នំពេញ
                  <span className="block mt-1.5 font-bold text-primary" style={{ fontSize: 13 }}>
                    📍 View on Maps →
                  </span>
                </a>
              </InfoCard>

              <InfoCard dark={dark} label="PHONE NUMBER" icon={<PhoneIcon size={20} />}>
                <a href="tel:+85596733 3725"
                  className="font-black hover:text-primary transition-colors block"
                  style={{ fontSize: 15, color: textMain }}>
                  096 733 3725
                </a>
                <a href="tel:+85577711126"
                  className="font-black hover:text-primary transition-colors block mt-1"
                  style={{ fontSize: 15, color: textMain }}>
                  077 711 126
                </a>
              </InfoCard>

              <InfoCard dark={dark} label="WORKING HOURS" icon={<ClockIcon size={20} />}>
                <div className="font-black" style={{ fontSize: 15, color: textMain }}>Mon – Sun</div>
                <div className="font-bold text-primary" style={{ fontSize: 14 }}>9:00 AM – 8:00 PM</div>
              </InfoCard>
            </div>

            {/* Social media */}
            <div className='items-center text-center'>
              <div className="font-black mb-3" style={{ fontSize: 15, color: '#F97316', letterSpacing: 3 }}>
                SOCIAL MEDIA
              </div>
              <div className="flex flex-wrap gap-2.5 justify-center">
                <SocialBtn href={FACEBOOK_URL} icon={<FacebookIcon />} label="Facebook" color="#1877f2" />
                <SocialBtn href={TELEGRAM_URL} icon={<TelegramIcon />} label="Telegram" color="#0088cc" />
                <SocialBtn href={TIKTOK_URL}   icon={<TikTokIcon />}   label="TikTok"   color="#010101" />
              </div>
            </div>

            {/* Store details card */}
            <div className="rounded-2xl p-5 relative overflow-hidden"
              style={{ background: cardBg, border: `1px solid ${border}` }}>
              <div className="absolute top-0 right-0 w-28 h-28 pointer-events-none"
                style={{ background: 'radial-gradient(circle at top right, rgba(249,115,22,0.12), transparent)', opacity: 0.8 }} />

              <div className="flex items-center gap-3 mb-4">
                <img src={logo} alt="" style={{ width: 36, height: 36, objectFit: 'contain' }} />
                <div>
                  <div className="font-black" style={{ fontSize: 15, color: textMain }}>TRONMATIX COMPUTER</div>
                  <div style={{ fontSize: 12, color: textSub }}>Official Store</div>
                </div>
                <div className="ml-auto flex items-center gap-1.5 px-3 py-1 rounded-full font-black flex-shrink-0"
                  style={{ background: 'rgba(34,197,94,0.12)', border: '1px solid rgba(34,197,94,0.3)', fontSize: 11, color: '#22c55e' }}>
                  <span className="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse inline-block" />
                  OPEN
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                {[
                  { icon: '🕘', label: 'Hours',    value: 'Mon–Sun, 9AM–8PM' },
                  { icon: '📍', label: 'Location', value: 'Toul Kork, Phnom Penh' },
                  { icon: '🛡️', label: 'Warranty', value: '1–3 Years Official' },
                  { icon: '💻', label: 'Products',  value: 'PCs, Laptops, Monitors' },
                ].map(({ icon, label, value }) => (
                  <div key={label} className="flex items-start gap-2">
                    <span style={{ fontSize: 16, flexShrink: 0, marginTop: 1 }}>{icon}</span>
                    <div>
                      <div className="font-black" style={{ fontSize: 16, color: '#F97316', letterSpacing: 1.5 }}>{label.toUpperCase()}</div>
                      <div className="font-semibold" style={{ fontSize: 16, color: textSub, lineHeight: 1.4 }}>{value}</div>
                    </div>
                  </div>
                ))}
              </div>

              <a href={GOOGLE_MAPS_URL} target="_blank" rel="noopener noreferrer"
                className="mt-4 flex items-center justify-center gap-2 w-full py-2.5 rounded-xl font-black tracking-wider transition-all hover:brightness-110 hover:scale-[1.02] active:scale-95"
                style={{ background: 'linear-gradient(135deg, #F97316, #fb923c)', color: '#fff', fontSize: 14, letterSpacing: 1 }}>
                <MapPinIcon size={16} /> GET DIRECTIONS
              </a>
            </div>
          </div>

          {/* ── RIGHT: Map ── */}
          <div className="rounded-2xl overflow-hidden shadow-xl"
            style={{ border: `1px solid ${border}` }}>
            <iframe
              title="Tronmatix Computer Location"
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3908.8449116589763!2d104.8995165!3d11.562973499999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x310951b12cb1dcc7%3A0xb36c6119eb9b2c23!2sTronmatix%20Computer%20Store!5e0!3m2!1sen!2skh!4v1773470968952!5m2!1sen!2skh"
              width="100%"
              height="560"
              style={{ border: 0, display: 'block' }}
              allowFullScreen=""
              loading="lazy"
              referrerPolicy="no-referrer-when-downgrade"
            />
          </div>
        </div>
      </div>
    </div>
  )
}
