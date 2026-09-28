// src/lib/telegramMiniApp.js
// Thin wrapper around the Telegram WebApp SDK used by the Telegram Mini App.
//
// A Mini App runs inside Telegram with a signed `initData` payload injected by
// the client. It IS the identity of the Telegram user for the bot that owns the
// mini app, and is the only safe thing to send to the backend for verification.
//
// We load the official script lazily so the normal website (outside Telegram)
// is completely unaffected — `getInitData()` returns null there.

const TELEGRAM_SDK_URL = 'https://telegram.org/js/telegram-web-app.js'

let webApp = null
let sdkPromise = null

function loadSdk() {
  if (typeof window === 'undefined') return null
  if (webApp || window.Telegram?.WebApp) {
    webApp = window.Telegram?.WebApp || null
    return webApp
  }
  if (!sdkPromise) {
    sdkPromise = new Promise((resolve) => {
      const s = document.createElement('script')
      s.src = TELEGRAM_SDK_URL
      s.async = true
      s.onload = () => {
        webApp = window.Telegram?.WebApp || null
        resolve(webApp)
      }
      s.onerror = () => resolve(null)
      document.head.appendChild(s)
    })
  }
  return sdkPromise
}

/**
 * Returns true when the page is running inside a Telegram Mini App (the WebApp
 * object exists and reports the Telegram client environment).
 */
export async function isMiniApp() {
  const app = await loadSdk()
  return Boolean(app && app.initData && app.version)
}

/**
 * The raw `initData` string Telegram injects — the signed payload the backend
 * verifies. Returns null outside Telegram (or if the SDK failed to load).
 */
export async function getInitData() {
  const app = await loadSdk()
  return app?.initData && app.initData.length > 0 ? app.initData : null
}

/**
 * Tell Telegram the mini app is ready (hides the loading placeholder and lets
 * the client show the expand button). Safe no-op outside Telegram.
 */
export function ready() {
  const app = webApp || window.Telegram?.WebApp
  if (app?.ready) try { app.ready() } catch { /* noop */ }
}

/**
 * Expand the mini app to full-height (mobile UX). Safe no-op outside Telegram.
 */
export function expand() {
  const app = webApp || window.Telegram?.WebApp
  if (app?.expand) try { app.expand() } catch { /* noop */ }
}