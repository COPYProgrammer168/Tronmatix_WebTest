/**
 * src/hooks/useApi.js
 *
 * Generic fetch hook used by StaffDashboard and DevDashboard.
 * Handles: loading, error, empty, refetch, abort on unmount.
 *
 * Usage:
 *   const { data, loading, error, refetch } = useApi('/api/orders')
 */
import { useState, useEffect, useCallback, useRef } from 'react'
import api from '../lib/axios' // existing axios instance with Bearer token

export function useApi(endpoint, options = {}) {
  const { transform = d => d, deps = [] } = options

  const [data,    setData]    = useState(null)
  const [loading, setLoading] = useState(true)
  const [error,   setError]   = useState(null)

  const abortRef = useRef(null)

  const fetch = useCallback(async () => {
    // Cancel previous in-flight request
    if (abortRef.current) abortRef.current.abort()
    abortRef.current = new AbortController()

    setLoading(true)
    setError(null)

    try {
      const res = await api.get(endpoint, {
        signal: abortRef.current.signal,
      })
      // Support both { data: [...] } and direct array responses
      const raw = res.data?.data ?? res.data
      setData(transform(raw))
    } catch (err) {
      if (err.name === 'CanceledError' || err.name === 'AbortError') return
      const msg = err.response?.data?.message || err.message || 'Failed to load data.'
      setError(msg)
    } finally {
      setLoading(false)
    }
  }, [endpoint, ...deps]) // eslint-disable-line react-hooks/exhaustive-deps

  useEffect(() => {
    fetch()
    return () => abortRef.current?.abort()
  }, [fetch])

  return { data, loading, error, refetch: fetch }
}