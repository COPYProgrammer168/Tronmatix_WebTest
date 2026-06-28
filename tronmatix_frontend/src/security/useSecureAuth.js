/**
 * src/security/useSecureAuth.js
 *
 * React hook that:
 * - Initializes CSRF protection on app load
 * - Listens for session:expired events from secureApi.js
 * - Exposes login/logout with secure API calls
 * - Stores only non-sensitive user metadata in state (NOT tokens)
 *
 * Usage:
 *   const { user, login, logout, loading } = useSecureAuth();
 */

import { useState, useEffect, useCallback, useRef } from 'react';
import api, { initCsrf } from './secureApi';

export function useSecureAuth() {
  const [user, setUser]       = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError]     = useState(null);

  // Guard against state updates after unmount
  const isMounted = useRef(true);
  useEffect(() => {
    isMounted.current = true;
    return () => { isMounted.current = false; };
  }, []);

  // -----------------------------------------------------------------------
  // Bootstrap: fetch CSRF cookie and check existing session
  // -----------------------------------------------------------------------
  useEffect(() => {
    async function bootstrap() {
      try {
        // 1. Fetch CSRF cookie from Laravel Sanctum
        await initCsrf();

        // 2. Check if the user already has a valid session
        const response = await api.get('/api/auth/me');
        if (isMounted.current) {
          setUser(response.data.user);
        }
      } catch {
        // 401 = no active session — that's fine on public pages
        if (isMounted.current) setUser(null);
      } finally {
        if (isMounted.current) setLoading(false);
      }
    }

    bootstrap();
  }, []);

  // -----------------------------------------------------------------------
  // Listen for session expiry events dispatched by the Axios interceptor
  // -----------------------------------------------------------------------
  useEffect(() => {
    function onSessionExpired(event) {
      if (isMounted.current) {
        setUser(null);
        setError(event.detail?.message || 'Session expired.');
      }
    }

    window.addEventListener('session:expired', onSessionExpired);
    return () => window.removeEventListener('session:expired', onSessionExpired);
  }, []);

  // -----------------------------------------------------------------------
  // Login
  // -----------------------------------------------------------------------
  const login = useCallback(async (email, password) => {
    setError(null);
    try {
      // Re-fetch CSRF cookie before login (token may have rotated)
      await initCsrf();

      const response = await api.post('/api/auth/login', { email, password });

      if (isMounted.current) {
        setUser(response.data.user);
      }

      return { success: true };
    } catch (err) {
      const message =
        err.response?.data?.errors?.email?.[0] ||
        err.response?.data?.message ||
        'Login failed. Please try again.';

      if (isMounted.current) setError(message);
      return { success: false, message };
    }
  }, []);

  // -----------------------------------------------------------------------
  // Logout
  // -----------------------------------------------------------------------
  const logout = useCallback(async () => {
    try {
      await api.post('/api/auth/logout');
    } catch {
      // Even if the request fails, clear local state
    } finally {
      if (isMounted.current) {
        setUser(null);
        setError(null);
      }
    }
  }, []);

  return { user, loading, error, login, logout };
}
