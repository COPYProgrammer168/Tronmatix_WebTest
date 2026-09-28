<?php

/**
 * config/session.php — Security-hardened additions
 *
 * INSTRUCTIONS:
 * Merge these values into your existing config/session.php file.
 * Do NOT duplicate keys that already exist — update their values instead.
 */

return [

    // -----------------------------------------------------------------------
    // Driver: use 'database' for production (stored server-side, not client)
    // Never use 'cookie' driver — session data would live entirely on client.
    // -----------------------------------------------------------------------
    'driver' => env('SESSION_DRIVER', 'database'),

    // -----------------------------------------------------------------------
    // Idle timeout: 2 hours of inactivity (in minutes, Laravel default)
    // -----------------------------------------------------------------------
    'lifetime' => env('SESSION_LIFETIME', 120),

    // Destroy session on browser close (no persistent session cookie)
    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    // -----------------------------------------------------------------------
    // Absolute session timeout in SECONDS (enforced by SecurityMiddleware).
    // 28800 = 8 hours. User must re-login regardless of activity.
    // -----------------------------------------------------------------------
    'absolute_timeout' => env('SESSION_ABSOLUTE_TIMEOUT', 28800),

    // -----------------------------------------------------------------------
    // Session ID rotation interval in SECONDS (enforced by SecurityMiddleware).
    // 900 = 15 minutes. New session ID issued every 15 min to limit fixation.
    // -----------------------------------------------------------------------
    'rotation_interval' => env('SESSION_ROTATION_INTERVAL', 900),

    // -----------------------------------------------------------------------
    // Cookie settings — CRITICAL for security
    // -----------------------------------------------------------------------
    'cookie' => env(
        'SESSION_COOKIE',
        'tronmatix_session'   // Rename from 'laravel_session' to obscure framework
    ),

    // Restrict cookie to your domain only
    'domain' => env('SESSION_DOMAIN', null),

    // Require HTTPS for session cookie (set to true in production!)
    'secure' => env('SESSION_SECURE_COOKIE', true),

    // Prevent JavaScript from accessing session cookie (XSS mitigation)
    'http_only' => true,

    // SameSite=Lax: blocks CSRF from cross-site POSTs, allows normal navigation.
    // Use 'strict' for maximum protection (may break OAuth redirects).
    // NEVER use 'none' unless you fully understand the implications.
    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    // Partition cookie (CHIPS) — set false unless you need cross-site iframes
    'partitioned' => false,

];
