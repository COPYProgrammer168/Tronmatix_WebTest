// src/context/LanguageContext.jsx
// Drop-in React i18n context for Tronmatix Computer
// Usage:
//   1. Wrap app: <LanguageProvider><App /></LanguageProvider>
//   2. In any component: const { t, lang, toggle, isKhmer } = useLang()
//   3. Use: t('cart.title'), t('common.showingProducts', { count: 12 })

import { createContext, useContext, useState, useCallback, useEffect } from 'react'
import en from '../locales/en.json'
import km from '../locales/km.json'

const LOCALES = { en, km }
const STORAGE_KEY = 'tronmatix_lang'

const LanguageContext = createContext(null)

export function LanguageProvider({ children }) {
  const [lang, setLang] = useState(() => {
    try {
      const saved = localStorage.getItem(STORAGE_KEY)
      return (saved === 'en' || saved === 'km') ? saved : 'en'
    } catch {
      return 'en'
    }
  })

  // dot-path translation accessor with variable interpolation
  // t('common.showingProducts', { count: 5 }) → "Showing 5 products"
  const t = useCallback((key, vars = {}) => {
    const locale = LOCALES[lang] || LOCALES.en
    const fallback = LOCALES.en

    const resolve = (obj, path) =>
      path.split('.').reduce((o, k) => (o != null && o[k] !== undefined ? o[k] : null), obj)

    const raw = resolve(locale, key) ?? resolve(fallback, key) ?? key

    if (typeof raw !== 'string') return raw

    return Object.entries(vars).reduce(
      (s, [k, v]) => s.replace(`{${k}}`, String(v)),
      raw
    )
  }, [lang])

  const switchLang = useCallback((l) => {
    if (l !== 'en' && l !== 'km') return
    setLang(l)
    try { localStorage.setItem(STORAGE_KEY, l) } catch {}
    document.documentElement.lang = l
    // Apply Khmer font class to body for global CSS targeting
    document.body.classList.toggle('lang-km', l === 'km')
    document.body.classList.toggle('lang-en', l === 'en')
  }, [])

  const toggle = useCallback(() => {
    switchLang(lang === 'en' ? 'km' : 'en')
  }, [lang, switchLang])

  // Sync on mount
  useEffect(() => {
    document.documentElement.lang = lang
    document.body.classList.toggle('lang-km', lang === 'km')
    document.body.classList.toggle('lang-en', lang === 'en')
  }, [lang])

  const value = {
    lang,
    isKhmer: lang === 'km',
    t,
    toggle,
    switchLang,
  }

  return (
    <LanguageContext.Provider value={value}>
      {children}
    </LanguageContext.Provider>
  )
}

export function useLang() {
  const ctx = useContext(LanguageContext)
  if (!ctx) throw new Error('useLang() must be called inside <LanguageProvider>')
  return ctx
}
