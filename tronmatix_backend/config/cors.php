<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],

    'allowed_methods' => ['*'],

    // Use env('FRONTEND_URL') so Render env var controls it
    // without needing a code push every time URL changes
    'allowed_origins' => array_values(array_filter([
        'http://localhost:5173',
        'http://localhost:5174',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:5174',
        env('FRONTEND_URL', 'https://tronmatix-webtest.onrender.com'),
    ])),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    // Cache preflight for 24h — reduces OPTIONS requests
    'max_age' => 86400,

    // false = Bearer token auth (no cookies needed)
    'supports_credentials' => false,

];
