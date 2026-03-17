<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:5173',
        'http://localhost:5174',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:5174',
        'https://agent-69b9394875597800c3a1d24d--tronmaixtest168.netlify.app/',
    ],
    'allowed_origins_patterns' => [
        '#^https:\/\/.*\.netlify\.app$#', // ✅ correct wildcard support
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
