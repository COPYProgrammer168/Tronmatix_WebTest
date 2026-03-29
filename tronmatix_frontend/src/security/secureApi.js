/**
 * src/security/secureApi.js
 *
 * Secure Axios instance for React + Laravel SPA.
 *
 * Handles:
 * - CSRF token fetching and injection (X-XSRF-TOKEN header)
 * - Automatic 401/419 session expiry handling
 * - Credentials included on every request (session cookie)
 * - Centralized error interception
 *
 * Usage:
 *   import api from '@/security/secureApi';
 *   const response = await api.post('/auth/login', { email, password });
 */

import axios from 'axios';

// -----------------------------------------------------------------------
// Base Axios instance
// -----------------------------------------------------------------------
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000',

  // CRITICAL: Send session cookie on every cross-origin request
  withCredentials: true,

  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },

  // Abort requests that take too long (prevents hanging requests)
  timeout: 15000,
});

// -----------------------------------------------------------------------
// CSRF Token Bootstrap
// -----------------------------------------------------------------------

/**
 * Fetches the CSRF cookie from Laravel's Sanctum endpoint.
 * Must be called ONCE before making any state-changing request (POST/PUT/DELETE).
 *
 * Laravel sets the XSRF-TOKEN cookie; Axios reads it and sends it back
 * as the X-XSRF-TOKEN header, which Laravel validates server-side.
 *
 * Call this in your App.jsx or auth store on app startup.
 */
export async function initCsrf() {
  try {
    await axios.get(
      `${import.meta.env.VITE_API_URL || 'http://localhost:8000'}/sanctum/csrf-cookie`,
      { withCredentials: true }
    );
  } catch (error) {
    console.error('[Security] Failed to fetch CSRF cookie:', error.message);
    throw error;
  }
}

// -----------------------------------------------------------------------
// Request Interceptor — attach XSRF token manually if needed
// -----------------------------------------------------------------------
api.interceptors.request.use(
  (config) => {
    // Axios handles XSRF-TOKEN cookie automatically via withXSRFToken,
    // but for non-Axios environments or extra safety, we can read it manually:
    const xsrfToken = getCookie('XSRF-TOKEN');
    if (xsrfToken) {
      config.headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrfToken);
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// -----------------------------------------------------------------------
// Response Interceptor — handle session expiry globally
// -----------------------------------------------------------------------
api.interceptors.response.use(
  (response) => response,

  (error) => {
    const status = error.response?.status;

    if (status === 401) {
      // Session expired or unauthenticated
      handleSessionExpiry('Your session has expired. Please log in again.');
    }

    if (status === 419) {
      // CSRF token mismatch — re-fetch token and inform user
      handleSessionExpiry('Security token expired. Please refresh and try again.');
    }

    if (status === 429) {
      // Rate limited — surface clearly to user
      console.warn('[Security] Rate limit hit. Too many requests.');
    }

    return Promise.reject(error);
  }
);

// -----------------------------------------------------------------------
// Session Expiry Handler
// -----------------------------------------------------------------------

/**
 * Centralized session expiry handler.
 * Clears local auth state and redirects to login.
 */
function handleSessionExpiry(message) {
  // Clear only known auth-related storage — never bluntly wipe all sessionStorage
  localStorage.removeItem('user')
  localStorage.removeItem('token')
  localStorage.removeItem('tronmatix_user')

  // Dispatch a global event so the AuthContext / auth store can react
  window.dispatchEvent(new CustomEvent('session:expired', { detail: { message } }))

  // Only hard-redirect if already on a protected path and not already heading to home
  const protectedPaths = ['/orders', '/profile', '/checkout', '/cart']
  const onProtected = protectedPaths.some((p) => window.location.pathname.startsWith(p))
  if (onProtected && !window.location.pathname.includes('/login')) {
    window.location.replace('/')
  }
}

// -----------------------------------------------------------------------
// Utility
// -----------------------------------------------------------------------

/**
 * Read a cookie by name.
 */
function getCookie(name) {
  const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
  return match ? match[2] : null;
}

export default api;
