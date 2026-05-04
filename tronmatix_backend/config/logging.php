<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace'   => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    */

    'channels' => [

        /*
         * Default stack.
         *
         * On Render/Railway/Heroku: set LOG_STACK=daily,stderr in your env vars.
         * stderr sends logs to the platform dashboard in real time.
         * daily keeps a rolling file on disk as backup.
         *
         * Locally: LOG_STACK=daily is fine (no stderr noise in terminal).
         */
        'stack' => [
            'driver'            => 'stack',
            'channels'          => explode(',', (string) env('LOG_STACK', 'daily,stderr')),
            'ignore_exceptions' => false,
        ],

        /*
         * General application log — rotating daily, 30-day retention.
         * Kept separate from security.log so you can grep each independently.
         */
        'single' => [
            'driver'               => 'single',
            'path'                 => storage_path('logs/laravel.log'),
            'level'                => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver'               => 'daily',
            'path'                 => storage_path('logs/laravel.log'),
            'level'                => env('LOG_LEVEL', 'debug'),
            'days'                 => env('LOG_DAILY_DAYS', 30), // was 14 — too short for attack patterns
            'replace_placeholders' => true,
        ],

        /*
         * Security channel — DEDICATED log for attack detection.
         *
         * Use this for ALL security-related events:
         *   - Failed login attempts
         *   - Rate limit hits
         *   - Session fingerprint mismatches
         *   - Session terminations
         *   - Banned account access attempts
         *
         * Usage in code:
         *   Log::channel('security')->warning('Auth: failed login', [...]);
         *
         * File: storage/logs/security.log (rotates daily, 90-day retention)
         * Level: 'notice' — skips debug/info noise, captures warning and above.
         *
         * To monitor in real time:
         *   tail -f storage/logs/security.log
         */
        'security' => [
            'driver'               => 'daily',
            'path'                 => storage_path('logs/security.log'),
            'level'                => env('LOG_SECURITY_LEVEL', 'notice'),
            'days'                 => env('LOG_SECURITY_DAYS', 90), // long retention for forensics
            'replace_placeholders' => true,
        ],

        /*
         * Stderr — required for Render/Railway/Heroku.
         *
         * These platforms capture stdout/stderr and show it in their log
         * dashboard. Without this, logs only exist on disk and are lost
         * when the container restarts.
         *
         * Set LOG_STACK=daily,stderr in your Render environment variables.
         */
        'stderr' => [
            'driver'       => 'monolog',
            'level'        => env('LOG_LEVEL', 'debug'),
            'handler'      => StreamHandler::class,
            'handler_with' => [
                'stream' => 'php://stderr',
            ],
            'formatter'  => env('LOG_STDERR_FORMATTER'),
            'processors' => [PsrLogMessageProcessor::class],
        ],

        /*
         * Slack — instant alerts for critical security events.
         *
         * Set LOG_SLACK_WEBHOOK_URL in your .env to enable.
         * Only fires at 'critical' level by default — use for:
         *   - Mass brute force (100+ attempts)
         *   - Confirmed session hijack
         *   - Admin account lockout
         *
         * To fire a Slack alert:
         *   Log::channel('slack')->critical('Admin brute force detected', [...]);
         */
        'slack' => [
            'driver'               => 'slack',
            'url'                  => env('LOG_SLACK_WEBHOOK_URL'),
            'username'             => env('LOG_SLACK_USERNAME', 'Tronmatix Security'),
            'emoji'                => env('LOG_SLACK_EMOJI', ':rotating_light:'),
            'level'                => env('LOG_SLACK_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver'       => 'monolog',
            'level'        => env('LOG_LEVEL', 'debug'),
            'handler'      => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host'             => env('PAPERTRAIL_URL'),
                'port'             => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://' . env('PAPERTRAIL_URL') . ':' . env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver'               => 'syslog',
            'level'                => env('LOG_LEVEL', 'debug'),
            'facility'             => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver'               => 'errorlog',
            'level'                => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver'  => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

    ],

];