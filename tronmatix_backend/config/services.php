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
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // ── Bot 1: ADMIN / SHOP OWNER ──────────────────────────────────────────────
    // Purpose: Order receipts, payment alerts, delivery confirmations TO THE OWNER
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
    ],

    // ── Bot 2: USER-FACING NOTIFICATION BOT ────────────────────────────────────
    // Purpose: Order updates, receipts, shipping alerts TO CUSTOMERS
    'telegram_user' => [
        'bot_token'       => env('TELEGRAM_USER_BOT_TOKEN'),
        'chat_id'         => env('TELEGRAM_CHAT_ID'),
        'webhook_secret'  => env('TELEGRAM_USER_WEBHOOK_SECRET', ''),
        'mini_app_url'    => env('TELEGRAM_MINI_APP_URL', env('APP_URL', '')),
        'poller_enabled'  => env('TELEGRAM_POLLER_ENABLED', true),
    ],

    'gemini' => ['key' => env('GEMINI_API_KEY')],
    'groq' => ['key' => env('GROQ_API_KEY')],

    // ── Bakong / KHQR ──────────────────────────────────────────────────────────
    'bakong' => [
        'bakong_id'     => env('KHQR_BAKONG_ID', 'ec475493@ababank'),
        'id'            => env('KHQR_BAKONG_ID', 'ec475493@ababank'),

        'merchant_name' => env('KHQR_BAKONG_MERCHANT_NAME', 'Tronmatix'),
        'merchant_city' => env('KHQR_BAKONG_MERCHANT_CITY', 'Phnom Penh'),
        'phone'         => env('KHQR_BAKONG_PHONE'),
        'currency'      => env('KHQR_CURRENCY', 'USD'),

        'api_url'           => env('KHQR_BAKONG_API_URL', 'https://api-bakong.nbc.gov.kh/v1'),

        'token'             => env('KHQR_BAKONG_TOKEN'),
        'bakong_token'      => env('KHQR_BAKONG_TOKEN'),

        'static_payway_url' => env('KHQR_STATIC_PAYWAY_URL', 'https://link.payway.com.kh/ABAPAYTD422549V'),
    ],
    'payway' => [
        // Merchant credentials
        'merchant_id' => env('PAYWAY_MERCHANT_ID', ''),
        'api_key' => env('PAYWAY_API_KEY', ''),
        'merchant_name' => env('KHQR_BAKONG_MERCHANT_NAME', 'Tronmatix'),

        // RSA key — path to PEM file
        'rsa_key_path' => env('PAYWAY_RSA_KEY_PATH', 'storage/payway_private.pem'),
        'rsa_public_key' => env('PAYWAY_RSA_PUBLIC_KEY', 'storage/payway_public.pem'),
        // Fallback: raw PEM string in env (less secure, for Render deploy)
        'rsa_private_key' => env('PAYWAY_RSA_KEY_PATH'),

        // API endpoints base (sandbox vs production)
        'api_url' => env('PAYWAY_API_URL', 'https://checkout-sandbox.payway.com.kh/api/payment-gateway/v1/payments'),

        // Where PayWay POSTs payment confirmation
        'callback_url' => env('PAYWAY_CALLBACK_URL', env('APP_URL') . '/api/payment/webhook'),
        'callback_local_url' => env('PAYWAY_CALLBACK_LOCAL_URL', ''),

        // QR lifetime in minutes (1–60)
        'qr_lifetime' => (int) env('PAYWAY_QR_LIFETIME', 10),
    ],

    'google' => [
        'maps_key' => env('GOOGLE_MAPS_KEY'),
    ],

    // ── Shop origin (used by distance-based delivery fee calculation) ─────────
    // Near Sovannphumi School, Stop Tep Phan, 14 St 160, Phnom Penh, Cambodia.
    'shop' => [
        'origin_lat' => env('SHOP_ORIGIN_LAT', 11.56298),
        'origin_lng' => env('SHOP_ORIGIN_LNG', 104.899518),
        'address'    => env('SHOP_ADDRESS', '14 St 160, Phnom Penh, Cambodia (Near Sovannphumi School)'),
    ],

    // ── SMS (phone-OTP password reset) ─────────────────────────────────────────
    // driver: 'twilio' (real SMS) or 'log' (write to security log for dev/testing)
    'sms' => [
        'driver'       => env('SMS_DRIVER', 'log'),
        'twilio_sid'   => env('TWILIO_SID'),
        'twilio_token' => env('TWILIO_TOKEN'),
        'twilio_from'  => env('TWILIO_FROM'),
        // Country code used by PhoneHelper to normalize local numbers to E.164.
        'country_code' => env('SMS_COUNTRY_CODE', '855'),
    ],

];
