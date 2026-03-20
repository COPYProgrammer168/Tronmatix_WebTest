<?php

// config/cors.php
// ── Laravel CORS — must match Apache headers exactly ─────────────────────────

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', '*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => [
        'https://tronmatix-webtest.onrender.com',
        'http://localhost:5173',
        'http://localhost:5174',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:5174',
    ],

    'allowed_origins_patterns' => [
        '#^https://[\w-]+\.onrender\.com$#',
        '#^http://localhost(:\d+)?$#',
    ],

    // ── FIX: Content-Type MUST be listed here ─────────────────────────────────
    // Error was: "content-type is not allowed by Access-Control-Allow-Headers"
    // Solution: list every header axios sends explicitly
    'allowed_headers' => [
        'Authorization',
        'Content-Type',
        'Accept',
        'Origin',
        'X-Requested-With',
        'X-CSRF-TOKEN',
    ],

    'exposed_headers' => ['Authorization'],

    'max_age' => 86400,

    // false = Bearer token (no cookies) — must match axios withCredentials:false
    'supports_credentials' => false,

];
