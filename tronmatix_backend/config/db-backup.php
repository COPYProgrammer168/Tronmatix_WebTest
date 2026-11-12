<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Destructive Commands Requiring Backup
    |--------------------------------------------------------------------------
    |
    | Any Artisan command name listed here will trigger an automatic
    | database backup before the command is allowed to execute.
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
    | after each successful backup.
    |
    */

    'keep_last' => (int) env('DB_BACKUP_KEEP_LAST', 10),

    /*
    |--------------------------------------------------------------------------
    | Backup Directory
    |--------------------------------------------------------------------------
    |
    | Where backup files are stored. On Render, point this at the persistent
    | disk (e.g. /var/www/html/storage/data/backups) so backups survive
    | redeploys.
    |
    */

    'backup_path' => env('DB_BACKUP_PATH', storage_path('app/backups')),

    /*
    |--------------------------------------------------------------------------
    | Offsite Copies (object storage)
    |--------------------------------------------------------------------------
    |
    | When an offsite disk is configured (e.g. "s3_backup", backed by a private
    | Cloudflare R2 bucket), every backup is also uploaded as a latest snapshot. The
    | Docker entrypoint restores it automatically when the SQLite file on the
    | persistent disk is missing or empty, so data survives a lost disk or a
    | service recreated without one.
    |
    */

    'offsite_disk' => env('DB_BACKUP_DISK'),

    'offsite_path' => env('DB_BACKUP_S3_PATH', 'database-backups'),

    'offsite_latest' => env('DB_BACKUP_S3_LATEST'),

    /*
    |--------------------------------------------------------------------------
    | pg_dump / psql Binary Paths (PostgreSQL only)
    |--------------------------------------------------------------------------
    |
    | SQLite backups use VACUUM INTO and need no external binaries. These
    | paths are only used when the default connection driver is "pgsql".
    |
    | Override these in your .env if the binaries are not in PATH.
    | On Windows, pg_dump.exe often needs a full path here, e.g.:
    |
    |   PG_DUMP_PATH="C:\Program Files\PostgreSQL\17\bin\pg_dump.exe"
    |   PSQL_PATH="C:\Program Files\PostgreSQL\17\bin\psql.exe"
    |
    */

    'pg_dump_path' => env('PG_DUMP_PATH', 'pg_dump'),
    'psql_path' => env('PSQL_PATH', 'psql'),

];
