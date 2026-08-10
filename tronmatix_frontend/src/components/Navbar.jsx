import { useState, useRef, useEffect, useMemo } from 'react'
import { createPortal } from 'react-dom'
import { Link, useNavigate, useLocation } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { useCart } from '../context/CartContext'
import { useFavorites } from '../context/FavoritesContext'
import { useTheme } from '../context/ThemeContext'
import { useLang } from '../context/LanguageContext'
import { useMobileMenu } from '../context/MobileMenuContext'
import { useCategories } from '../context/CategoryContext'
import LogoutConfirmModal from './LogoutConfirmModal'
import logo from '../assets/logo.png'

const slugify = s => s.toLowerCase().replace(/\s+/g, '-')

const brandPath = (item, brand) =>
  `${item.path}/${slugify(brand)}?cats=${encodeURIComponent(brand)}`

/* ── Desktop dropdown panel (4-level: Category → MainCate → SubCate → Brand) ─ */
function DropdownPanel({ item, openDrop, openSub, openSubSub, setOpenDrop, setOpenSub, setOpenSubSub, isKhmer, dark, expandedCatNames }) {
  const closeTimeoutRef = useRef(null)

  const clearClose = () => {
    if (closeTimeoutRef.current) {
      clearTimeout(closeTimeoutRef.current)
      closeTimeoutRef.current = null
    }
  }

  const scheduleCloseTop = () => {
    clearClose()
    closeTimeoutRef.current = setTimeout(() => {
      setOpenDrop(null)
      setOpenSub(null)
      setOpenSubSub(null)
    }, 150)
  }

  const scheduleCloseSub = () => {
    clearClose()
    closeTimeoutRef.current = setTimeout(() => {
      setOpenSub(null)
      setOpenSubSub(null)
    }, 150)
  }

  const scheduleCloseSubSub = () => {
    clearClose()
    closeTimeoutRef.current = setTimeout(() => {
      setOpenSubSub(null)
    }, 150)
  }

  useEffect(() => {
    return () => {
      if (closeTimeoutRef.current) {
        clearTimeout(closeTimeoutRef.current)
      }
    }
  }, [])

  const hasNested = item.sub && item.sub.length > 0 && item.sub[0].sub
  const dropFont = isKhmer ? 'Kh_Jrung_Thom, sans-serif' : 'Rajdhani, sans-serif'
  return (
    <div className="absolute top-full left-0 shadow-2xl z-[200] py-2 min-w-[210px]"
      style={{
        background: dark ? 'rgba(26, 26, 26, 0.85)' : 'rgba(255, 255, 255, 0.85)',
        backdropFilter: 'blur(12px) saturate(180%)',
        WebkitBackdropFilter: 'blur(12px) saturate(180%)',
        border: `1px solid ${dark ? 'rgba(249,115,22,0.3)' : 'rgba(249,115,22,0.2)'}`,
      }}
      onMouseEnter={() => { clearClose(); setOpenDrop(item.label) }}
      onMouseLeave={scheduleCloseTop}>
      <Link
        to={(() => { const cats = expandedCatNames[item.path.split('/').pop()] || item.categories || []; return cats.length ? `${item.path}?cats=${cats.map(c => encodeURIComponent(c)).join(',')}` : item.path })()}
        className="block px-4 py-2 font-bold text-primary border-b border-[#333] mb-1 tracking-wider"
        style={{ fontFamily: dropFont, fontSize: 15, letterSpacing: isKhmer ? 0 : undefined, color: dark ? '#F97316' : '#1f2937' }}
        onClick={() => { setOpenDrop(null); setOpenSub(null); setOpenSubSub(null) }}>
        ALL {item.label}
      </Link>

      {hasNested
        ? item.sub.map(mc => (
            <div key={mc.label} className="relative"
              onMouseEnter={() => { clearClose(); setOpenSub(mc.label) }}
              onMouseLeave={scheduleCloseSub}>
              <div className="flex items-center justify-between transition-colors"
                style={{ backgroundColor: openSub === mc.label ? (dark ? 'rgba(249,115,22,0.1)' : 'rgba(249,115,22,0.05)') : 'transparent' }}
                onMouseEnter={() => { clearClose(); setOpenSub(mc.label) }}
                onMouseLeave={scheduleCloseSub}>
                <Link to={`${item.path}/${slugify(mc.label)}`}
                  className="flex-1 px-4 py-2.5 font-bold hover:text-primary tracking-wider"
                  style={{ fontFamily: dropFont, fontSize: 16, letterSpacing: isKhmer ? 0 : undefined, color: dark ? '#d1d5db' : '#374151' }}
                  onClick={() => { setOpenDrop(null); setOpenSub(null); setOpenSubSub(null) }}>
                  {mc.label}
                </Link>
                {/* Arrow only when this main category actually has sub-categories */}
                {mc.sub && mc.sub.length > 0 && (
                  <span className="pr-3 text-gray-500">
                    <svg className="w-3 h-3" fill="none" stroke="currentColor" strokeWidth={2.5} viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                  </span>
                )}
              </div>

              {openSub === mc.label && mc.sub && (
                <div className="absolute left-full top-0 shadow-2xl z-[210] min-w-[220px] py-2"
                  style={{
                    background: dark ? 'rgba(17, 17, 17, 0.85)' : 'rgba(249, 250, 251, 0.85)',
                    backdropFilter: 'blur(12px) saturate(180%)',
                    WebkitBackdropFilter: 'blur(12px) saturate(180%)',
                    border: `1px solid ${dark ? 'rgba(249,115,22,0.3)' : 'rgba(249,115,22,0.2)'}`,
                  }}
                  onMouseEnter={() => { clearClose(); setOpenSub(mc.label) }}
                  onMouseLeave={scheduleCloseSub}>
                  <div className="px-4 py-1 text-primary font-black tracking-widest border-b border-[#333] mb-1" style={{ fontSize: 12 }}>
                    {mc.label}
                  </div>
                  {mc.sub.map(sc => (
                    <div key={sc.label} className="relative"
                      onMouseEnter={() => { clearClose(); setOpenSubSub(sc.label) }}
                      onMouseLeave={scheduleCloseSubSub}>
                      <div className="flex items-center justify-between transition-colors"
                        style={{ backgroundColor: openSubSub === sc.label ? (dark ? 'rgba(249,115,22,0.1)' : 'rgba(249,115,22,0.05)') : 'transparent' }}
                        onMouseEnter={() => { clearClose(); setOpenSubSub(sc.label) }}
                        onMouseLeave={scheduleCloseSubSub}>
                        <Link to={`${item.path}/${slugify(mc.label)}/${slugify(sc.label)}`}
                          className="flex-1 px-4 py-2.5 font-bold hover:text-primary tracking-wider"
                          style={{ fontFamily: dropFont, fontSize: 16, letterSpacing: isKhmer ? 0 : undefined, color: dark ? '#d1d5db' : '#374151' }}
                          onClick={() => { setOpenDrop(null); setOpenSub(null); setOpenSubSub(null) }}>
                          {sc.label}
                        </Link>
                        {sc.brands && sc.brands.length > 0 && (
                          <span className="pr-3 text-gray-500">
                            <svg className="w-3 h-3" fill="none" stroke="currentColor" strokeWidth={2.5} viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                          </span>
                        )}
                      </div>

                      {openSubSub === sc.label && sc.brands && (
                        <div className="absolute left-full top-0 shadow-2xl z-[220] min-w-[220px] py-2"
                          style={{
                            background: dark ? 'rgba(17, 17, 17, 0.85)' : 'rgba(249, 250, 251, 0.85)',
                            backdropFilter: 'blur(12px) saturate(180%)',
                            WebkitBackdropFilter: 'blur(12px) saturate(180%)',
                            border: `1px solid ${dark ? 'rgba(249,115,22,0.3)' : 'rgba(249,115,22,0.2)'}`,
                          }}
                          onMouseEnter={() => { clearClose(); setOpenSubSub(sc.label) }}
                          onMouseLeave={scheduleCloseSubSub}>
                          <div className="px-4 py-1 text-primary font-black tracking-widest border-b border-[#333] mb-1" style={{ fontSize: 12 }}>
                            {sc.label}
                          </div>
                          {sc.brands.map(brand => (
                            <Link key={brand}
                              to={`${item.path}/${slugify(mc.label)}/${slugify(sc.label)}?cats=${encodeURIComponent(brand)}`}
                              className="block px-4 py-2 hover:text-primary tracking-wider transition-colors font-bold"
                              style={{
                                fontFamily: dropFont,
                                fontSize: 16,
                                letterSpacing: isKhmer ? 0 : undefined,
                                color: dark ? '#d1d5db' : '#374151',
                                backgroundColor: 'transparent'
                              }}
                              onMouseEnter={(e) => e.target.style.backgroundColor = dark ? 'rgba(249,115,22,0.1)' : 'rgba(249,115,22,0.05)'}
                              onMouseLeave={(e) => e.target.style.backgroundColor = 'transparent'}
                              onClick={() => { setOpenDrop(null); setOpenSub(null); setOpenSubSub(null) }}>
                              {brand}
                            </Link>
                          ))}
                        </div>
                      )}
                    </div>
                  ))}
                </div>
              )}
            </div>
          ))
        : item.sub.map(sub => {
            // Flattened single-main categories (e.g. PC BUILD) surface their
            // sub-categories here as { label, brands } objects; legacy nav
            // strings still work too.
            const subLabel = typeof sub === 'string' ? sub : sub.label
            const subBrands = typeof sub === 'string' ? [] : (sub.brands || [])
            const hasFlyout = subBrands.length > 0

            const link = (
              <Link to={`${item.path}/${slugify(subLabel)}`}
                className="flex-1 px-4 py-2.5 font-bold hover:text-primary tracking-wider transition-colors"
                style={{
                  fontFamily: dropFont,
                  fontSize: 16,
                  letterSpacing: isKhmer ? 0 : undefined,
                  color: dark ? '#d1d5db' : '#374151',
                  backgroundColor: 'transparent'
                }}
                onMouseEnter={(e) => e.target.style.backgroundColor = dark ? 'rgba(249,115,22,0.1)' : 'rgba(249,115,22,0.05)'}
                onMouseLeave={(e) => e.target.style.backgroundColor = 'transparent'}
                onClick={() => { setOpenDrop(null); setOpenSub(null); setOpenSubSub(null) }}>
                {subLabel}
              </Link>
            )

            return (
              <div key={subLabel} className="relative flex items-center justify-between transition-colors"
                onMouseEnter={() => { clearClose(); if (hasFlyout) setOpenSub(subLabel) }}
                onMouseLeave={scheduleCloseSub}>
                {link}
                {/* Flyout arrow only when this item has brands to show */}
                {hasFlyout && (
                  <span className="pr-3 text-gray-500">
                    <svg className="w-3 h-3" fill="none" stroke="currentColor" strokeWidth={2.5} viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                  </span>
                )}
                {openSub === subLabel && hasFlyout && (
                  <div className="absolute left-full top-0 shadow-2xl z-[220] min-w-[220px] py-2"
                    style={{
                      background: dark ? 'rgba(17, 17, 17, 0.85)' : 'rgba(249, 250, 251, 0.85)',
                      backdropFilter: 'blur(12px) saturate(180%)',
                      WebkitBackdropFilter: 'blur(12px) saturate(180%)',
                      border: `1px solid ${dark ? 'rgba(249,115,22,0.3)' : 'rgba(249,115,22,0.2)'}`,
                    }}
                    onMouseEnter={() => { clearClose(); setOpenSub(subLabel) }}
                    onMouseLeave={scheduleCloseSub}>
                    <div className="px-4 py-1 text-primary font-black tracking-widest border-b border-[#333] mb-1" style={{ fontSize: 12 }}>
                      {subLabel}
                    </div>
                    {subBrands.map(brand => (
                      <Link key={brand}
                        to={brandPath(item, brand)}
                        className="block px-4 py-2 hover:text-primary tracking-wider transition-colors font-bold"
                        style={{
                          fontFamily: dropFont,
                          fontSize: 16,
                          letterSpacing: isKhmer ? 0 : undefined,
                          color: dark ? '#d1d5db' : '#374151',
                          backgroundColor: 'transparent'
                        }}
                        onMouseEnter={(e) => e.target.style.backgroundColor = dark ? 'rgba(249,115,22,0.1)' : 'rgba(249,115,22,0.05)'}
                        onMouseLeave={(e) => e.target.style.backgroundColor = 'transparent'}
                        onClick={() => { setOpenDrop(null); setOpenSub(null); setOpenSubSub(null) }}>
                        {brand}
                      </Link>
                    ))}
                  </div>
                )}
              </div>
            )
          })
      }
    </div>
  )
}

export default function Navbar({ onAuthOpen }) {
  const { categories: apiCategories, loading, error } = useCategories()

  const [openDrop, setOpenDrop] = useState(null)
  const [openSub, setOpenSub] = useState(null)
  const [openSubSub, setOpenSubSub] = useState(null)
  const { isMobileMenuOpen: mobileOpen, setIsMobileMenuOpen } = useMobileMenu()
  const [mobileSub, setMobileSub] = useState(null)
  const [mobileSubItem, setMobileSubItem] = useState(null)
  const [mobileSubSub, setMobileSubSub] = useState(null)
  const [search, setSearch] = useState('')
  const [userMenu, setUserMenu] = useState(false)
  const [scrolled, setScrolled] = useState(false)
  const [hoveredNav, setHoveredNav] = useState(null)
  const [logoutOpen, setLogoutOpen] = useState(false)
  const { user, logout, ready } = useAuth()
  const { items, setCartOpen } = useCart()
  const { favorites } = useFavorites()
  const { dark } = useTheme()
  const { t, isKhmer } = useLang()
  const navigate = useNavigate()
  const location = useLocation()
  const headerRef = useRef(null)
  const userMenuRef = useRef(null)
  const compactUserMenuRef = useRef(null)
  const drawerRef = useRef(null)

  const totalQty = items.reduce((s, i) => s + i.qty, 0)

  const navBg = dark ? '#111827' : '#ffffff'
  const navBorder = dark ? '#1f2937' : '#e5e7eb'
  const textColor = dark ? '#f9fafb' : '#1f2937'
  const subTextColor = dark ? '#9ca3af' : '#6b7280'
  const inputBg = dark ? '#1f2937' : '#ffffff'
  const inputBorder = dark ? '#374151' : '#d1d5db'
  const ddBg = dark ? '#1f2937' : '#ffffff'
  const ddBorder = dark ? '#374151' : '#e5e7eb'
  const ddHover = dark ? '#374151' : '#f3f4f6'
  const drawerBg = dark ? '#111827' : '#ffffff'
  const drawerBorder = dark ? '#1f2937' : '#e5e7eb'
  const drawerSubBg = dark ? '#0f172a' : '#f1f5f9'

  // Khmer font for nav links when in Khmer mode
  const navFont = isKhmer
    ? 'Kh_Jrung_Thom, Rajdhani, sans-serif'
    : 'Rajdhani, sans-serif'
  const navbFont = isKhmer
    ? 'Kdam Thmor Pro, Rajdhani, sans-serif'
    : 'Rajdhani, sans-serif'

  /* ── Build navItems from API tree ──────────────────────────────────────── */
  const navItems = useMemo(() => {
    const items = [
      { label: 'HOME', path: '/' },
    ]

    if (error || !apiCategories.length) {
      return items
    }

    apiCategories.forEach(cat => {
      const mainCates = cat.main_categories || []
      const mainNames = mainCates.map(mc => mc.name)

      // Each main category → { label, sub: [subCate], brands }
      const mainItems = mainCates.map(mc => {
        const subCates = mc.sub_categories || []
        return {
          label: mc.name,
          sub: subCates.map(sc => {
            const raw = sc.brands
            const brandList = Array.isArray(raw)
              ? raw.map(b => b.name)
              : (typeof raw === 'string' ? raw.split(',').map(s => s.trim()).filter(Boolean) : [])
            return { label: sc.name, brands: brandList }
          }),
        }
      })

      let sub
      if (mainItems.length === 1 && mainItems[0].label === cat.name) {
        sub = mainItems[0].sub
      } else {
        sub = mainItems
      }

      if (
        sub.length === 1 &&
        typeof sub[0] === 'object' &&
        sub[0].label === 'GENERAL' &&
        (sub[0].brands || []).length > 0
      ) {
        sub = sub[0].brands.map(name => ({ label: name }))
      }

      items.push({
        label: cat.name,
        path: `/category/${cat.slug}`,
        categories: mainNames,
        sub,
      })
    })

    items.push({ label: 'CONTACT US', path: '/contact' })
    return items
  }, [apiCategories, error])

  /* ── Expanded category names for "ALL {label}" links ─────────── */
  const expandedCatNames = useMemo(() => {
    const map = {}
    ;(apiCategories || []).forEach(cat => {
      const names = []
      ;(cat.main_categories || []).forEach(mc => {
        if (mc.name) names.push(mc.name)
        const scs = mc.sub_categories || []
        scs.forEach(sc => {
          if (sc.name) names.push(sc.name)
          const brands = Array.isArray(sc.brands)
            ? sc.brands.map(b => b.name).filter(b => b && b !== 'TBD')
            : (typeof sc.brands === 'string' ? sc.brands.split(',').map(s => s.trim()).filter(s => s && s !== 'TBD') : [])
          if (brands.length) names.push(...brands)
        })
      })
      map[cat.slug] = [...new Set(names.filter(Boolean))]
    })
    return map
  }, [apiCategories])

  /* ── Dynamic NAV_LABEL_KEYS (slug → existing i18n key, fallback to label) ─ */
  const SLUG_TO_I18N_KEY = {
    'pc-build': 'pcBuild',
    'monitor': 'monitor',
    'pc-part': 'pcPart',
    'pc-parts': 'pcParts',
    'hot-item': 'hotItem',
    'accessory': 'accessory',
    'table-/-chair': 'tableChair',
  }

  const NAV_LABEL_KEYS = useMemo(() => {
    const known = { 'HOME': 'nav.home', 'CONTACT US': 'nav.contactUs' }
    const map = {}
    navItems.forEach(item => {
      if (known[item.label]) {
        map[item.label] = known[item.label]
      } else if (SLUG_TO_I18N_KEY[slugify(item.label)]) {
        map[item.label] = `nav.${SLUG_TO_I18N_KEY[slugify(item.label)]}`
      } else {
        map[item.label] = item.label
      }
    })
    return map
  }, [navItems])

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 20)
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  useEffect(() => {
    const handler = e => {
      if (headerRef.current && !headerRef.current.contains(e.target)) {
        setOpenDrop(null); setOpenSub(null); setOpenSubSub(null)
      }
      const inFull = userMenuRef.current && userMenuRef.current.contains(e.target)
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

  useEffect(() => {
    setIsMobileMenuOpen(false)
    setMobileSub(null)
    setMobileSubItem(null)
    setMobileSubSub(null)
    document.body.style.overflow = ''
  }, [location.pathname])

  useEffect(() => {
    if (openDrop === null) {
      setHoveredNav(null)
    }
  }, [openDrop])

  const handleSearch = e => {
    e.preventDefault()
    const q = search.trim().toLowerCase()
    if (q) { navigate(`/category/search?q=${encodeURIComponent(q)}`); setSearch(''); setIsMobileMenuOpen(false) }
  }

  const isActive = (item) => {
    if (!item.path) return false
    if (item.path === '/') return location.pathname === '/'
    return location.pathname === item.path || location.pathname.startsWith(item.path + '/')
  }

  const dropProps = { openDrop, openSub, openSubSub, setOpenDrop, setOpenSub, setOpenSubSub, isKhmer, dark, expandedCatNames }

  /* ── Loading skeleton for nav ──────────────────────────────────────────── */
  const NavSkeleton = () => (
    <li className="flex-shrink-0 px-3">
      <div className="animate-pulse rounded" style={{ width: 70, height: 20, background: dark ? '#374151' : '#e5e7eb' }} />
    </li>
  )

  /* ── Theme Toggle ──────────────────────────────────────────────────────── */
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
            <path d="M12 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm5 10a5 5 0 11-10 0 5 5 0 0110 0zm4.95-1H21a1 1 0 110 2h-1.05A8.001 8.001 0 0113 20.95V22a1 1 0 11-2 0v-1.05A8.001 8.001 0 013.05 13H2a1 1 0 110-2h1.05A8.001 8.001 0 0111 3.05V2a1 1 0 112 0v1.05A8.001 8.001 0 0120.95 11z" />
          </svg>
        </span>
        <span className="absolute transition-all duration-300"
          style={{ opacity: dark ? 1 : 0, transform: dark ? 'scale(1) rotate(0deg)' : 'scale(0.4) rotate(-90deg)' }}>
          <svg className="w-4 h-4" fill="#F97316" viewBox="0 0 24 24">
            <path d="M21 12.79A9 9 0 1111.21 3a7 7 0 109.79 9.79z" />
          </svg>
        </span>
      </button>
    )
  }

  /* ── Language Toggle ────────────────────────────────────────────────────── */
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
          fontSize: 13, fontWeight: 800, letterSpacing: 0.5,
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

  /* ── Icon button ────────────────────────────────────────────────────────── */
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
            <span className="font-bold truncate" style={{ fontSize: 15, color: '#F97316', display: 'inline-flex', alignItems: 'center', gap: 3, maxWidth: 200 }}>
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
            { labelKey: 'nav.myOrders', icon: '📦', path: '/orders' },
          ].map(({ labelKey, icon, path }) => (
            <button key={path}
              className="w-full flex font-bold items-center gap-2 px-4 py-2 transition-colors text-left"
              style={{ fontFamily: navbFont, fontSize: 17, color: textColor }}
              onMouseEnter={e => { e.currentTarget.style.color = '#F97316'; e.currentTarget.style.background = ddHover }}
              onMouseLeave={e => { e.currentTarget.style.color = textColor; e.currentTarget.style.background = 'transparent' }}
              onClick={() => { setUserMenu(false); navigate(path) }}>
              {icon} {t(labelKey)}
            </button>
          ))}
          <hr style={{ borderColor: ddBorder, margin: '4px 0' }} />
          <button
            onClick={() => { setUserMenu(false); setLogoutOpen(true) }}
            className="w-full text-left px-4 font-bold py-2 text-red-500 transition-colors"
            style={{ fontFamily: navFont, fontSize: 17 }}
            onMouseEnter={e => e.currentTarget.style.background = ddHover}
            onMouseLeave={e => e.currentTarget.style.background = 'transparent'}>
            🚪 {t('nav.logout')}
          </button>
        </div>
      )}
    </div>
  )

  return (
    <>
      <header ref={headerRef} className="w-full sticky top-0 z-50 transition-all duration-300" style={{
        background: dark ? 'rgba(17, 24, 39, 0.75)' : 'rgba(255, 255, 255, 0.75)',
        backdropFilter: 'blur(12px) saturate(180%)',
        WebkitBackdropFilter: 'blur(12px) saturate(180%)',
        borderBottom: `1px solid ${dark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.05)'}`,
        boxShadow: scrolled ? '0 4px 30px rgba(0,0,0,0.1)' : 'none',
      }}>

        {/* ══════════ COMPACT BAR (scrolled) ══════════════════════════════════ */}
        <div style={{ display: scrolled ? 'block' : 'none', borderBottom: `1px solid ${navBorder}` }}>
          <div className="w-full max-w-[1550px] mx-auto px-4 lg:px-6 xl:px-8 flex items-center gap-1" style={{ height: 70 }}>
            <Link to="/" className="flex-shrink-0">
              <img src={logo} alt="Tronmatix" className="object-contain" style={{ height: 60 }} />
            </Link>

            <div className="flex flex-col gap-0.5 ml-2 flex-shrink-0">
              <a href="tel:0967333725" className="flex items-center gap-1">
                <svg className="w-4 h-4" fill="none" stroke="#F97316" strokeWidth={2} viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span className="font-bold text-[14px]" style={{ color: textColor }}>096 733 3725</span>
              </a>
              <a href="tel:077711126" className="flex items-center gap-1">
                <span className="w-4" /> {/* Spacer for alignment */}
                <span className="font-bold text-[14px]" style={{ color: textColor }}>077 711 126</span>
              </a>
            </div>

            {/* Inline nav tablet+ */}
            <nav className="hidden xl:flex items-center flex-1 min-w-0 justify-center">
              <ul className="flex items-center flex-wrap">
                {loading ? (
                  <>
                    <NavSkeleton /><NavSkeleton /><NavSkeleton /><NavSkeleton /><NavSkeleton />
                  </>
                ) : (
                  navItems.map(item => (
                    <li key={item.label} className="relative flex-shrink-0">
                      <div onMouseEnter={() => { if (item.sub && !error) setOpenDrop(item.label); setHoveredNav(item.label) }}
                        onMouseLeave={() => { setOpenDrop(null); setOpenSub(null); setOpenSubSub(null) }}>
                        <Link
                          to={(() => { const cats = expandedCatNames[item.path.split('/').pop()] || item.categories || []; return cats.length ? `${item.path}?cats=${cats.map(c => encodeURIComponent(c)).join(',')}` : item.path })()}
                          className="flex items-center gap-0.5 px-2 py-2 font-bold tracking-wide whitespace-nowrap"
                          style={{ fontFamily: navbFont, fontSize: isKhmer ? 18 : 20, color: (hoveredNav === item.label || isActive(item)) ? '#F97316' : textColor, transition: 'color 0.15s', letterSpacing: isKhmer ? 0 : undefined }}
                          onClick={() => { setOpenDrop(null); setOpenSub(null); setOpenSubSub(null); setHoveredNav(null) }}>
                          {t(NAV_LABEL_KEYS[item.label] || item.label)}
                          {item.sub && !error && (
                            <svg className={`w-2 h-2 flex-shrink-0 transition-transform ${openDrop === item.label ? 'rotate-180' : ''}`}
                              fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M19 9l-7 7-7-7" />
                            </svg>
                          )}
                        </Link>
                        {item.sub && !error && openDrop === item.label && <DropdownPanel item={item} {...dropProps} />}
                      </div>
                    </li>
                  ))
                )}
              </ul>
            </nav>

            {/* Right icons compact */}
            <div className="flex items-center gap-1 ml-auto flex-shrink-0">
              <div className="hidden md:flex items-center gap-1">
                <ThemeToggle />
                <LanguageToggle />
              </div>

              <IconBtn onClick={() => navigate('/bookmark')} className="hidden md:flex" style={{ color: textColor }}>
                <svg className="w-5 h-5"
                  fill={favorites.length > 0 ? '#F97316' : 'none'}
                  stroke={favorites.length > 0 ? '#F97316' : 'currentColor'}
                  strokeWidth={2} viewBox="0 0 24 24">
                  <path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z" />
                </svg>
                {favorites.length > 0 && (
                  <span className="absolute -top-0.5 -right-0.5 bg-primary text-white w-4 h-4 flex items-center justify-center rounded-full font-bold" style={{ fontSize: 9 }}>
                    {favorites.length}
                  </span>
                )}
              </IconBtn>

              <IconBtn onClick={() => setCartOpen(true)} className="hidden md:flex" style={{ color: textColor }}>
                <svg className="w-5 h-5" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
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
                      <span className="font-bold hidden lg:block truncate" style={{ fontSize: 16, color: '#F97316', display: 'inline-flex', alignItems: 'center', gap: 3, maxWidth: 200 }}>
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
                      { labelKey: 'nav.myOrders', icon: '📦', path: '/orders' },
                    ].map(({ labelKey, icon, path }) => (
                      <button key={path}
                        className="w-full flex items-center gap-2 px-3 py-2 font-semibold transition-colors text-left"
                        style={{ fontFamily: navbFont, fontSize: 17, color: textColor }}
                        onMouseEnter={e => { e.currentTarget.style.color = '#F97316'; e.currentTarget.style.background = ddHover }}
                        onMouseLeave={e => { e.currentTarget.style.color = textColor; e.currentTarget.style.background = 'transparent' }}
                        onClick={() => { setUserMenu(false); navigate(path) }}>
                        {icon} {t(labelKey)}
                      </button>
                    ))}
                    <hr style={{ borderColor: ddBorder, margin: '2px 0' }} />
                    <button
                      onClick={() => { setUserMenu(false); setLogoutOpen(true) }}
                      className="w-full text-left px-3 py-2 text-red-500 font-semibold"
                      style={{ fontFamily: navFont, fontSize: 17 }}
                      onMouseEnter={e => e.currentTarget.style.background = ddHover}
                      onMouseLeave={e => e.currentTarget.style.background = 'transparent'}>
                      🚪 {t('nav.logout')}
                    </button>
                  </div>
                )}
              </div>

              <IconBtn onClick={() => setCartOpen(true)} className="xl:hidden" style={{ color: textColor }}>
                <svg className="w-6 h-6" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                {totalQty > 0 && (
                  <span className="absolute -top-0.5 -right-0.5 bg-primary text-white w-4 h-4 flex items-center justify-center rounded-full font-bold" style={{ fontSize: 9 }}>
                    {totalQty}
                  </span>
                )}
              </IconBtn>

              <button className="xl:hidden p-2" style={{ color: textColor, transition: 'color 0.15s' }}
                onMouseEnter={e => e.currentTarget.style.color = '#F97316'}
                onMouseLeave={e => e.currentTarget.style.color = textColor}
                onClick={() => setIsMobileMenuOpen(true)}>
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
                <img src={logo} alt="Tronmatix" className="object-contain" style={{ height: 70 }} />
              </Link>

              <div className="hidden md:flex flex-col items-start ml-2 flex-shrink-0">
                <div className="flex items-center gap-2">
                  <svg className="w-5 h-5 flex-shrink-0" fill="none" stroke="#F97316" strokeWidth={2} viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  <div>
                    <div style={{ fontFamily: navbFont, fontSize: 13, color: subTextColor, fontWeight: 700, letterSpacing: isKhmer ? 0 : 0.5 }}>
                      {isKhmer ? 'ទំនាក់ទំនងយើង' : 'Call us now'}
                    </div>
                    <div style={{ fontFamily: navFont, fontSize: 13, fontWeight: 700, color: textColor, letterSpacing: isKhmer ? 0 : 0.5 }}>{isKhmer ? '096 733 3725 / 077 711 126' : '096 733 3725 / 077 711 126'}</div>
                  </div>
                </div>
                {/* <div className="flex gap-3 mt-1.5 ml-7">
                  <a href="https://www.facebook.com/TronmatixComputer?_rdc=1&_rdr#" style={{ color: subTextColor, transition: 'color 0.15s' }} onMouseEnter={e => e.currentTarget.style.color = '#F97316'} onMouseLeave={e => e.currentTarget.style.color = subTextColor}><FacebookIcon /></a>
                  <a href="https://t.me/+VZScFi_U95PsFk0M" style={{ color: subTextColor, transition: 'color 0.15s' }} onMouseEnter={e => e.currentTarget.style.color = '#F97316'} onMouseLeave={e => e.currentTarget.style.color = subTextColor}><TelegramIcon /></a>
                  <a href="https://www.tiktok.com/@tronmatixcomputer" style={{ color: subTextColor, transition: 'color 0.15s' }} onMouseEnter={e => e.currentTarget.style.color = '#F97316'} onMouseLeave={e => e.currentTarget.style.color = subTextColor}><TikTokIcon /></a>
                </div> */}
              </div>

              <form onSubmit={handleSearch} className="flex-1 hidden md:flex mx-3">
                <div className="relative w-full max-w-md">
                  <input value={search} onChange={e => setSearch(e.target.value)}
                    placeholder={t('nav.search')}
                    className="w-full rounded-full px-5 py-2.5 pr-11 focus:outline-none transition-colors"
                    style={{ fontFamily: navFont, fontSize: 15, fontWeight: 700, background: inputBg, border: `1px solid ${inputBorder}`, color: textColor }}
                  />
                  <button type="submit" className="absolute right-3 top-1/2 -translate-y-1/2" style={{ color: subTextColor }}
                    onMouseEnter={e => e.currentTarget.style.color = '#F97316'}
                    onMouseLeave={e => e.currentTarget.style.color = subTextColor}>
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                  </button>
                </div>
              </form>

              <div className="flex items-center gap-1.5 ml-auto">
                <div className="hidden md:flex items-center gap-1">
                  <ThemeToggle />
                  <LanguageToggle />
                </div>

                <IconBtn onClick={() => navigate('/bookmark')} className="hidden md:flex" style={{ color: textColor }}>
                  <svg className="w-6 h-6"
                    fill={favorites.length > 0 ? '#F97316' : 'none'}
                    stroke={favorites.length > 0 ? '#F97316' : 'currentColor'}
                    strokeWidth={2} viewBox="0 0 24 24">
                    <path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z" />
                  </svg>
                  {favorites.length > 0 && (
                    <span className="absolute -top-1 -right-1 bg-primary text-white w-5 h-5 flex items-center justify-center rounded-full font-bold" style={{ fontSize: 11 }}>
                      {favorites.length}
                    </span>
                  )}
                </IconBtn>

                <IconBtn onClick={() => setCartOpen(true)} style={{ color: textColor }}>
                  <svg className="w-6 h-6" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  {totalQty > 0 && (
                    <span className="absolute -top-1 -right-1 bg-primary text-white w-5 h-5 flex items-center justify-center rounded-full font-bold" style={{ fontSize: 11 }}>
                      {totalQty}
                    </span>
                  )}
                </IconBtn>

                <UserDropdown menuRef={userMenuRef} />

                <button className="xl:hidden p-2" style={{ color: textColor, transition: 'color 0.15s' }}
                  onMouseEnter={e => e.currentTarget.style.color = '#F97316'}
                  onMouseLeave={e => e.currentTarget.style.color = textColor}
                  onClick={() => setIsMobileMenuOpen(true)}>
                  <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          {/* ── DESKTOP NAV BAR ── */}
          <nav className="hidden xl:block" style={{ background: navBg, borderBottom: `1px solid ${navBorder}` }}>
            <div className="max-w-[1280px] mx-auto px-2 flex items-center">
              <ul className="flex items-center justify-center flex-1 flex-wrap">
                {loading ? (
                  <>
                    <NavSkeleton /><NavSkeleton /><NavSkeleton /><NavSkeleton /><NavSkeleton />
                  </>
                ) : (
                  navItems.map(item => (
                    <li key={item.label} className="relative">
                      <div className="flex items-center"
                        onMouseEnter={() => { if (item.sub && !error) setOpenDrop(item.label); setHoveredNav(item.label) }}
                        onMouseLeave={() => { setOpenDrop(null); setOpenSub(null); setOpenSubSub(null) }}>
                        <Link
                          to={(() => { const cats = expandedCatNames[item.path.split('/').pop()] || item.categories || []; return cats.length ? `${item.path}?cats=${cats.map(c => encodeURIComponent(c)).join(',')}` : item.path })()}
                          className="flex items-center gap-0.5 border-b-2 border-transparent whitespace-nowrap font-bold tracking-wide"
                          style={{
                            fontFamily: navbFont,
                            fontSize: isKhmer ? 20 : 21,
                            fontWeight: 700,
                            padding: 'clamp(10px, 1.2vw, 20px) clamp(6px, 0.8vw, 18px)',
                            color: (hoveredNav === item.label || isActive(item)) ? '#F97316' : textColor,
                            borderBottomColor: (hoveredNav === item.label || isActive(item)) ? '#F97316' : 'transparent',
                            letterSpacing: isKhmer ? 0 : undefined,
                            transition: 'color 0.15s, border-color 0.15s',
                          }}
                          onClick={() => { setOpenDrop(null); setOpenSub(null); setOpenSubSub(null); setHoveredNav(null) }}>
                          {t(NAV_LABEL_KEYS[item.label] || item.label)}
                          {item.sub && !error && (
                            <svg className={`w-3 h-3 flex-shrink-0 ml-0.5 transition-transform ${openDrop === item.label ? 'rotate-180' : ''}`}
                              fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M19 9l-7 7-7-7" />
                            </svg>
                          )}
                        </Link>
                        {item.sub && !error && openDrop === item.label && <DropdownPanel item={item} {...dropProps} />}
                      </div>
                    </li>
                  ))
                )}
              </ul>
            </div>
          </nav>
        </div>

      </header>

      {createPortal(
        <>
          {mobileOpen && (
            <div
              style={{
                position: 'fixed',
                top: 0,
                left: 0,
                right: 0,
                bottom: 0,
                background: 'rgba(0,0,0,0.55)',
                backdropFilter: 'blur(4px)',
                WebkitBackdropFilter: 'blur(4px)',
                zIndex: 80,
              }}
              onClick={() => setIsMobileMenuOpen(false)}
            />
          )}
          <div ref={drawerRef}
            style={{
              position: 'fixed',
              top: 0,
              right: 0,
              height: '100%',
              width: 300,
              zIndex: 90,
              display: 'flex',
              flexDirection: 'column',
              /* ── Glassmorphism ── */
              background: dark
                ? 'rgba(15, 23, 42, 0.82)'
                : 'rgba(255, 255, 255, 0.82)',
              backdropFilter: 'blur(20px) saturate(180%)',
              WebkitBackdropFilter: 'blur(20px) saturate(180%)',
              borderLeft: dark
                ? '1px solid rgba(249,115,22,0.25)'
                : '1px solid rgba(249,115,22,0.18)',
              boxShadow: dark
                ? '-8px 0 40px rgba(0,0,0,0.6), inset 1px 0 0 rgba(249,115,22,0.12)'
                : '-8px 0 40px rgba(0,0,0,0.18), inset 1px 0 0 rgba(249,115,22,0.10)',
              visibility: mobileOpen ? 'visible' : 'hidden',
              transform: mobileOpen ? 'translateX(0)' : 'translateX(100%)',
              transition: 'transform 0.32s cubic-bezier(0.4,0,0.2,1), visibility 0.32s',
              overflowX: 'hidden',
              overflowY: 'auto',
            }}>
            {/* Drawer header — glass accent strip */}
            <div className="flex-shrink-0" style={{
              borderBottom: dark ? '1px solid rgba(249,115,22,0.2)' : '1px solid rgba(249,115,22,0.15)',
              background: dark ? 'rgba(249,115,22,0.06)' : 'rgba(249,115,22,0.04)',
              position: 'relative', overflow: 'hidden',
            }}>
              {/* top shimmer line */}
              <div style={{
                position: 'absolute', top: 0, left: 0, right: 0, height: 2,
                background: 'linear-gradient(90deg, transparent 0%, #F97316 40%, #FBBF24 60%, #F97316 80%, transparent 100%)',
                opacity: 0.8,
              }} />
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
                        <div className="font-black truncate" style={{ fontSize: 15, color: '#F97316', display: 'inline-flex', alignItems: 'center', gap: 4 }}>
                          {user.username || user.name}
                        </div>
                        <div style={{ fontFamily: navFont, fontSize: 11, color: subTextColor }}>
                          {isKhmer ? 'បានចូល' : 'Logged in'}
                        </div>
                      </div>
                    </>
                  ) : (
                    <div className="flex gap-2 flex-wrap">
                      <button onClick={() => { onAuthOpen('login'); setIsMobileMenuOpen(false) }}
                        className="bg-primary text-white px-3 py-1.5 rounded-lg font-bold flex-shrink-0"
                        style={{ fontFamily: navFont, fontSize: 13 }}>
                        {t('nav.login').toUpperCase()}
                      </button>
                      <button onClick={() => { onAuthOpen('register'); setIsMobileMenuOpen(false) }}
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
                    onClick={() => setIsMobileMenuOpen(false)}
                    className="w-8 h-8 rounded-full flex items-center justify-center font-bold flex-shrink-0"
                    style={{ background: dark ? '#374151' : '#f3f4f6', color: textColor, fontSize: 18 }}>
                    ✕
                  </button>
                </div>
              </div>
            </div>

            {/* Mobile search */}
            <form onSubmit={handleSearch} className="px-4 py-3 flex-shrink-0" style={{
              borderBottom: dark ? '1px solid rgba(249,115,22,0.15)' : '1px solid rgba(249,115,22,0.1)',
              background: dark ? 'rgba(249,115,22,0.03)' : 'rgba(249,115,22,0.02)',
            }}>
              <div className="relative">
                <input value={search} onChange={e => setSearch(e.target.value)}
                  placeholder={t('nav.search')}
                  className="w-full rounded-full px-5 py-2.5 pr-11 focus:outline-none"
                  style={{
                    fontFamily: navbFont, fontSize: 15, color: textColor,
                    background: dark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.04)',
                    border: dark ? '1px solid rgba(249,115,22,0.25)' : '1px solid rgba(249,115,22,0.2)',
                  }} />
                <button type="submit" className="absolute right-4 top-1/2 -translate-y-1/2" style={{ color: subTextColor }}>
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </button>
              </div>
            </form>

            {/* Nav items */}
            <div className="flex-1 overflow-y-auto" style={{ overflowX: 'hidden' }}>
              {navItems.map(item => (
                <div key={item.label} style={{
                  borderBottom: dark ? '1px solid rgba(255,255,255,0.06)' : '1px solid rgba(0,0,0,0.06)',
                }}>
                  <div className="flex items-center justify-between px-4 py-3.5 select-none"
                    onClick={() => {
                      if (item.sub && !error) {
                        setMobileSub(mobileSub === item.label ? null : item.label);
                      } else {
                        const cats = expandedCatNames[item.path.split('/').pop()] || item.categories || []
                        const dest = cats.length
                          ? `${item.path}?cats=${cats.map(c => encodeURIComponent(c)).join(',')}`
                          : item.path;
                        navigate(dest);
                        setIsMobileMenuOpen(false);
                      }
                    }}>
                    <span
                      className="font-bold tracking-wide cursor-pointer flex-1"
                      style={{ fontFamily: navbFont, fontSize: 16, color: isActive(item) ? '#F97316' : textColor, transition: 'color 0.15s', letterSpacing: isKhmer ? 0 : undefined }}
                    >
                      {t(NAV_LABEL_KEYS[item.label] || item.label)}
                    </span>
                    {item.sub && !error && (
                      <svg className={`w-4 h-4 flex-shrink-0 transition-transform duration-300 ${mobileSub === item.label ? 'rotate-180' : ''}`}
                        fill="none" stroke={textColor} viewBox="0 0 24 24"
                        onClick={(e) => { e.stopPropagation(); setMobileSub(mobileSub === item.label ? null : item.label); }}>
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M19 9l-7 7-7-7" />
                      </svg>
                    )}
                  </div>
                  <div>
                    {item.sub && !error && mobileSub === item.label && (
                      <div className="pb-2" style={{ background: drawerSubBg }}>
                        <Link
                          to={(() => { const cats = expandedCatNames[item.path.split('/').pop()] || item.categories || []; return cats.length ? `${item.path}?cats=${cats.map(c => encodeURIComponent(c)).join(',')}` : item.path })()}
                          className="block px-8 py-2 font-bold text-primary border-b mb-1"
                          style={{ fontSize: 14, borderColor: drawerBorder }}
                          onClick={() => setIsMobileMenuOpen(false)}>
                          ALL {item.label}
                        </Link>
                        {/* 3-level accordion: MainCate → SubCate → Brand.
                            Flattened single-main categories (e.g. PC BUILD)
                            surface their sub-categories here directly. */}
                        {item.sub.map(mc => {
                          const mcLabel = typeof mc === 'string' ? mc : mc.label
                          const mcPath = `${item.path}/${slugify(mcLabel)}`
                          const mcExpanded = mobileSub === item.label && mobileSubItem === mcLabel;
                          const mcSubs = (mc && mc.sub) || []
                          const mcBrands = (mc && mc.brands) || []

                          return (
                            <div key={mcLabel}>
                              <div className="flex items-center justify-between">
                                <Link to={mcPath}
                                  className="block px-8 py-2 font-bold flex-1"
                                  style={{ fontSize: 14, color: '#F97316', transition: 'color 0.15s' }}
                                  onMouseEnter={e => e.currentTarget.style.color = '#FB923C'}
                                  onMouseLeave={e => e.currentTarget.style.color = '#F97316'}
                                  onClick={() => setIsMobileMenuOpen(false)}>{mcLabel}</Link>
                                {(mcSubs.length > 0 || mcBrands.length > 0) && (
                                  <button
                                    className="px-4 py-2"
                                    onClick={(e) => {
                                      e.stopPropagation();
                                      setMobileSubItem(mcExpanded ? null : mcLabel);
                                    }}>
                                    <span style={{
                                      color: subTextColor,
                                      display: 'inline-block',
                                      transition: 'transform 0.3s',
                                      transform: mcExpanded ? 'rotate(180deg)' : 'rotate(0deg)'
                                    }}>
                                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M19 9l-7 7-7-7" />
                                      </svg>
                                    </span>
                                  </button>
                                )}
                              </div>

                              {(mcSubs.length > 0 || mcBrands.length > 0) && (
                                <div className="pl-12 pb-1 overflow-hidden transition-all duration-300 ease-in-out"
                                  style={{
                                    maxHeight: mcExpanded ? Math.max(mcSubs.length, mcBrands.length) * 35 : 0,
                                    opacity: mcExpanded ? 1 : 0
                                  }}>
                                  {/* Flattened single-main: sub-categories are the brand level */}
                                  {mcSubs.length === 0 && mcBrands.map(brand => (
                                    <Link key={brand} to={brandPath(item, brand)}
                                      className="block py-1 font-semibold"
                                      style={{ fontSize: 13, color: subTextColor, transition: 'color 0.15s' }}
                                      onMouseEnter={e => e.currentTarget.style.color = '#F97316'}
                                      onMouseLeave={e => e.currentTarget.style.color = subTextColor}
                                      onClick={() => setIsMobileMenuOpen(false)}>- {brand}</Link>
                                  ))}
                                  {mcSubs.map(sc => {
                                    const scLabel = sc.label
                                    const scPath = `${mcPath}/${slugify(scLabel)}`
                                    const scExpanded = mcExpanded && mobileSubSub === scLabel;
                                    const brands = sc.brands || [];

                                    return (
                                      <div key={scLabel}>
                                        <div className="flex items-center justify-between">
                                          <Link to={scPath}
                                            className="block py-1 font-semibold flex-1"
                                            style={{ fontSize: 13, color: subTextColor, transition: 'color 0.15s' }}
                                            onMouseEnter={e => e.currentTarget.style.color = '#F97316'}
                                            onMouseLeave={e => e.currentTarget.style.color = subTextColor}
                                            onClick={() => setIsMobileMenuOpen(false)}>{scLabel}</Link>
                                          {brands.length > 0 && (
                                            <button
                                              className="px-2 py-1"
                                              onClick={(e) => {
                                                e.stopPropagation();
                                                setMobileSubSub(scExpanded ? null : scLabel);
                                              }}>
                                              <span style={{
                                                color: subTextColor,
                                                display: 'inline-block',
                                                transition: 'transform 0.3s',
                                                transform: scExpanded ? 'rotate(180deg)' : 'rotate(0deg)'
                                              }}>
                                                <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M19 9l-7 7-7-7" />
                                                </svg>
                                              </span>
                                            </button>
                                          )}
                                        </div>

                                        {brands.length > 0 && (
                                          <div className="pl-8 pb-1 overflow-hidden transition-all duration-300 ease-in-out"
                                            style={{
                                              maxHeight: scExpanded ? brands.length * 30 : 0,
                                              opacity: scExpanded ? 1 : 0
                                            }}>
                                            {brands.map(brand => (
                                              <Link key={brand} to={`${scPath}?cats=${encodeURIComponent(brand)}`}
                                                className="block py-1 font-semibold"
                                                style={{ fontSize: 12, color: subTextColor, transition: 'color 0.15s' }}
                                                onMouseEnter={e => e.currentTarget.style.color = '#F97316'}
                                                onMouseLeave={e => e.currentTarget.style.color = subTextColor}
                                                onClick={() => setIsMobileMenuOpen(false)}>- {brand}</Link>
                                            ))}
                                          </div>
                                        )}
                                      </div>
                                    )
                                  })}
                                </div>
                              )}
                            </div>
                          )
                        })}
                      </div>
                    )}
                  </div>
                </div>
              ))}
            </div>

            {/* Profile actions at bottom */}
            {user && (
              <div className="flex-shrink-0 p-4" style={{
                borderTop: dark ? '1px solid rgba(249,115,22,0.2)' : '1px solid rgba(249,115,22,0.15)',
                background: dark ? 'rgba(249,115,22,0.05)' : 'rgba(249,115,22,0.03)',
              }}>
                <div className="flex flex-col gap-1.5 mb-3">
                  {[
                    { to: '/profile', labelKey: 'nav.myProfile', icon: '👤' },
                    { to: '/orders', labelKey: 'nav.myOrders', icon: '📦' },
                    { to: '/bookmark', label: 'Bookmark', icon: '🔖' },
                  ].map(({ to, labelKey, label, icon }) => (
                    <Link key={to} to={to} onClick={() => setIsMobileMenuOpen(false)}
                      className="flex items-center gap-2 px-3 py-2 rounded-lg font-bold"
                      style={{ fontFamily: navbFont, fontSize: 18, color: textColor, border: `1px solid ${drawerBorder}`, transition: 'background 0.15s' }}
                      onMouseEnter={e => e.currentTarget.style.background = ddHover}
                      onMouseLeave={e => e.currentTarget.style.background = 'transparent'}>
                      {icon} {t(labelKey ?? label)}
                    </Link>
                  ))}
                </div>
                <button onClick={() => { setIsMobileMenuOpen(false); setLogoutOpen(true) }}
                  className="w-full text-red-500 font-bold border border-red-300 py-2 rounded-lg"
                  style={{ fontFamily: navFont, fontSize: 15 }}>
                  🚪 {t('nav.logout')}
                </button>
              </div>
            )}
          </div>
        </>,
        document.body
      )}

      <style>{`
      @keyframes slideInRight {
        from { transform: translateX(100%) }
        to   { transform: translateX(0) }
      }
    `}</style>

      <LogoutConfirmModal
        open={logoutOpen}
        onCancel={() => setLogoutOpen(false)}
        onConfirm={() => { setLogoutOpen(false); logout() }}
      />
    </>
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
