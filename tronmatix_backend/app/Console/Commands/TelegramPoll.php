<?php

// app/Console/Commands/TelegramPoll.php
// ─────────────────────────────────────────────────────────────────────────────
// Polling mode — works for both local dev AND production (Render).
//
// Usage:
//   php artisan telegram:poll
//   php artisan telegram:poll --timeout=25 --limit=10
//
// Supports graceful shutdown via SIGTERM (Render redeploy) and SIGINT (Ctrl+C).
// ─────────────────────────────────────────────────────────────────────────────

declare(ticks=1); // Required for pcntl_signal to fire between PHP opcodes

namespace App\Console\Commands;

use App\Services\TelegramBotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TelegramPoll extends Command
{
    protected $signature = 'telegram:poll
        {--timeout=20 : Long-poll timeout in seconds (max 50)}
        {--limit=10   : Max updates per request}';

    protected $description = 'Poll Telegram for updates (polling mode — local dev & production)';

    private string $token;
    private string $apiBase;

    // FIX: flag to break the poll loop on SIGTERM/SIGINT
    private bool $running = true;

    public function handle(TelegramBotService $bot): int
    {
        $this->token   = config('services.telegram_user.bot_token', '');
        $this->apiBase = "https://api.telegram.org/bot{$this->token}";

        if (! $this->token) {
            $this->error('TELEGRAM_USER_BOT_TOKEN not set in .env');
            return self::FAILURE;
        }

        // ── FIX: Register signal handlers so Render SIGTERM exits cleanly ─────
        // Without this, the old instance keeps polling → Error 409 on new deploy.
        if (extension_loaded('pcntl')) {
            pcntl_signal(SIGTERM, function () {
                $this->warn('[telegram-poll] SIGTERM received — stopping gracefully...');
                $this->running = false;
            });
            pcntl_signal(SIGINT, function () {
                $this->warn('[telegram-poll] SIGINT received — stopping...');
                $this->running = false;
            });
        }

        $timeout = min(50, (int) $this->option('timeout'));
        $limit   = (int) $this->option('limit');
        $offset  = 0;

        // ── FIX: Only deleteWebhook once at startup, not on every loop restart ─
        $del = Http::timeout(10)->withoutVerifying()
            ->post("{$this->apiBase}/deleteWebhook", ['drop_pending_updates' => false])
            ->json();

        if ($del['ok'] ?? false) {
            $this->info('✅ Webhook cleared — polling mode active');
        } else {
            $this->warn('Could not clear webhook: ' . json_encode($del));
        }

        $this->info('🤖 Polling @tronmatix_notification_bot  (Ctrl+C to stop)');
        $this->info(str_repeat('─', 50));

        // ── Poll loop ─────────────────────────────────────────────────────────
        while ($this->running) {
            try {
                $res = Http::timeout($timeout + 5)
                    ->withoutVerifying()
                    ->get("{$this->apiBase}/getUpdates", [
                        'offset'          => $offset,
                        'limit'           => $limit,
                        'timeout'         => $timeout,
                        'allowed_updates' => ['message', 'callback_query'],
                    ])
                    ->json();

                // Check signal again after long-poll returns
                if (! $this->running) {
                    break;
                }

                if (! ($res['ok'] ?? false)) {
                    $this->warn('Telegram error: ' . json_encode($res));
                    sleep(3);
                    continue;
                }

                $updates = $res['result'] ?? [];

                foreach ($updates as $update) {
                    if (! $this->running) {
                        break 2; // Exit both foreach and while
                    }

                    // Advance offset past this update
                    $offset = $update['update_id'] + 1;

                    // Log the incoming message
                    $chatId = $update['message']['chat']['id']
                        ?? $update['callback_query']['message']['chat']['id']
                        ?? '?';
                    $text   = $update['message']['text']
                        ?? ('callback: ' . ($update['callback_query']['data'] ?? '?'));

                    $this->line(
                        '<fg=cyan>[' . now()->format('H:i:s') . ']</> '
                        . "<fg=yellow>chat:{$chatId}</> "
                        . "<fg=white>{$text}</>"
                    );

                    // Pass to the same bot handler used by the webhook
                    try {
                        $bot->handleUpdate($update);
                        $this->line('  <fg=green>→ handled ✓</>');
                    } catch (\Throwable $e) {
                        $this->warn('  → handler error: ' . $e->getMessage());
                    }
                }

            } catch (\Throwable $e) {
                if (! $this->running) {
                    break;
                }
                $this->warn('Poll error: ' . $e->getMessage());
                sleep(3);
            }
        }

        $this->info('[telegram-poll] Exited cleanly.');
        return self::SUCCESS;
    }
}