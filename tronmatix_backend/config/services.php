<?php

return [

    'postmark' => ['key' => env('POSTMARK_API_KEY')],
    'resend'   => ['key' => env('RESEND_API_KEY')],

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

    // ── Bot 1: ADMIN / SHOP OWNER ─────────────────────────────────────────────
    // Used by: TelegramService.php
    // Purpose: Order receipts & alerts → TO THE OWNER
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id'   => env('TELEGRAM_CHAT_ID'),
    ],

    // ── Bot 2: USER-FACING NOTIFICATION BOT ──────────────────────────────────
    // Used by: TelegramBotService.php + TelegramUserService.php
    // Purpose: Order updates, receipts → TO CUSTOMERS
    'telegram_user' => [
        'bot_token'      => env('TELEGRAM_USER_BOT_TOKEN'),
        'chat_id'        => env('TELEGRAM_CHAT_ID'),
        'webhook_secret' => env('TELEGRAM_USER_WEBHOOK_SECRET', ''),
        'mini_app_url'   => env('TELEGRAM_MINI_APP_URL', ''),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],

    'bakong' => [
        'bakong_id'      => env('KHQR_BAKONG_ID'),
        'id'             => env('KHQR_BAKONG_ID'),
        'merchant_name'  => env('KHQR_BAKONG_MERCHANT_NAME', 'KRY VICHHEKA Tronmatix'),
        'merchant_city'  => env('KHQR_BAKONG_MERCHANT_CITY', 'Phnom Penh'),
        'phone'          => env('KHQR_BAKONG_PHONE'),
        'currency'       => env('KHQR_CURRENCY', 'USD'),
        'api_url'        => env('KHQR_BAKONG_API_URL', 'https://api-bakong.nbc.gov.kh/v1'),
        'token'          => env('KHQR_BAKONG_TOKEN'),
        'static_payway_url' => env('KHQR_STATIC_PAYWAY_URL', 'https://link.payway.com.kh/ABAPAYTD422549V'),
    ],

    'google' => [
        'maps_key' => env('GOOGLE_MAPS_KEY'),
    ],
];
