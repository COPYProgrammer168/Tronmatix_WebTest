<?php

// app/Console/Commands/TelegramPoll.php
// ─────────────────────────────────────────────────────────────────────────────
// LOCAL DEVELOPMENT ONLY — replaces webhook with long-polling.
// No HTTPS needed. No ngrok needed.
//
// Usage:
//   php artisan telegram:poll
//   php artisan telegram:poll --timeout=30
//   php artisan telegram:poll --limit=10
//
// Stop with Ctrl+C
// ─────────────────────────────────────────────────────────────────────────────

namespace App\Console\Commands;

use App\Services\TelegramBotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TelegramPoll extends Command
{
    protected $signature = 'telegram:poll
        {--timeout=20 : Long-poll timeout in seconds (max 50)}
        {--limit=10   : Max updates per request}';

    protected $description = '[LOCAL DEV] Poll Telegram for updates — no HTTPS/webhook needed';

    private string $token;
    private string $apiBase;

    public function handle(TelegramBotService $bot): int
    {
        $this->token   = config('services.telegram_user.bot_token', '');
        $this->apiBase = "https://api.telegram.org/bot{$this->token}";

        if (! $this->token) {
            $this->error('TELEGRAM_USER_BOT_TOKEN not set in .env');
            return self::FAILURE;
        }

        $timeout = min(50, (int) $this->option('timeout'));
        $limit   = (int) $this->option('limit');
        $offset  = 0;

        // ── Delete any existing webhook so polling works ───────────────────────
        $del = Http::timeout(10)->withoutVerifying()
            ->post("{$this->apiBase}/deleteWebhook", ['drop_pending_updates' => true])
            ->json();

        if ($del['ok'] ?? false) {
            $this->info('✅ Webhook cleared — polling mode active');
        } else {
            $this->warn('Could not clear webhook: ' . json_encode($del));
        }

        $this->info("🤖 Polling @tronmatix_notification_bot  (Ctrl+C to stop)");
        $this->info(str_repeat('─', 50));

        // ── Poll loop ─────────────────────────────────────────────────────────
        while (true) {
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

                if (! ($res['ok'] ?? false)) {
                    $this->warn('Telegram error: ' . json_encode($res));
                    sleep(3);
                    continue;
                }

                $updates = $res['result'] ?? [];

                foreach ($updates as $update) {
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
                $this->warn('Poll error: ' . $e->getMessage());
                sleep(3);
            }
        }
    }
}
