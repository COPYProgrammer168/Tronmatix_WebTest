<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'register', 'auth/*'],

    'allowed_methods' => ['*'],

    // Use env('FRONTEND_URL') so Render env var controls it
    // without needing a code push every time URL changes
    'allowed_origins' => [
        'https://tronmatix-webtest.onrender.com',
        'http://localhost:5173',
        'http://localhost:5174',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    // Cache preflight for 24h — reduces OPTIONS requests
    'max_age' => 86400,

    'supports_credentials' => false,

];
