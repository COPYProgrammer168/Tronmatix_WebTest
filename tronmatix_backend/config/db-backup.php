<?php

return [
    'risky_commands' => [
        'migrate:fresh',
        'migrate:refresh',
        'db:wipe',
        'migrate:reset',
    ],

    'keep_last' => 10,

    'backup_path' => storage_path('app/backups'),

    'pg_dump_path' => env('PG_DUMP_PATH', 'pg_dump'),

    'psql_path' => env('PSQL_PATH', 'psql'),
];
