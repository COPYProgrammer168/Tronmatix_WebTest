import { createContext, useContext, useState, useEffect } from 'react'

const LocationContext = createContext(null)
const STORAGE_KEY = 'tronmatix_location'

export function LocationProvider({ children }) {
  const [savedLocation, setSavedLocation] = useState(() => {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null')
    } catch {
      return null
    }
  })
  
  useEffect(() => {
    const onFocus = () => {
      try {
        const stored = JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null')
        setSavedLocation(stored)  // always sync, even if already set
      } catch {}
    }
    window.addEventListener('focus', onFocus)
    return () => window.removeEventListener('focus', onFocus)
  }, [])  // empty deps — register once, never re-register

  const saveLocation = (loc) => {
    setSavedLocation(loc)
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(loc)) } catch {}
  }

  const clearLocation = () => {
    setSavedLocation(null)
    try { localStorage.removeItem(STORAGE_KEY) } catch {}
  }

  return (
    <LocationContext.Provider value={{ savedLocation, saveLocation, clearLocation }}>
      {children}
    </LocationContext.Provider>
  )
}

export const useDeliveryLocation = () => useContext(LocationContext)
export const useLocation2 = useDeliveryLocation  // backward-compat alias
