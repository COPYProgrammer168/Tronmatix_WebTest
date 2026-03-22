<?php

// config/cors.php
// ── Laravel CORS — must match Apache headers exactly ─────────────────────────

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        env('FRONTEND_URL', 'https://tronmatix-frontend.onrender.com'),
    ],

    'allowed_origins_patterns' => [
    ],

    'allowed_headers' => [*],

    'exposed_headers' => ['Authorization'],

    'max_age' => 86400,

    // false = Bearer token (no cookies) — must match axios withCredentials:false
    'supports_credentials' => false,

];
