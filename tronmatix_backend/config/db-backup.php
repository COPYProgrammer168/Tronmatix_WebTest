<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Destructive Commands Requiring Backup
    |--------------------------------------------------------------------------
    |
    | Any Artisan command name listed here will trigger an automatic
    | pg_dump backup before the command is allowed to execute.
    |
    */
    'risky_commands' => [
        'migrate:fresh',
        'migrate:refresh',
        'db:wipe',
        'migrate:reset',
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Retention
    |--------------------------------------------------------------------------
    |
    | How many recent backups to keep. Older backups are pruned automatically
    | after each successful dump.
    |
    */
    'keep_last' => 10,

    /*
    |--------------------------------------------------------------------------
    | Backup Directory
    |--------------------------------------------------------------------------
    |
    | Where pg_dump output files are stored. Relative to the project root
    | via storage_path().
    |
    */
    'backup_path' => storage_path('app/backups'),

    /*
    |--------------------------------------------------------------------------
    | pg_dump / psql Binary Paths
    |--------------------------------------------------------------------------
    |
    | Override these in your .env if the binaries are not in PATH.
    | On Windows, pg_dump.exe often needs a full path here, e.g.:
    |
    |   PG_DUMP_PATH="C:\Program Files\PostgreSQL\17\bin\pg_dump.exe"
    |   PSQL_PATH="C:\Program Files\PostgreSQL\17\bin\psql.exe"
    |
    */
    'pg_dump_path' => env('PG_DUMP_PATH', 'pg_dump'),
    'psql_path'   => env('PSQL_PATH', 'psql'),

];
