<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        // Local development
        'http://localhost:5173',
        'http://localhost:5174',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:5174',

        // Production / preview domains – add every place you deploy frontend
        'https://tronmatixwebtest.netlify.app',
        'https://*.netlify.app',               // all Netlify previews
        'https://*.vercel.app',                // all Vercel previews
        '*.onrender.com',                      // Render preview URLs
        'https://your-custom-domain.com',      // ← replace with real domain later
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,  // ← MUST BE TRUE for Sanctum + cookies
];
