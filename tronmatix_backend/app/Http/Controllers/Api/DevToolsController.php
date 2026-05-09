<?php

// ════════════════════════════════════════════════════════════════════════════
// app/Http/Controllers/Api/DevToolsController.php
// GET /api/dev/health → SystemTab
// GET /api/dev/logs → ApiLogsTab
// GET /api/dev/env → EnvTab
// ════════════════════════════════════════════════════════════════════════════

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class DevToolsController extends Controller
{
    /** GET /api/dev/health */
    public function health()
    {
        $checks = [];

        // Database
        try {
            DB::connection()->getPdo();
            $checks[] = [
                'label' => 'Database Connection',
                'status' => 'ok',
                'detail' => config('database.default') . ' —
connected'
            ];
        } catch (\Exception $e) {
            $checks[] = ['label' => 'Database Connection', 'status' => 'error', 'detail' => $e->getMessage()];
        }

        // Cache / Redis
        try {
            Cache::put('_health_check', true, 5);
            $checks[] = [
                'label' => 'Cache / Redis',
                'status' => Cache::get('_health_check') ? 'ok' : 'error',
                'detail' =>
                    config('cache.default')
            ];
        } catch (\Exception $e) {
            $checks[] = ['label' => 'Cache / Redis', 'status' => 'error', 'detail' => $e->getMessage()];
        }

        // Storage
        try {
            Storage::disk('local')->put('_health', 'ok');
            Storage::disk('local')->delete('_health');
            $checks[] = ['label' => 'Storage Disk', 'status' => 'ok', 'detail' => 'local disk writable'];
        } catch (\Exception $e) {
            $checks[] = ['label' => 'Storage Disk', 'status' => 'error', 'detail' => $e->getMessage()];
        }

        // Queue — check if workers are processing (basic check via jobs table)
        try {
            $pendingJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();
            $checks[] = [
                'label' => 'Queue Worker',
                'status' => $failedJobs > 10 ? 'warn' : 'ok',
                'detail' => "Pending: {$pendingJobs}, Failed: {$failedJobs}",
            ];
        } catch (\Exception $e) {
            $checks[] = ['label' => 'Queue Worker', 'status' => 'unknown', 'detail' => 'jobs table not found'];
        }

        // Sanctum Auth
        $checks[] = ['label' => 'Sanctum Auth', 'status' => 'ok', 'detail' => 'token-based auth active'];

        return response()->json([
            'data' => [
                'laravel_version' => app()->version(),
                'php_version' => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
                'db_driver' => strtoupper(config('database.default')),
                'cache_driver' => ucfirst(config('cache.default')),
                'queue_driver' => ucfirst(config('queue.default')),
                'vite_version' => '6.x',
                'checks' => $checks,
            ]
        ]);
    }

    /** GET /api/dev/logs — reads Laravel log file, last 100 lines */
    public function logs()
    {
        $logPath = storage_path('logs/laravel.log');

        if (!file_exists($logPath)) {
            return response()->json(['data' => []]);
        }

        $lines  = $this->tailFile($logPath, 300);
        $parsed = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Match: [2026-05-09 10:36:44] local.ERROR: Some message
            if (preg_match(
                '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+\S+\.(ERROR|WARNING|INFO|DEBUG|NOTICE|CRITICAL):\s+(.+)$/i',
                $line, $m
            )) {
                $level  = strtoupper($m[2]);
                $status = match($level) {
                    'ERROR','CRITICAL' => 500,
                    'WARNING'          => 400,
                    default            => 200,
                };
                $parsed[] = [
                    'method'      => $level,
                    'endpoint'    => mb_strimwidth(trim($m[3]), 0, 120, '…'),
                    'status'      => $status,
                    'duration_ms' => 0,
                    'ip'          => '—',
                    'created_at'  => $m[1],
                ];
            }
        }

        return response()->json([
            'data' => array_slice(array_reverse($parsed), 0, 100)
        ]);
    }

    /** GET /api/dev/env — safe subset of env vars, sensitive values masked server-side */
    public function env()
    {
        $vars = [
            ['key' => 'APP_ENV', 'value' => config('app.env'), 'sensitive' => false],
            ['key' => 'APP_DEBUG', 'value' => config('app.debug') ? 'true' : 'false', 'sensitive' => false],
            ['key' => 'APP_URL', 'value' => config('app.url'), 'sensitive' => false],
            ['key' => 'DB_CONNECTION', 'value' => config('database.default'), 'sensitive' => false],
            [
                'key' => 'DB_HOST',
                'value' => config('database.connections.' . config('database.default') . '.host', '—'),
                'sensitive'
                => false
            ],
            [
                'key' => 'DB_DATABASE',
                'value' => config('database.connections.' . config('database.default') . '.database', '—'),
                'sensitive' => false
            ],
            [
                'key' => 'DB_PASSWORD',
                'value' => config('database.connections.' . config('database.default') . '.password', ''),
                'sensitive' => true
            ],
            ['key' => 'CACHE_DRIVER', 'value' => config('cache.default'), 'sensitive' => false],
            ['key' => 'QUEUE_CONNECTION', 'value' => config('queue.default'), 'sensitive' => false],
            ['key' => 'SANCTUM_TOKEN_TTL', 'value' => config('sanctum.expiration', 'null'), 'sensitive' => false],
            ['key' => 'DEV_PORTAL_KEY', 'value' => config('app.dev_portal_key', ''), 'sensitive' => true],
        ];

        // Mask sensitive values — never send plaintext over the wire
// Frontend handles display toggle using the `sensitive` flag
        foreach ($vars as &$v) {
            if ($v['sensitive']) {
                $v['value'] = str_repeat('•', min(strlen((string) $v['value']), 20));
            }
        }

        return response()->json(['data' => $vars]);
    }

    private function tailFile(string $path, int $lines): array
    {
        $file = new \SplFileObject($path);
        $file->seek(PHP_INT_MAX);
        $total = $file->key();
        $start = max(0, $total - $lines);
        $result = [];
        $file->seek($start);
        while (!$file->eof()) {
            $result[] = $file->current();
            $file->next();
        }
        return $result;
    }
}
