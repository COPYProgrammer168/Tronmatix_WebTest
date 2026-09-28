/**
 * src/lib/firebase.js
 * Firebase app initialization for one-time phone verification.
 * Config comes from VITE_FIREBASE_* env vars (inlined by Vite at build time).
 *
 * IMPORTANT: init is guarded so a missing/incomplete Firebase config NEVER
 * crashes the app. Previously getAuth() threw `auth/invalid-api-key` at module
 * load whenever VITE_FIREBASE_* were unset, which took down the whole React
 * tree — including the login modal — before it could render.
 */
import { initializeApp } from 'firebase/app'
import { getAuth } from 'firebase/auth'

const firebaseConfig = {
  apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
  authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
  projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
  storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
  messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
  appId: import.meta.env.VITE_FIREBASE_APP_ID,
}

// Firebase is only needed for optional phone-OTP features. If the project
// isn't configured, export a null auth + flag so callers can degrade
// gracefully instead of throwing on every page load.
const isConfigured =
  Boolean(firebaseConfig.apiKey) &&
  Boolean(firebaseConfig.projectId) &&
  Boolean(firebaseConfig.authDomain)

let app = null
let auth = null

if (isConfigured) {
  try {
    app = initializeApp(firebaseConfig)
    auth = getAuth(app)
  } catch (err) {
    // Never let Firebase misconfiguration take down the app.
    console.error('[firebase] Failed to initialize Firebase:', err)
    app = null
    auth = null
  }
}

export { app, auth, isConfigured }
