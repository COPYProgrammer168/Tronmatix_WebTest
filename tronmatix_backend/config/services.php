<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // ── Bot 1: ADMIN / SHOP OWNER ──────────────────────────────────────────────
    // Purpose: Order receipts, payment alerts, delivery confirmations TO THE OWNER
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id'   => env('TELEGRAM_CHAT_ID'),
    ],

    // ── Bot 2: USER-FACING NOTIFICATION BOT ────────────────────────────────────
    // Purpose: Order updates, receipts, shipping alerts TO CUSTOMERS
    'telegram_user' => [
        'bot_token'      => env('TELEGRAM_USER_BOT_TOKEN'),
        'chat_id'        => env('TELEGRAM_CHAT_ID'),
        'webhook_secret' => env('TELEGRAM_USER_WEBHOOK_SECRET', ''),
        'mini_app_url'   => env('TELEGRAM_MINI_APP_URL', env('APP_URL', '')),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],

    // ── Bakong / KHQR ──────────────────────────────────────────────────────────
    'bakong' => [
        // FIX [1]: KHQR_BAKONG_ID must end with @ababank (NOT @abaa).
        // Open ABA app → Profile → Bakong ID to verify your exact value.
        // Example: "1600273@ababank"
        'bakong_id'     => env('KHQR_BAKONG_ID'),
        'id'            => env('KHQR_BAKONG_ID'), // legacy alias

        'merchant_name' => env('KHQR_BAKONG_MERCHANT_NAME', 'Tronmatix'),
        'merchant_city' => env('KHQR_BAKONG_MERCHANT_CITY', 'Phnom Penh'),
        'phone'         => env('KHQR_BAKONG_PHONE'),
        'currency'      => env('KHQR_CURRENCY', 'USD'),

        'api_url'           => env('KHQR_BAKONG_API_URL', 'https://api-bakong.nbc.gov.kh/v1'),

        // FIX [2]: Both 'token' and legacy 'bakong_token' keys provided.
        // CheckPaymentController uses config('services.bakong.token').
        // This JWT expires every 90 days — renew at https://api-bakong.nbc.gov.kh
        'token'             => env('KHQR_BAKONG_TOKEN'),
        'bakong_token'      => env('KHQR_BAKONG_TOKEN'), // legacy alias

        'static_payway_url' => env('KHQR_STATIC_PAYWAY_URL', 'https://link.payway.com.kh/ABAPAYTD422549V'),
    ],

    'google' => [
        'maps_key' => env('GOOGLE_MAPS_KEY'),
    ],

];
