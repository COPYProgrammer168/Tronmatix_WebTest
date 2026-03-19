<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],

    'allowed_methods' => ['*'],

    // FIX: Use env('FRONTEND_URL') so you can change it from Render dashboard
    // without pushing code. Falls back to hardcoded URLs for local dev.
    'allowed_origins' => array_filter([
        'http://localhost:5173',
        'http://localhost:5174',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:5174',
        env('FRONTEND_URL'),   // set in Render: https://tronmatix-webtest.onrender.com
    ]),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400, // cache preflight for 24h — reduces OPTIONS requests

    // Must be false for Bearer token auth (no cookies)
    'supports_credentials' => false,

];
