import { useState, useRef, useEffect } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { useCart } from '../context/CartContext'
import { useFavorites } from '../context/FavoritesContext'
import { useTheme } from '../context/ThemeContext'
import { useLang } from '../context/LanguageContext'
import logo from '../assets/logo.png'

const slugify = s => s.toLowerCase().replace(/\s+/g, '-')

// navItems: label is always the English slug key used for URL + NAV_LABEL_KEYS lookup.
// URLs never change — only the displayed text translates.
const navItems = [
  { label: 'HOME', path: '/' },
  {
    label: 'PC BUILD', path: '/category/pc-build',
    categories: ['PC BUILD UNDER 1K','PC BUILD UNDER 2K','PC BUILD UNDER 3K','PC BUILD UNDER 4K','PC BUILD UNDER 5K','PC BUILD 5K UP'],
    sub: ['PC BUILD UNDER 1K','PC BUILD UNDER 2K','PC BUILD UNDER 3K','PC BUILD UNDER 4K','PC BUILD UNDER 5K','PC BUILD 5K UP'],
  },
  {
    label: 'MONITOR', path: '/category/monitor',
    categories: ['MONITOR 25INCH','MONITOR 27INCH','MONITOR 32INCH','MONITOR 34INCH','MONITOR 39INCH','MONITOR 42INCH','MONITOR 48INCH','MONITOR 49INCH'],
    sub: ['MONITOR 25INCH','MONITOR 27INCH','MONITOR 32INCH','MONITOR 34INCH','MONITOR 39INCH','MONITOR 42INCH','MONITOR 48INCH','MONITOR 49INCH'],
  },
  {
    label: 'PC PART', path: '/category/pc-part',
    categories: ['CPU','RAM','MAINBOARD','COOLING','M2','VGA','CASE','POWER SUPPLY','FAN'],
    sub: [
      { label: 'CPU',          brands: ['INTEL 12TH','INTEL 13TH','INTEL 14TH','INTEL 15TH ULTRA','AMD ALL SERIES'] },
      { label: 'RAM',          brands: ['DDR4 16GB','DDR4 32GB','DDR5 16GB','DDR5 32GB','DDR5 64GB'] },
      { label: 'MAINBOARD',    brands: ['ASUS','GIGABYTE','MSI','ASROCK'] },
      { label: 'COOLING',      brands: ['NOCTUA','DEEPCOOL','BE QUIET','CORSAIR','LIAN LI'] },
      { label: 'M2',           brands: ['SAMSUNG','WD','SEAGATE','KINGSTON','CRUCIAL'] },
      { label: 'VGA',          brands: ['RTX 5090','RTX 5080','RTX 4090','RTX 4080','RTX 4070','RX 7900 XTX','RX 7800 XT'] },
      { label: 'CASE',         brands: ['LIAN LI','FRACTAL','NZXT','CORSAIR','PHANTEKS'] },
      { label: 'POWER SUPPLY', brands: ['CORSAIR','SEASONIC','EVGA','BE QUIET','COOLER MASTER'] },
      { label: 'FAN',          brands: ['NOCTUA','BE QUIET','CORSAIR','ARCTIC','LIAN LI'] },
    ],
  },
  {
    label: 'HOT ITEM', path: '/category/hot-item',
    categories: ['BEST PRICE','BEST SET'], sub: ['BEST PRICE','BEST SET'],
  },
  {
    label: 'ACCESSORY', path: '/category/accessory',
    categories: ['KEYBOARD','MOUSE','HEADSET','EARPHONE','MONITOR STAND','SPEAKER','MICROPHONE','WEBCAM','MOUSEPAD','LIGHTBAR','ROUTER'],
    sub: ['KEYBOARD','MOUSE','HEADSET','EARPHONE','MONITOR STAND','SPEAKER','MICROPHONE','WEBCAM','MOUSEPAD','LIGHTBAR','ROUTER'],
  },
  {
    label: 'TABLE CHAIR', path: '/category/table-chair',
    categories: ['DX RACER','SECRETLAB','RAZER','CONSAIR','FANTECH','COOLER MASTER','TTR RACING'],
    sub: ['DX RACER','SECRETLAB','RAZER','CONSAIR','FANTECH','COOLER MASTER','TTR RACING'],
  },
  { label: 'CONTACT US', path: '/contact' },
]

// Maps English label → i18n key so URLs stay English but text translates
const NAV_LABEL_KEYS = {
  'HOME':        'nav.home',
  'PC BUILD':    'nav.pcBuild',
  'MONITOR':     'nav.monitor',
  'PC PART':     'nav.pcPart',
  'HOT ITEM':    'nav.hotItem',
  'ACCESSORY':   'nav.accessory',
  'TABLE CHAIR': 'nav.tableChair',
  'CONTACT US':  'nav.contactUs',
}

/* ── Theme Toggle ─────────────────────────────────────────────────────────── */
function ThemeToggle() {
  const { dark, toggle } = useTheme()
  return (
    <button onClick={toggle}
      title={dark ? 'Switch to Light Mode' : 'Switch to Dark Mode'}
      className="relative flex items-center justify-center w-9 h-9 rounded-full border-2 transition-all duration-300 focus:outline-none flex-shrink-0"
      style={{ borderColor: dark ? '#F97316' : '#e5e7eb', background: dark ? 'rgba(249,115,22,0.12)' : '#f3f4f6' }}>
      <span className="absolute transition-all duration-300"
        style={{ opacity: dark ? 0 : 1, transform: dark ? 'scale(0.4) rotate(90deg)' : 'scale(1) rotate(0deg)' }}>
        <svg className="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm5 10a5 5 0 11-10 0 5 5 0 0110 0zm4.95-1H21a1 1 0 110 2h-1.05A8.001 8.001 0 0113 20.95V22a1 1 0 11-2 0v-1.05A8.001 8.001 0 013.05 13H2a1 1 0 110-2h1.05A8.001 8.001 0 0111 3.05V2a1 1 0 112 0v1.05A8.001 8.001 0 0120.95 11z"/>
        </svg>
      </span>
      <span className="absolute transition-all duration-300"
        style={{ opacity: dark ? 1 : 0, transform: dark ? 'scale(1) rotate(0deg)' : 'scale(0.4) rotate(-90deg)' }}>
        <svg className="w-4 h-4" fill="#F97316" viewBox="0 0 24 24">
          <path d="M21 12.79A9 9 0 1111.21 3a7 7 0 109.79 9.79z"/>
        </svg>
      </span>
    </button>
  )
}

/* ── Language Toggle ──────────────────────────────────────────────────────── */
function LanguageToggle() {
  const { toggle, isKhmer } = useLang()
  return (
    <button
      onClick={toggle}
      title={isKhmer ? 'Switch to English' : 'ប្តូរទៅភាសាខ្មែរ'}
      aria-label={isKhmer ? 'Switch to English' : 'Switch to Khmer'}
      className="relative flex items-center flex-shrink-0 focus:outline-none"
      style={{
        height: 36, minWidth: 76, borderRadius: 20,
        border: `2px solid ${isKhmer ? '#F97316' : '#e5e7eb'}`,
        background: isKhmer ? 'rgba(249,115,22,0.10)' : '#f3f4f6',
        padding: '0 3px', cursor: 'pointer', overflow: 'hidden',
        transition: 'border-color 0.25s ease, background 0.25s ease',
      }}>
      <span aria-hidden="true" style={{
        position: 'absolute', top: 2, bottom: 2,
        width: 'calc(50% - 2px)', borderRadius: 16,
        background: isKhmer ? '#F97316' : '#1f2937', left: 2,
        transform: isKhmer ? 'translateX(calc(100% + 0px))' : 'translateX(0)',
        transition: 'transform 0.25s cubic-bezier(0.4,0,0.2,1), background 0.25s',
        pointerEvents: 'none',
      }} />
      <span style={{
        position: 'relative', flex: 1, textAlign: 'center',
        fontSize: 11, fontWeight: 800, letterSpacing: 0.5,
        color: isKhmer ? 'rgba(156,163,175,1)' : '#ffffff',
        fontFamily: 'Rajdhani, sans-serif',
        transition: 'color 0.2s', zIndex: 1, padding: '0 4px', userSelect: 'none',
      }}>EN</span>
      <span style={{
        position: 'relative', flex: 1, textAlign: 'center',
        fontSize: 12, fontWeight: 800,
        color: isKhmer ? '#ffffff' : 'rgba(156,163,175,1)',
        fontFamily: 'Kh_Jrung_Thom, Khmer OS, system-ui, sans-serif',
        transition: 'color 0.2s', zIndex: 1, padding: '0 4px',
        userSelect: 'none', lineHeight: 1.8,
      }}>ខ្មែរ</span>
    </button>
  )
}

/* ── Icon button ─────────────────────────────────────────────────────────── */
function IconBtn({ onClick, className = '', style = {}, children, title }) {
  const [hovered, setHovered] = useState(false)
  return (
    <button onClick={onClick} title={title}
      className={`relative p-2 transition-colors ${className}`}
      style={{ ...style, color: hovered ? '#F97316' : style.color }}
      onMouseEnter={() => setHovered(true)}
      onMouseLeave={() => setHovered(false)}>
      {children}
    </button>
  )
}

/* ── Desktop dropdown panel ─────────────────────────────────────────────── */
function DropdownPanel({ item, openDrop, openSub, setOpenDrop, setOpenSub, isKhmer }) {
  const isNested = typeof item.sub[0] === 'object'
  const dropFont = isKhmer ? 'Kh_Jrung_Thom, sans-serif' : 'Rajdhani, sans-serif'
  return (
    <div className="absolute top-full left-0 shadow-2xl z-[200] py-2 min-w-[210px]"
      style={{ background: '#1a1a1a', border: '1px solid #F97316' }}
      onMouseEnter={() => setOpenDrop(item.label)}
      onMouseLeave={() => { setOpenDrop(null); setOpenSub(null) }}>
      <Link
        to={item.categories ? `${item.path}?cats=${item.categories.map(c => encodeURIComponent(c)).join(',')}` : item.path}
        className="block px-4 py-2 font-bold text-primary border-b border-[#333] mb-1 tracking-wider"
        style={{ fontFamily: dropFont, fontSize: 15, letterSpacing: isKhmer ? 0 : undefined }}
        onClick={() => { setOpenDrop(null); setOpenSub(null) }}>
        ALL {item.label}
      </Link>
      {isNested
        ? item.sub.map(subObj => (
          <div key={subObj.label} className="relative"
            onMouseEnter={() => setOpenSub(subObj.label)}
            onMouseLeave={() => setOpenSub(null)}>
            <div className="flex items-center justify-between hover:bg-[#2a2a2a] transition-colors">
              <Link to={`/category/${slugify(item.label)}/${slugify(subObj.label)}`}
                className="flex-1 px-4 py-2.5 font-semibold text-gray-300 hover:text-primary tracking-wider"
                style={{ fontFamily: dropFont, fontSize: 15, letterSpacing: isKhmer ? 0 : undefined }}
                onClick={() => { setOpenDrop(null); setOpenSub(null) }}>
                {subObj.label}
              </Link>
              <span className="pr-3 text-gray-500">
                <svg className="w-3 h-3" fill="none" stroke="currentColor" strokeWidth={2.5} viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
              </span>
            </div>
            {openSub === subObj.label && (
              <div className="absolute left-full top-0 shadow-2xl z-[210] min-w-[220px] py-2"
                style={{ background: '#111', border: '1px solid #F97316' }}
                onMouseEnter={() => setOpenSub(subObj.label)}
                onMouseLeave={() => setOpenSub(null)}>
                <div className="px-4 py-1 text-primary font-black tracking-widest border-b border-[#333] mb-1" style={{ fontSize: 12 }}>
                  {subObj.label}
                </div>
                {subObj.brands.map(brand => (
                  <Link key={brand}
                    to={`/category/${slugify(item.label)}/${slugify(subObj.label)}?brand=${encodeURIComponent(brand)}`}
                    className="block px-4 py-2 text-gray-300 hover:text-primary hover:bg-[#2a2a2a] tracking-wider transition-colors"
                    style={{ fontFamily: dropFont, fontSize: 14, letterSpacing: isKhmer ? 0 : undefined }}
                    onClick={() => { setOpenDrop(null); setOpenSub(null) }}>
                    {brand}
                  </Link>
                ))}
              </div>
            )}
          </div>
        ))
        : item.sub.map(sub => (
          <Link key={sub}
            to={`/category/${slugify(item.label)}/${slugify(sub)}`}
            className="block px-4 py-2.5 font-semibold text-gray-300 hover:text-primary hover:bg-[#2a2a2a] tracking-wider transition-colors"
            style={{ fontFamily: dropFont, fontSize: 15, letterSpacing: isKhmer ? 0 : undefined }}
            onClick={() => { setOpenDrop(null); setOpenSub(null) }}>
            {sub}
          </Link>
        ))
      }
    </div>
  )
}

export default function Navbar({ onAuthOpen }) {
  const [openDrop, setOpenDrop]     = useState(null)
  const [openSub, setOpenSub]       = useState(null)
  const [mobileOpen, setMobileOpen] = useState(false)
  const [mobileSub, setMobileSub]   = useState(null)
  const [search, setSearch]         = useState('')
  const [userMenu, setUserMenu]     = useState(false)
  const [scrolled, setScrolled]     = useState(false)
  const { user, logout, ready }     = useAuth()
  const { items, setCartOpen }      = useCart()
  const { favorites }               = useFavorites()
  const { dark }                    = useTheme()
  const { t, isKhmer }              = useLang()
  const navigate                    = useNavigate()
  const headerRef          = useRef(null)
  const userMenuRef        = useRef(null)
  const compactUserMenuRef = useRef(null)
  const drawerRef          = useRef(null)

  const totalQty = items.reduce((s, i) => s + i.qty, 0)

  const navBg        = dark ? '#111827' : '#ffffff'
  const navBorder    = dark ? '#1f2937' : '#e5e7eb'
  const textColor    = dark ? '#f9fafb' : '#1f2937'
  const subTextColor = dark ? '#9ca3af' : '#6b7280'
  const inputBg      = dark ? '#1f2937' : '#ffffff'
  const inputBorder  = dark ? '#374151' : '#d1d5db'
  const ddBg         = dark ? '#1f2937' : '#ffffff'
  const ddBorder     = dark ? '#374151' : '#e5e7eb'
  const ddHover      = dark ? '#374151' : '#f3f4f6'
  const drawerBg     = dark ? '#111827' : '#ffffff'
  const drawerBorder = dark ? '#1f2937' : '#e5e7eb'
  const drawerSubBg  = dark ? '#0f172a' : '#f1f5f9'

  // Khmer font for nav links when in Khmer mode
  const navFont = isKhmer
    ? 'Kh_Jrung_Thom, Rajdhani, sans-serif'
    : 'Rajdhani, sans-serif'
  const navbFont = isKhmer
    ? 'KantumruyPro, Rajdhani, sans-serif'
    : 'Rajdhani, sans-serif'

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 20)
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  useEffect(() => {
    const handler = e => {
      if (headerRef.current && !headerRef.current.contains(e.target)) {
        setOpenDrop(null); setOpenSub(null)
      }
      const inFull    = userMenuRef.current        && userMenuRef.current.contains(e.target)
      const inCompact = compactUserMenuRef.current && compactUserMenuRef.current.contains(e.target)
      if (!inFull && !inCompact) setUserMenu(false)
    }
    document.addEventListener('mousedown', handler)
    return () => document.removeEventListener('mousedown', handler)
  }, [])

  useEffect(() => {
    document.body.style.overflow = mobileOpen ? 'hidden' : ''
    return () => { document.body.style.overflow = '' }
  }, [mobileOpen])

  const handleSearch = e => {
    e.preventDefault()
    const q = search.trim().toLowerCase()
    if (q) { navigate(`/category/search?q=${encodeURIComponent(q)}`); setSearch(''); setMobileOpen(false) }
  }

  const dropProps = { openDrop, openSub, setOpenDrop, setOpenSub, isKhmer }

  /* ── User Avatar ─────────────────────────────────────────────────────── */
  const UserAvatar = ({ size = 10, fontSize = 16 }) => (
    <div className={`w-${size} h-${size} rounded-full flex-shrink-0 overflow-hidden`}
      style={{ border: '2px solid #F97316', background: '#F97316' }}>
      {user?.avatar ? (
        <img src={user.avatar} alt={user.username}
          style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }}
          onError={e => { e.target.style.display = 'none'; e.target.nextSibling.style.display = 'flex' }} />
      ) : null}
      <div style={{
        display: user?.avatar ? 'none' : 'flex',
        width: '100%', height: '100%',
        alignItems: 'center', justifyContent: 'center',
        color: '#fff', fontWeight: 900, fontSize,
      }}>
        {(user?.username || user?.name || user?.email || 'U').charAt(0).toUpperCase()}
      </div>
    </div>
  )

  /* ── Desktop User Dropdown ───────────────────────────────────────────── */
  const UserDropdown = ({ menuRef }) => (
    <div ref={menuRef} className="relative">
      <button
        onClick={() => { if (!ready) return; user ? setUserMenu(p => !p) : onAuthOpen?.('login') }}
        className="flex flex-col items-center gap-0.5 px-1 transition-colors"
        style={{ color: textColor }}
        disabled={!ready}>
        {!ready ? (
          <div className="w-7 h-7 rounded-full animate-pulse" style={{ background: dark ? '#374151' : '#e5e7eb' }} />
        ) : user ? (
          <>
            <UserAvatar size={10} fontSize={16} />
            <span className="font-bold max-w-[64px] truncate" style={{ fontSize: 13, color: '#F97316' }}>
              {user.username || user.name || 'User'}
            </span>
          </>
        ) : (
          <span style={{ color: textColor, display: 'inline-flex', transition: 'color 0.15s' }}
            onMouseEnter={e => e.currentTarget.style.color = '#F97316'}
            onMouseLeave={e => e.currentTarget.style.color = textColor}>
            <svg className="w-6 h-6" fill="none" stroke="currentColor" strokeWidth={1.8} viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
          </span>
        )}
      </button>

      {ready && user && userMenu && (
        <div className="absolute right-0 top-full mt-2 rounded-lg shadow-xl w-48 py-2 z-[200] border"
          style={{ background: ddBg, borderColor: ddBorder }}>
          {[
            { labelKey: 'nav.myProfile', icon: '👤', path: '/profile' },
            { labelKey: 'nav.myOrders',  icon: '📦', path: '/orders'  },
          ].map(({ labelKey, icon, path }) => (
            <button key={path}
              className="w-full flex items-center gap-2 px-4 py-2 transition-colors text-left"
              style={{ fontFamily: navbFont, fontSize: 15, color: textColor }}
              onMouseEnter={e => { e.currentTarget.style.color = '#F97316'; e.currentTarget.style.background = ddHover }}
              onMouseLeave={e => { e.currentTarget.style.color = textColor; e.currentTarget.style.background = 'transparent' }}
              onClick={() => { setUserMenu(false); navigate(path) }}>
              {icon} {t(labelKey)}
            </button>
          ))}
          <hr style={{ borderColor: ddBorder, margin: '4px 0' }} />
          <button
            onClick={() => { logout(); setUserMenu(false) }}
            className="w-full text-left px-4 py-2 text-red-500 transition-colors"
            style={{ fontFamily: navFont, fontSize: 15 }}
            onMouseEnter={e => e.currentTarget.style.background = ddHover}
            onMouseLeave={e => e.currentTarget.style.background = 'transparent'}>
            🚪 {t('nav.logout')}
          </button>
        </div>
      )}
    </div>
  )

  return (
    <header ref={headerRef} className="sticky top-0 z-50"
      style={{ background: navBg, boxShadow: scrolled ? '0 2px 20px rgba(0,0,0,0.15)' : '0 1px 3px rgba(0,0,0,0.06)' }}>

      {/* ══════════ COMPACT BAR (scrolled) ══════════════════════════════════ */}
      <div style={{ display: scrolled ? 'block' : 'none', borderBottom: `1px solid ${navBorder}` }}>
        <div className="max-w-[1280px] mx-auto px-4 flex items-center gap-3" style={{ height: 64 }}>
          <Link to="/" className="flex-shrink-0">
            <img src={logo} alt="Tronmatix" className="object-contain" style={{ height: 44 }} />
          </Link>

          {/* Inline nav tablet+ */}
          <nav className="hidden lg:flex items-center flex-1 min-w-0">
            <ul className="flex items-center flex-wrap">
              {navItems.map(item => (
                <li key={item.label} className="relative flex-shrink-0">
                  <div onMouseEnter={() => item.sub && setOpenDrop(item.label)}
                    onMouseLeave={() => { setOpenDrop(null); setOpenSub(null) }}>
                    <Link
                      to={item.categories ? `${item.path}?cats=${item.categories.map(c => encodeURIComponent(c)).join(',')}` : item.path}
                      className="flex items-center gap-0.5 px-2 py-2 font-bold tracking-wide whitespace-nowrap"
                      style={{ fontFamily: navbFont, fontSize: 13, color: textColor, transition: 'color 0.15s', letterSpacing: isKhmer ? 0 : undefined }}
                      onMouseEnter={e => e.currentTarget.style.color = '#F97316'}
                      onMouseLeave={e => e.currentTarget.style.color = textColor}
                      onClick={() => { setOpenDrop(null); setOpenSub(null) }}>
                      {t(NAV_LABEL_KEYS[item.label] || item.label)}
                      {item.sub && (
                        <svg className={`w-2 h-2 flex-shrink-0 transition-transform ${openDrop === item.label ? 'rotate-180' : ''}`}
                          fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M19 9l-7 7-7-7" />
                        </svg>
                      )}
                    </Link>
                    {item.sub && openDrop === item.label && <DropdownPanel item={item} {...dropProps} />}
                  </div>
                </li>
              ))}
            </ul>
          </nav>

          {/* Right icons compact */}
          <div className="flex items-center gap-1 ml-auto flex-shrink-0">
            <ThemeToggle />
            <LanguageToggle />

            <IconBtn onClick={() => navigate('/favorites')} className="hidden lg:flex" style={{ color: textColor }}>
              <svg className="w-5 h-5"
                fill={favorites.length > 0 ? '#F97316' : 'none'}
                stroke={favorites.length > 0 ? '#F97316' : 'currentColor'}
                strokeWidth={2} viewBox="0 0 24 24">
                <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
              </svg>
              {favorites.length > 0 && (
                <span className="absolute -top-0.5 -right-0.5 bg-primary text-white w-4 h-4 flex items-center justify-center rounded-full font-bold" style={{ fontSize: 9 }}>
                  {favorites.length}
                </span>
              )}
            </IconBtn>

            <IconBtn onClick={() => setCartOpen(true)} className="hidden lg:flex" style={{ color: textColor }}>
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              {totalQty > 0 && (
                <span className="absolute -top-0.5 -right-0.5 bg-primary text-white w-4 h-4 flex items-center justify-center rounded-full font-bold" style={{ fontSize: 9 }}>
                  {totalQty}
                </span>
              )}
            </IconBtn>

            {/* Compact user menu */}
            <div className="relative" ref={compactUserMenuRef}>
              <button className="flex items-center gap-1 px-1 py-2"
                style={{ color: textColor, transition: 'color 0.15s' }}
                onMouseEnter={e => { if (!user) e.currentTarget.style.color = '#F97316' }}
                onMouseLeave={e => { if (!user) e.currentTarget.style.color = textColor }}
                onClick={() => user ? setUserMenu(p => !p) : onAuthOpen?.('login')}>
                {user ? (
                  <>
                    <UserAvatar size={9} fontSize={15} />
                    <span className="font-bold hidden lg:block max-w-[60px] truncate" style={{ fontSize: 13, color: '#F97316' }}>
                      {user.username || user.name}
                    </span>
                  </>
                ) : (
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" strokeWidth={1.8} viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                  </svg>
                )}
              </button>

              {user && userMenu && (
                <div className="absolute right-0 top-full mt-1 rounded-lg shadow-xl w-44 py-1 z-[200] border"
                  style={{ background: ddBg, borderColor: ddBorder }}>
                  {[
                    { labelKey: 'nav.myProfile', icon: '👤', path: '/profile' },
                    { labelKey: 'nav.myOrders',  icon: '📦', path: '/orders'  },
                  ].map(({ labelKey, icon, path }) => (
                    <button key={path}
                      className="w-full flex items-center gap-2 px-3 py-2 font-semibold transition-colors text-left"
                      style={{ fontFamily: navFont, fontSize: 14, color: textColor }}
                      onMouseEnter={e => { e.currentTarget.style.color = '#F97316'; e.currentTarget.style.background = ddHover }}
                      onMouseLeave={e => { e.currentTarget.style.color = textColor; e.currentTarget.style.background = 'transparent' }}
                      onClick={() => { setUserMenu(false); navigate(path) }}>
                      {icon} {t(labelKey)}
                    </button>
                  ))}
                  <hr style={{ borderColor: ddBorder, margin: '2px 0' }} />
                  <button
                    onClick={() => { logout(); setUserMenu(false) }}
                    className="w-full text-left px-3 py-2 text-red-500 font-semibold"
                    style={{ fontFamily: navFont, fontSize: 14 }}
                    onMouseEnter={e => e.currentTarget.style.background = ddHover}
                    onMouseLeave={e => e.currentTarget.style.background = 'transparent'}>
                    🚪 {t('nav.logout')}
                  </button>
                </div>
              )}
            </div>

            <IconBtn onClick={() => setCartOpen(true)} className="md:hidden" style={{ color: textColor }}>
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              {totalQty > 0 && (
                <span className="absolute -top-0.5 -right-0.5 bg-primary text-white w-4 h-4 flex items-center justify-center rounded-full font-bold" style={{ fontSize: 9 }}>
                  {totalQty}
                </span>
              )}
            </IconBtn>

            <button className="lg:hidden p-2" style={{ color: textColor, transition: 'color 0.15s' }}
              onMouseEnter={e => e.currentTarget.style.color = '#F97316'}
              onMouseLeave={e => e.currentTarget.style.color = textColor}
              onClick={() => setMobileOpen(true)}>
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      {/* ══════════ FULL BAR (not scrolled) ════════════════════════════════ */}
      <div style={{ display: scrolled ? 'none' : 'block' }}>

        {/* ── TOP BAR ── */}
        <div style={{ borderBottom: `1px solid ${navBorder}` }}>
          <div className="max-w-[1280px] mx-auto px-4 flex items-center py-2 gap-3">
            <Link to="/" className="flex-shrink-0">
              <img src={logo} alt="Tronmatix" className="object-contain" style={{ height: 90 }} />
            </Link>

            <div className="hidden md:flex flex-col items-start ml-2 flex-shrink-0">
              <div className="flex items-center gap-2">
                <svg className="w-5 h-5 flex-shrink-0" fill="none" stroke="#F97316" strokeWidth={2} viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <div>
                  <div style={{ fontFamily: navFont, fontSize: 13, color: subTextColor, fontWeight: 700, letterSpacing: isKhmer ? 0 : 0.5 }}>
                    {isKhmer ? 'ទំនាក់ទំនងយើង' : 'Call us now'}
                  </div>
                  <div style={{ fontFamily: navFont, fontSize: 13, fontWeight: 700, color: textColor, letterSpacing: isKhmer ? 0 : 0.5  }}>{isKhmer ? '096 733 3725 / 077 711 126' : '096 733 3725 / 077 711 126'}</div>
                </div>
              </div>
              <div className="flex gap-3 mt-1.5 ml-7">
                <a href="https://www.facebook.com/TronmatixComputer?_rdc=1&_rdr#" style={{ color: subTextColor, transition: 'color 0.15s' }} onMouseEnter={e => e.currentTarget.style.color='#F97316'} onMouseLeave={e => e.currentTarget.style.color=subTextColor}><FacebookIcon /></a>
                <a href="https://t.me/+VZScFi_U95PsFk0M" style={{ color: subTextColor, transition: 'color 0.15s' }} onMouseEnter={e => e.currentTarget.style.color='#F97316'} onMouseLeave={e => e.currentTarget.style.color=subTextColor}><TelegramIcon /></a>
                <a href="https://www.tiktok.com/@tronmatixcomputer" style={{ color: subTextColor, transition: 'color 0.15s' }} onMouseEnter={e => e.currentTarget.style.color='#F97316'} onMouseLeave={e => e.currentTarget.style.color=subTextColor}><TikTokIcon /></a>
              </div>
            </div>

            <form onSubmit={handleSearch} className="flex-1 hidden md:flex mx-3">
              <div className="relative w-full max-w-md">
                <input value={search} onChange={e => setSearch(e.target.value)}
                  placeholder={t('nav.search')}
                  className="w-full rounded-full px-5 py-2.5 pr-11 focus:outline-none transition-colors"
                  style={{ fontFamily: navFont, fontSize: 15, background: inputBg, border: `1px solid ${inputBorder}`, color: textColor }}
                />
                <button type="submit" className="absolute right-3 top-1/2 -translate-y-1/2" style={{ color: subTextColor }}
                  onMouseEnter={e => e.currentTarget.style.color='#F97316'}
                  onMouseLeave={e => e.currentTarget.style.color=subTextColor}>
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </button>
              </div>
            </form>

            <div className="flex items-center gap-1.5 ml-auto">
              <ThemeToggle />
              <LanguageToggle />

              <IconBtn onClick={() => navigate('/favorites')} className="hidden md:flex" style={{ color: textColor }}>
                <svg className="w-6 h-6"
                  fill={favorites.length > 0 ? '#F97316' : 'none'}
                  stroke={favorites.length > 0 ? '#F97316' : 'currentColor'}
                  strokeWidth={2} viewBox="0 0 24 24">
                  <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                </svg>
                {favorites.length > 0 && (
                  <span className="absolute -top-1 -right-1 bg-primary text-white w-5 h-5 flex items-center justify-center rounded-full font-bold" style={{ fontSize: 11 }}>
                    {favorites.length}
                  </span>
                )}
              </IconBtn>

              <IconBtn onClick={() => setCartOpen(true)} style={{ color: textColor }}>
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                {totalQty > 0 && (
                  <span className="absolute -top-1 -right-1 bg-primary text-white w-5 h-5 flex items-center justify-center rounded-full font-bold" style={{ fontSize: 11 }}>
                    {totalQty}
                  </span>
                )}
              </IconBtn>

              <UserDropdown menuRef={userMenuRef} />

              <button className="lg:hidden p-2" style={{ color: textColor, transition: 'color 0.15s' }}
                onMouseEnter={e => e.currentTarget.style.color = '#F97316'}
                onMouseLeave={e => e.currentTarget.style.color = textColor}
                onClick={() => setMobileOpen(true)}>
                <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        {/* ── DESKTOP NAV BAR ── */}
        <nav className="hidden lg:block" style={{ background: navBg, borderBottom: `1px solid ${navBorder}` }}>
          <div className="max-w-[1280px] mx-auto px-2 flex items-center">
            <ul className="flex items-center justify-center flex-1 flex-wrap">
              {navItems.map(item => (
                <li key={item.label} className="relative">
                  <div className="flex items-center"
                    onMouseEnter={() => item.sub && setOpenDrop(item.label)}
                    onMouseLeave={() => { setOpenDrop(null); setOpenSub(null) }}>
                    <Link
                      to={item.categories ? `${item.path}?cats=${item.categories.map(c => encodeURIComponent(c)).join(',')}` : item.path}
                      className="flex items-center gap-0.5 border-b-2 border-transparent whitespace-nowrap font-bold tracking-wide"
                      style={{
                        fontFamily: navbFont,
                        fontSize: 'clamp(14px, 1.5vw, 18px)',
                        fontWeight: 700,
                        padding: 'clamp(10px, 1.2vw, 20px) clamp(6px, 0.8vw, 18px)',
                        color: textColor,
                        letterSpacing: isKhmer ? 0 : undefined,
                        transition: 'color 0.15s, border-color 0.15s',
                      }}
                      onMouseEnter={e => { e.currentTarget.style.color = '#F97316'; e.currentTarget.style.borderBottomColor = '#F97316' }}
                      onMouseLeave={e => { e.currentTarget.style.color = textColor; e.currentTarget.style.borderBottomColor = 'transparent' }}
                      onClick={() => { setOpenDrop(null); setOpenSub(null) }}>
                      {t(NAV_LABEL_KEYS[item.label] || item.label)}
                      {item.sub && (
                        <svg className={`w-3 h-3 flex-shrink-0 ml-0.5 transition-transform ${openDrop === item.label ? 'rotate-180' : ''}`}
                          fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M19 9l-7 7-7-7" />
                        </svg>
                      )}
                    </Link>
                    {item.sub && openDrop === item.label && <DropdownPanel item={item} {...dropProps} />}
                  </div>
                </li>
              ))}
            </ul>
          </div>
        </nav>
      </div>

      {/* ══════════ MOBILE DRAWER ═══════════════════════════════════════════ */}
      {mobileOpen && (
        <div className="fixed inset-0 bg-black/50 z-[80]" onClick={() => setMobileOpen(false)} />
      )}
      <div ref={drawerRef}
        className="fixed top-0 right-0 h-full w-[300px] z-[90] flex flex-col"
        style={{
          background: drawerBg,
          transform: mobileOpen ? 'translateX(0)' : 'translateX(100%)',
          transition: 'transform 0.28s cubic-bezier(0.4,0,0.2,1)',
          overflowY: 'auto',
        }}>

        {/* Drawer header */}
        <div className="flex-shrink-0" style={{ borderBottom: `1px solid ${drawerBorder}`, background: drawerBg }}>
          {/* Top row: user info OR login buttons + right controls */}
          <div className="flex items-center gap-2 px-4 py-3" style={{ flexWrap: 'nowrap', minWidth: 0 }}>
            {/* Left side: user info or login buttons */}
            <div className="flex items-center gap-2 min-w-0 flex-1">
              {user ? (
                <>
                  <div className="w-10 h-10 rounded-full flex-shrink-0 overflow-hidden"
                    style={{ border: '2.5px solid #F97316', background: '#F97316' }}>
                    {user.avatar ? (
                      <img src={user.avatar} alt={user.username}
                        style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }} />
                    ) : (
                      <div style={{ width: '100%', height: '100%', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', fontWeight: 900, fontSize: 18 }}>
                        {(user.username || user.name || 'U').charAt(0).toUpperCase()}
                      </div>
                    )}
                  </div>
                  <div className="min-w-0">
                    <div className="font-black truncate" style={{ fontSize: 15, color: '#F97316' }}>{user.username || user.name}</div>
                    <div style={{ fontFamily: navFont, fontSize: 11, color: subTextColor }}>
                      {isKhmer ? 'បានចូល' : 'Logged in'}
                    </div>
                  </div>
                </>
              ) : (
                <div className="flex gap-2 flex-wrap">
                  <button onClick={() => { onAuthOpen('login'); setMobileOpen(false) }}
                    className="bg-primary text-white px-3 py-1.5 rounded-lg font-bold flex-shrink-0"
                    style={{ fontFamily: navFont, fontSize: 13 }}>
                    {t('nav.login').toUpperCase()}
                  </button>
                  <button onClick={() => { onAuthOpen('register'); setMobileOpen(false) }}
                    className="border-2 border-primary px-3 py-1.5 rounded-lg font-bold flex-shrink-0"
                    style={{ fontFamily: navFont, fontSize: 13, color: '#F97316' }}>
                    {t('nav.register').toUpperCase()}
                  </button>
                </div>
              )}
            </div>

            {/* Right controls: theme + lang + close — always in a row, never pushed off screen */}
            <div className="flex items-center gap-1.5 flex-shrink-0 ml-auto">
              <ThemeToggle />
              <LanguageToggle />
              <button
                onClick={() => setMobileOpen(false)}
                className="w-8 h-8 rounded-full flex items-center justify-center font-bold flex-shrink-0"
                style={{ background: dark ? '#374151' : '#f3f4f6', color: textColor, fontSize: 18 }}>
                ✕
              </button>
            </div>
          </div>
        </div>

        {/* Mobile search */}
        <form onSubmit={handleSearch} className="px-4 py-3 flex-shrink-0" style={{ borderBottom: `1px solid ${drawerBorder}` }}>
          <div className="relative">
            <input value={search} onChange={e => setSearch(e.target.value)}
              placeholder={t('nav.search')}
              className="w-full rounded-full px-5 py-2.5 pr-11 focus:outline-none"
              style={{ fontFamily: navbFont, fontSize: 15, background: inputBg, border: `1px solid ${inputBorder}`, color: textColor }} />
            <button type="submit" className="absolute right-4 top-1/2 -translate-y-1/2" style={{ color: subTextColor }}>
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </button>
          </div>
        </form>

        {/* Nav items */}
        <div className="flex-1 overflow-y-auto">
          {navItems.map(item => (
            <div key={item.label} style={{ borderBottom: `1px solid ${drawerBorder}` }}>
              <div className="flex items-center justify-between px-4 py-3.5 select-none">
                <span
                  className="font-bold tracking-wide cursor-pointer flex-1"
                  style={{ fontFamily: navbFont, fontSize: 16, color: textColor, transition: 'color 0.15s', letterSpacing: isKhmer ? 0 : undefined }}
                  onMouseEnter={e => e.currentTarget.style.color='#F97316'}
                  onMouseLeave={e => e.currentTarget.style.color=textColor}
                  onClick={() => {
                    const dest = item.categories
                      ? `${item.path}?cats=${item.categories.map(c => encodeURIComponent(c)).join(',')}`
                      : item.path
                    navigate(dest)
                    setMobileOpen(false)
                  }}>
                  {t(NAV_LABEL_KEYS[item.label] || item.label)}
                </span>
                {item.sub
                  ? <button className="p-1.5 rounded" style={{ background: 'transparent' }}
                      onClick={() => setMobileSub(mobileSub === item.label ? null : item.label)}>
                      <svg className={`w-4 h-4 flex-shrink-0 transition-transform duration-200 ${mobileSub === item.label ? 'rotate-180' : ''}`}
                        fill="none" stroke={textColor} viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                      </svg>
                    </button>
                  : <svg className="w-4 h-4 flex-shrink-0" fill="none" stroke="#F97316" strokeWidth={2} viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                }
              </div>
              {item.sub && mobileSub === item.label && (
                <div className="pb-2" style={{ background: drawerSubBg }}>
                  <Link
                    to={item.categories ? `${item.path}?cats=${item.categories.map(c => encodeURIComponent(c)).join(',')}` : item.path}
                    className="block px-8 py-2 font-bold text-primary border-b mb-1"
                    style={{ fontSize: 14, borderColor: drawerBorder }}
                    onClick={() => setMobileOpen(false)}>
                    ALL {item.label}
                  </Link>
                  {item.sub.map(sub => {
                    const isObj  = typeof sub === 'object'
                    const label  = isObj ? sub.label : sub
                    const path   = `/category/${slugify(item.label)}/${slugify(label)}`
                    return (
                      <Link key={label} to={path}
                        className="block px-8 py-1.5 font-semibold"
                        style={{ fontSize: 14, color: subTextColor, transition: 'color 0.15s' }}
                        onMouseEnter={e => e.currentTarget.style.color='#F97316'}
                        onMouseLeave={e => e.currentTarget.style.color=subTextColor}
                        onClick={() => setMobileOpen(false)}>{label}</Link>
                    )
                  })}
                </div>
              )}
            </div>
          ))}
        </div>

        {/* Profile actions at bottom */}
        {user && (
          <div className="flex-shrink-0 p-4" style={{ borderTop: `1px solid ${drawerBorder}` }}>
            <div className="flex flex-col gap-1.5 mb-3">
              {[
                { to: '/profile',   labelKey: 'nav.myProfile',   icon: '👤' },
                { to: '/orders',    labelKey: 'nav.myOrders',    icon: '📦' },
                { to: '/favorites', labelKey: 'nav.myFavorites', icon: '❤️' },
              ].map(({ to, labelKey, icon }) => (
                <Link key={to} to={to} onClick={() => setMobileOpen(false)}
                  className="flex items-center gap-2 px-3 py-2 rounded-lg font-bold"
                  style={{ fontFamily: navFont, fontSize: 14, color: textColor, border: `1px solid ${drawerBorder}`, transition: 'background 0.15s' }}
                  onMouseEnter={e => e.currentTarget.style.background = ddHover}
                  onMouseLeave={e => e.currentTarget.style.background = 'transparent'}>
                  {icon} {t(labelKey)}
                </Link>
              ))}
            </div>
            <button onClick={() => { logout(); setMobileOpen(false) }}
              className="w-full text-red-500 font-bold border border-red-300 py-2 rounded-lg"
              style={{ fontFamily: navFont, fontSize: 15 }}>
              🚪 {t('nav.logout')}
            </button>
          </div>
        )}
      </div>

      <style>{`
        @keyframes slideInRight {
          from { transform: translateX(100%) }
          to   { transform: translateX(0) }
        }
      `}</style>
    </header>
  )
}

function FacebookIcon() {
  return <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
}
function TelegramIcon() {
  return <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
}
function TikTokIcon() {
  return <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.27 8.27 0 004.84 1.55V6.78a4.85 4.85 0 01-1.07-.09z"/></svg>
}