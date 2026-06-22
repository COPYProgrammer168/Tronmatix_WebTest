import { Link } from 'react-router-dom'
import logo from '../assets/logo.png'
import telegramIcon from '../assets/telegram.svg'
import { useTheme } from '../context/ThemeContext'
import { useLang } from '../context/LanguageContext'

export default function Footer() {
  const { dark } = useTheme()
  const { isKhmer } = useLang()

  const bg      = dark ? '#0f172a' : '#f3f4f6'
  const border  = dark ? '#1f2937' : '#e5e7eb'
  const heading = dark ? '#f9fafb' : '#374151'
  const text    = dark ? '#9ca3af' : '#4b5563'
  const bottomBg = dark ? '#0a0f1a' : '#e9eaec'
  
  const bodyFont = isKhmer
    ? "Kdam_Thmor_Pro, sans-serif"
    : "Rajdhani, sans-serif";
  const headFont = isKhmer ? 'Kh_Jrung_Thom, sans-serif' : 'HurstBagod, sans-serif'

  return (
    <footer style={{ background: bg, borderTop: `1px solid ${border}`, marginTop: 48, fontFamily: headFont }}>
      <div className="max-w-[1280px] mx-auto px-4 py-10"> 
        <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
          <div className="col-span-2">
            <img src={logo} alt="Tronmatix" className="h-20 mb-4" />
            <div className="font-bold mb-2" style={{ fontSize: 20, color: heading, fontFamily: bodyFont }}>ADDRESS</div>
            <Link to="/contact" target="_blank" rel="noopener noreferrer" style={{ fontSize: 18, color: text, lineHeight: 1.7, fontFamily: bodyFont }}>
              ផ្លូវលេខ: ១៦២ | ផ្ទះលេខ: ២៣២ | កែងផ្លូវ: ២៣៧ | ផ្សារដេប៉ូ១ | ទួលគោក
            </Link>
          </div>
          <div>
            <div className="font-bold mb-3" style={{ fontSize: 20, color: heading, fontFamily: headFont }}>SOCIAL MEDIA</div>
            <div className="flex gap-3">
              {[
                { icon: <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg> },
                { icon: <img src={telegramIcon} alt="Telegram" className="w-5 h-5" /> },
                { icon: <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.27 8.27 0 004.84 1.55V6.78a4.85 4.85 0 01-1.07-.09z"/></svg> },
              ].map((item, i) => (
                <a key={i} href="#"
                  className={`w-10 h-10 rounded-full flex items-center justify-center transition-colors 
                    hover:text-white hover:bg-primary
                    ${dark ? 'bg-gray-800 border-gray-700 text-gray-400' : 'bg-white border-gray-300 text-gray-600'}`}
                  style={{ borderWidth: '1px' }}>
                  {item.icon}
                </a>
              ))}
            </div>
          </div>
          <div>
            <div className="font-bold mb-2" style={{ fontSize: 20, color: heading, fontFamily: headFont }}>CONTACT</div>
            <ul className="space-y-1 mb-4" style={{ fontSize: 18, color: text }}>
              <li style={{ fontFamily: bodyFont }}>• 077 711 126</li>
              <li style={{ fontFamily: bodyFont }}>• 096 733 3725</li>
            </ul>
            <div className="font-bold mb-2" style={{ fontSize: 20, color: heading, fontFamily: headFont }}>MENU</div>
            <ul className="space-y-1" style={{ fontSize: 18 }}>
              <li><Link to="/" className="hover:text-primary transition-colors" style={{ color: text, fontFamily: bodyFont }}>• HOME</Link></li>
              <li><Link to="/contact" className="hover:text-primary transition-colors" style={{ color: text, fontFamily: bodyFont }}>• CONTACT US</Link></li>
            </ul>
          </div>
        </div>
      </div>
      <div style={{ borderTop: `1px solid ${border}`, background: bottomBg }}>
        <div className="max-w-[1280px] mx-auto px-4 py-3 text-center"
          style={{ fontSize: 15, color: text }}>
          © TRONMATIX. 2022. ALL RIGHTS RESERVED
        </div>
      </div>
    </footer>
  )
}
