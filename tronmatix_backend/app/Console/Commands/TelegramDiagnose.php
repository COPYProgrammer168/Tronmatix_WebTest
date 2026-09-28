<?php

// app/Console/Commands/TelegramDiagnose.php
// Run with:  php artisan telegram:diagnose

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TelegramDiagnose extends Command
{
    protected $signature   = 'telegram:diagnose';
    protected $description = 'Check both Telegram bots configuration and connectivity';

    public function handle(): int
    {
        $this->newLine();
        $this->info('═══════════════════════════════════════════');
        $this->info('  TELEGRAM DUAL-BOT DIAGNOSTIC');
        $this->info('═══════════════════════════════════════════');
        $this->newLine();

        $this->checkBot(
            label:    'BOT 1 — Admin (TelegramService)',
            token:    config('services.telegram.bot_token'),
            chatId:   config('services.telegram.chat_id'),
            envToken: 'TELEGRAM_BOT_TOKEN',
            envChat:  'TELEGRAM_CHAT_ID',
        );

        $this->newLine();

        $this->checkBot(
            label:    'BOT 2 — User (TelegramBotService)',
            token:    config('services.telegram_user.bot_token'),
            chatId:   config('services.telegram_user.chat_id'),
            envToken: 'TELEGRAM_USER_BOT_TOKEN',
            envChat:  'TELEGRAM_CHAT_ID',
            webhook:  true,
            webhookSecret: config('services.telegram_user.webhook_secret'),
            miniAppUrl:    config('services.telegram_user.mini_app_url'),
        );

        $this->newLine();
        $this->checkRoutes();

        $this->newLine();
        $this->checkMigration();

        $this->newLine();
        $this->info('═══════════════════════════════════════════');

        return self::SUCCESS;
    }

    private function checkBot(
        string  $label,
        ?string $token,
        ?string $chatId,
        string  $envToken,
        string  $envChat,
        bool    $webhook = false,
        ?string $webhookSecret = null,
        ?string $miniAppUrl = null,
    ): void {
        $this->line("<fg=cyan>── {$label}</>");

        // 1. Token present?
        if (! $token) {
            $this->error("  ✗ {$envToken} is missing in .env");
            return;
        }
        $this->line("  <fg=green>✓</> Token found: ".substr($token, 0, 10).'...');

        // 2. Chat ID present?
        if (! $chatId) {
            $this->warn("  ⚠ {$envChat} is empty — owner alerts will not send");
        } else {
            $this->line("  <fg=green>✓</> Chat ID: {$chatId}");
        }

        // 3. Call Telegram getMe to verify the token is valid
        $this->line('  Calling getMe...');
        try {
            $res = Http::timeout(8)->withoutVerifying()
                ->get("https://api.telegram.org/bot{$token}/getMe");

            if ($res->successful() && ($res->json('ok') === true)) {
                $bot = $res->json('result');
                $this->line("  <fg=green>✓</> Bot valid: @{$bot['username']} ({$bot['first_name']})");
            } else {
                $this->error('  ✗ getMe failed: '.$res->body());
                $this->warn("  → Check {$envToken} — the token may be wrong or revoked");
                return;
            }
        } catch (\Throwable $e) {
            $this->error('  ✗ Network error calling Telegram API: '.$e->getMessage());
            return;
        }

        // 4. Webhook info (Bot 2 only)
        if ($webhook) {
            $this->line('  Checking webhook...');
            try {
                $res = Http::timeout(8)->withoutVerifying()
                    ->get("https://api.telegram.org/bot{$token}/getWebhookInfo");

                $info = $res->json('result') ?? [];
                $url  = $info['url'] ?? '';

                if (! $url) {
                    $this->error('  ✗ Webhook NOT registered');
                    $this->warn('  → Run:  php artisan telegram:setup');
                } else {
                    $this->line("  <fg=green>✓</> Webhook URL: {$url}");

                    $pendingCount = $info['pending_update_count'] ?? 0;
                    if ($pendingCount > 0) {
                        $this->warn("  ⚠ {$pendingCount} pending updates (bot may be slow)");
                    }

                    $lastError = $info['last_error_message'] ?? null;
                    if ($lastError) {
                        $this->error("  ✗ Last webhook error: {$lastError}");
                        $this->warn('  → Your server must be reachable over HTTPS on the webhook URL');
                    } else {
                        $this->line('  <fg=green>✓</> No webhook errors');
                    }
                }
            } catch (\Throwable $e) {
                $this->error('  ✗ Could not fetch webhook info: '.$e->getMessage());
            }

            // Secret token
            if (! $webhookSecret) {
                $this->warn('  ⚠ TELEGRAM_USER_WEBHOOK_SECRET not set (less secure in production)');
            } else {
                $this->line('  <fg=green>✓</> Webhook secret configured');
            }

            // Mini app URL
            if (! $miniAppUrl) {
                $this->warn('  ⚠ TELEGRAM_MINI_APP_URL not set — "Open App" button will be hidden');
            } else {
                $this->line("  <fg=green>✓</> Mini App URL: {$miniAppUrl}");
            }
        }
    }

    private function checkRoutes(): void
    {
        $this->line('<fg=cyan>── Routes</>');

        $routes = collect(\Illuminate\Support\Facades\Route::getRoutes()->getRoutes());

        $required = [
            'POST api/telegram/bot-webhook'  => false,
            'GET api/telegram/status'        => false,
            'POST api/telegram/connect'      => false,
            'POST api/telegram/disconnect'   => false,
            'POST api/telegram/test-message' => false,
        ];

        foreach ($routes as $route) {
            $methods = implode('|', $route->methods());
            $uri     = $route->uri();

            foreach (array_keys($required) as $key) {
                [$method, $path] = explode(' ', $key);
                if (str_contains($methods, $method) && str_contains($uri, ltrim($path, 'api/'))) {
                    $required[$key] = true;
                }
            }
        }

        foreach ($required as $route => $found) {
            if ($found) {
                $this->line("  <fg=green>✓</> {$route}");
            } else {
                $this->error("  ✗ MISSING: {$route}");
            }
        }

        if (in_array(false, $required, true)) {
            $this->warn('  → Add missing routes to routes/api.php (see api_bot_routes.php)');
        }
    }

    private function checkMigration(): void
    {
        $this->line('<fg=cyan>── Database columns (users table)</>');

        try {
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing('users');
            $needed  = ['telegram_chat_id', 'telegram_username', 'telegram_connected_at'];

            foreach ($needed as $col) {
                if (in_array($col, $columns)) {
                    $this->line("  <fg=green>✓</> users.{$col}");
                } else {
                    $this->error("  ✗ MISSING column: users.{$col}");
                }
            }

            if (! array_intersect($needed, $columns) === $needed) {
                $this->warn('  → Run:  php artisan migrate');
            }
        } catch (\Throwable $e) {
            $this->error('  ✗ Could not read users table: '.$e->getMessage());
        }
    }
}
