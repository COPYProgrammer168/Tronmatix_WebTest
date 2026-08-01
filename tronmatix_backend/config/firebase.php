<?php

// config/firebase.php
// Minimal Firebase config for the core kreait/firebase-php Factory.
//
// Credentials can be supplied two ways:
//   1. FIREBASE_CREDENTIALS_FILE — path to a service-account JSON file
//   2. FIREBASE_CREDENTIALS      — the service-account JSON as a string
// FIREBASE_PROJECT_ID is used as a fallback for the project id.

return [

    'project_id' => env('FIREBASE_PROJECT_ID'),

    // Path to a service-account JSON file (Render/container friendly).
    'credentials_file' => env('FIREBASE_CREDENTIALS_FILE'),

    // Service-account JSON supplied inline as a string (env var).
    'credentials' => env('FIREBASE_CREDENTIALS'),

];
