<?php

/**
 * config/security-headers.php
 *
 * HTTP Security Headers to add to all responses.
 * Wire this up via App\Http\Middleware\SecurityHeadersMiddleware (see below).
 *
 * These headers protect against clickjacking, MIME sniffing,
 * XSS injection, and enforce HTTPS.
 */

return [

    // -----------------------------------------------------------------------
    // Content Security Policy
    // Restricts which resources the browser can load.
    // Tighten 'script-src' to remove 'unsafe-inline' once you have nonces set up.
    // -----------------------------------------------------------------------
    'Content-Security-Policy' => implode('; ', [
        "default-src 'self'",
        "script-src 'self'",        // Only scripts from your own domain
        "style-src 'self' 'unsafe-inline'",  // Inline styles needed for most React apps
        "img-src 'self' data: https:",
        "font-src 'self' data:",
        "connect-src 'self'",       // API calls only to your own backend
        "frame-ancestors 'none'",   // Prevents clickjacking
        "form-action 'self'",
        "base-uri 'self'",
        "upgrade-insecure-requests",
    ]),

    // Prevent MIME type sniffing
    'X-Content-Type-Options' => 'nosniff',

    // Prevent loading in iframes (redundant with CSP but kept for old browsers)
    'X-Frame-Options' => 'DENY',

    // Enable browser XSS filter (legacy browsers)
    'X-XSS-Protection' => '1; mode=block',

    // Enforce HTTPS for 1 year, include subdomains
    // WARNING: Only enable in production with a valid SSL certificate
    'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',

    // Control what info is sent in Referrer header
    'Referrer-Policy' => 'strict-origin-when-cross-origin',

    // Restrict browser features (camera, mic, geolocation, etc.)
    'Permissions-Policy' => implode(', ', [
        'camera=()',
        'microphone=()',
        'geolocation=()',
        'payment=()',
        'usb=()',
    ]),

    // Remove the X-Powered-By header (set in middleware, not here)
    // This is handled by removing it in SecurityHeadersMiddleware
];
