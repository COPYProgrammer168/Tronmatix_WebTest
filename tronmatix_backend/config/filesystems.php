<?php

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app/private'),
            'serve'  => true,
            'throw'  => false,
            'report' => false,
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw'      => false,
            'report'     => false,
        ],

        // Cloudflare R2 — S3-compatible storage
        // Render env vars:
        //   FILESYSTEM_DISK=s3
        //   AWS_ACCESS_KEY_ID=2b679ac59f3dad64d7aa14def5645ee1
        //   AWS_SECRET_ACCESS_KEY=64076e80be981955a7fefaffeaae52eb46f9524e488e7beff82f2e519f3aebea
        //   AWS_DEFAULT_REGION=auto
        //   AWS_BUCKET=tronmatix-storage
        //   AWS_ENDPOINT_URL=https://efa9fc81ab145bae274144ca40776866.r2.cloudflarestorage.com
        //   AWS_URL=https://pub-632ee2... (your R2 public URL)
        //   AWS_USE_PATH_STYLE_ENDPOINT=true
        's3' => [
            'driver'                  => 's3',
            'key'                     => env('AWS_ACCESS_KEY_ID'),
            'secret'                  => env('AWS_SECRET_ACCESS_KEY'),
            'region'                  => env('AWS_DEFAULT_REGION', 'auto'),
            'bucket'                  => env('AWS_BUCKET'),
            'url'                     => env('AWS_URL'),
            // FIX: Render uses AWS_ENDPOINT_URL (not AWS_ENDPOINT)
            // Check both keys for compatibility
            'endpoint'                => env('AWS_ENDPOINT_URL', env('AWS_ENDPOINT')),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', true),
            'visibility'              => 'public',
            'throw'                   => false,
            'report'                  => false,
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
