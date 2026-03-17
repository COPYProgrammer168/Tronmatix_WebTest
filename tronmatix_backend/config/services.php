<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
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

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],
    'bakong' => [
        // 'bakong_id' — used by GenerateKhqrController: config('services.bakong.bakong_id')
        'bakong_id' => env('KHQR_BAKONG_ID'),
        // 'id' kept as alias so any legacy code still works
        'id' => env('KHQR_BAKONG_ID'),

        'merchant_name' => env('KHQR_BAKONG_MERCHANT_NAME', 'KRY VICHHEKA Tronmatix'),
        'merchant_city' => env('KHQR_BAKONG_MERCHANT_CITY', 'Phnom Penh'),
        'phone' => env('KHQR_BAKONG_PHONE'),
        'currency' => env('KHQR_CURRENCY', 'USD'),   // USD — matches KHQR_CURRENCY in .env

        'api_url' => env('KHQR_BAKONG_API_URL', 'https://api-bakong.nbc.gov.kh/v1'),
        'token' => env('KHQR_BAKONG_TOKEN'),

        // Static ABA PayWay fallback link (used when NBC API is unreachable in dev)
        'static_payway_url' => env('KHQR_STATIC_PAYWAY_URL', 'https://link.payway.com.kh/ABAPAYTD422549V'),
    ],
];
