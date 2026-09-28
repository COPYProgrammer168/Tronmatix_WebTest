<?php

// config/firebase.php
// Firebase config for the core kreait/firebase-php Factory (server-side ID-token
// verification) plus the web-app keys the Blade dashboard uses to load the
// Firebase JS SDK for phone verification.

return [

    'project_id' => env('FIREBASE_PROJECT_ID'),

    // Path to a service-account JSON file (Render/container friendly).
    'credentials_file' => env('FIREBASE_CREDENTIALS_FILE'),

    // Service-account JSON supplied inline as a string (env var).
    'credentials' => env('FIREBASE_CREDENTIALS'),

    // Web-app config (used by the client SDK in Blade views)
    'api_key'     => env('FIREBASE_API_KEY'),
    'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
    'app_id'      => env('FIREBASE_APP_ID'),

];
