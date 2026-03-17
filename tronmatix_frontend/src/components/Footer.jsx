import { Link } from 'react-router-dom'
import logo from '../assets/logo.png'
import { useTheme } from '../context/ThemeContext'

export default function Footer() {
  const { dark } = useTheme()

  const bg      = dark ? '#0f172a' : '#f3f4f6'
  const border  = dark ? '#1f2937' : '#e5e7eb'
  const heading = dark ? '#f9fafb' : '#374151'
  const text    = dark ? '#9ca3af' : '#4b5563'
  const linkHov = '#F97316'
  const bottomBg = dark ? '#0a0f1a' : '#e9eaec'

  return (
    <footer style={{ background: bg, borderTop: `1px solid ${border}`, marginTop: 48 }}>
      <div className="max-w-[1280px] mx-auto px-4 py-10">
        <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
          <div className="col-span-2">
            <img src={logo} alt="Tronmatix" className="h-20 mb-4" />
            <div className="font-bold mb-2" style={{ fontSize: 20, color: heading }}>ADDRESS</div>
            <p style={{ fontSize: 18, color: text, lineHeight: 1.7 }}>
              ផ្លូវលេខ: 162 | ផ្ទះលេខ: 232 | កែងផ្លូវ: 237 | ផ្សារដេប៉ូ១ | ទួលគោក
            </p>
          </div>
          <div>
            <div className="font-bold mb-3" style={{ fontSize: 20, color: heading }}>SOCIAL MEDIA</div>
            <div className="flex gap-3">
              {[
                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>,
                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>,
                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.27 8.27 0 004.84 1.55V6.78a4.85 4.85 0 01-1.07-.09z"/></svg>,
              ].map((icon, i) => (
                <a key={i} href="#"
                  className="w-10 h-10 rounded-full flex items-center justify-center transition-colors hover:text-primary"
                  style={{ background: dark ? '#1f2937' : '#fff', border: `1px solid ${border}`, color: text }}>
                  {icon}
                </a>
              ))}
            </div>
          </div>
          <div>
            <div className="font-bold mb-2" style={{ fontSize: 20, color: heading }}>CONTACT</div>
            <ul className="space-y-1 mb-4" style={{ fontSize: 18, color: text }}>
              <li>• 077 711 126</li>
              <li>• 096 733 3725</li>
            </ul>
            <div className="font-bold mb-2" style={{ fontSize: 20, color: heading }}>MENU</div>
            <ul className="space-y-1" style={{ fontSize: 18 }}>
              <li><Link to="/" className="hover:text-primary transition-colors" style={{ color: text }}>• HOME</Link></li>
              <li><Link to="/contact" className="hover:text-primary transition-colors" style={{ color: text }}>• CONTACT US</Link></li>
            </ul>
          </div>
        </div>
      </div>
      <div style={{ borderTop: `1px solid ${border}`, background: bottomBg }}>
        <div className="max-w-[1280px] mx-auto px-4 py-3 text-center"
          style={{ fontFamily: 'HurstBagod, Rajdhani, sans-serif', fontSize: 15, color: text }}>
          © TRONMATIX. 2022. ALL RIGHTS RESERVED
        </div>
      </div>
    </footer>
  )
}
