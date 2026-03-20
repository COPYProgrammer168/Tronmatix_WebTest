<?php

// config/cors.php
// ── CORS must allow the frontend origin for ALL api/* routes ─────────────────
// FIX: Use '*' wildcard for allowed_origins_patterns as fallback
// FIX: supports_credentials must match frontend axios withCredentials setting

$frontendUrl = rtrim(env('FRONTEND_URL', 'https://tronmatix-webtest.onrender.com'), '/');

return [

    // Allow ALL paths — '*' catches any route including OPTIONS preflight
    'paths' => ['*'],

    'allowed_methods' => ['*'],

    // FIX: Build allowed_origins dynamically from env so no code push needed
    'allowed_origins' => array_values(array_unique(array_filter([
        $frontendUrl,
        'http://localhost:5173',
        'http://localhost:5174',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:5174',
    ]))),

    // FIX: Allow any onrender.com subdomain as pattern fallback
    'allowed_origins_patterns' => [
        '#^https://[\w-]+\.onrender\.com$#',
        '#^http://localhost(:\d+)?$#',
        '#^http://127\.0\.0\.1(:\d+)?$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Authorization'],

    // Cache preflight 2h (86400 = 24h can cause stale issues during dev)
    'max_age' => 7200,

    // FIX: MUST be false — frontend uses Bearer token not cookies
    // If true, browser blocks requests unless axios has withCredentials:true
    'supports_credentials' => false,

];
