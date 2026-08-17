<?php

// app/Console/Commands/TelegramPoll.php
// ─────────────────────────────────────────────────────────────────────────────
// Polling mode — works for both local dev AND production (Render).
//
// To avoid 409 conflicts when the production server is also polling:
//   - On startup:  saves current webhook URL, sets webhook (kills any active
//                  getUpdates from other instances), then deletes webhook and
//                  starts polling.
//   - On shutdown: restores the original webhook URL so production takes over.
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

    // Saved webhook URL to restore on exit (avoids 409 conflicts)
    private ?string $savedWebhookUrl = null;

    // Exponential backoff state for 409 conflicts
    private int $backoffLevel = 0;
    private const BACKOFFS = [5, 10, 20, 40, 60]; // seconds
    private const MAX_BACKOFF = 60;
    private bool $hasLogged409 = false;

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

        // ── Environment gate: only auto-run when explicitly enabled ─────────────
        // Set TELEGRAM_POLLER_ENABLED=false in .env to disable this poller.
        // Useful when you want production webhook-only or local-only operation.
        $pollerEnabled = config('telegram_user.poller_enabled', true);

        if (! $pollerEnabled) {
            $this->info('[telegram-poll] Poller disabled via TELEGRAM_POLLER_ENABLED=false — exiting.');
            return self::SUCCESS;
        }

        // ── Take exclusive control of the bot ─────────────────────────────────
        // This kills any active getUpdates/webhook from other instances
        // (e.g. production Render) so THIS poller gets all updates.
        // The HTTPS temp webhook fallback uses the production frontend URL
        // because Telegram requires HTTPS for setWebhook.
        $this->disconnectOtherInstances();

        $timeout = min(50, (int) $this->option('timeout'));
        $limit   = (int) $this->option('limit');
        $offset  = 0;

        $this->info('🤖 Polling @tronmatix_notification_bot  (Ctrl+C to stop)');
        $this->info(str_repeat('─', 50));

        $spinner = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];
        $spinIdx = 0;
        $lastPulse = 0;

        // ── Poll loop ─────────────────────────────────────────────────────────
        while ($this->running) {
            try {
                // ── Spinner: animate while waiting for getUpdates ──────────────
                $spinIdx++;
                $s = $spinner[$spinIdx % count($spinner)];

                // Show a waiting pulse every 5s so the user knows it's alive
                $now = time();
                if ($now - $lastPulse >= 5) {
                    $lastPulse = $now;
                    $this->line(
                        "  <fg=cyan>{$s}</> <fg=green>listening...</><fg=gray>  ({$now})</>"
                    );
                }

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
                    $code = $res['error_code'] ?? 0;

                    if ($code === 409) {
                        $this->backoffLevel = min($this->backoffLevel + 1, count(self::BACKOFFS) - 1);
                        $wait = self::BACKOFFS[$this->backoffLevel];

                        if (! $this->hasLogged409) {
                            $this->hasLogged409 = true;
                            $this->warn('⚠️  409 Conflict — another instance is active. Backing off...');
                        } else {
                            $this->line("  <fg=gray>[409] backing off {$wait}s...</>");
                        }

                        $this->disconnectOtherInstances();
                        sleep($wait);
                    } else {
                        $this->warn('Telegram error: ' . json_encode($res));
                        sleep(3);
                    }
                    continue;
                }

                $updates = $res['result'] ?? [];

                if ($updates) {
                    $this->resetBackoff();
                }

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

        // ── Restore webhook on exit if we saved one ────────────────────────────
        $this->restoreWebhook();

        $this->info('[telegram-poll] Exited cleanly.');
        return self::SUCCESS;
    }

    /**
     * Save the current webhook URL, then force-disconnect other pollers.
     *
     * Calling setWebhook terminates any active getUpdates connection from other
     * bot instances (production Render). We then delete the webhook so we can
     * poll without a 409 conflict.
     */
    private function disconnectOtherInstances(): void
    {
        // 1. Save current webhook URL (if any)
        $info = Http::timeout(10)->withoutVerifying()
            ->get("{$this->apiBase}/getWebhookInfo")
            ->json();

        $currentUrl = $info['result']['url'] ?? null;
        if ($currentUrl && ! str_contains($currentUrl, '/api/telegram/bot-webhook')) {
            // Only save URLs that look like our webhook endpoint
            $currentUrl = null;
        }

        if ($currentUrl) {
            $this->savedWebhookUrl = $currentUrl;
            $this->line("  <fg=cyan>[setup]</> Saved existing webhook: {$currentUrl}");
        }

        // 2. Force-disconnect other pollers by setting a webhook.
        //    Telegram requires HTTPS for setWebhook. If local APP_URL is HTTP,
        //    use the production frontend URL (known to be HTTPS) as a temp target.
        //    This terminates any active getUpdates from Render's poller.
        $tempUrl = $currentUrl;
        if (! $tempUrl || ! str_starts_with($tempUrl, 'https://')) {
            // Fallback: use the mini-app URL (production frontend, always HTTPS)
            $fallback = config('services.telegram_user.mini_app_url', '');
            if ($fallback && str_starts_with($fallback, 'https://')) {
                $tempUrl = rtrim($fallback, '/') . '/api/telegram/bot-webhook';
            } else {
                $tempUrl = null;
            }
        }

        if ($tempUrl) {
            $set = Http::timeout(10)->withoutVerifying()->asJson()
                ->post("{$this->apiBase}/setWebhook", [
                    'url'                => $tempUrl,
                    'allowed_updates'    => ['message', 'callback_query'],
                    'drop_pending_updates' => true,
                ])
                ->json();

            if (($set['ok'] ?? false)) {
                $this->line('  <fg=cyan>[setup]</> Switched to webhook mode — disconnected other instances');
            } else {
                $this->warn('  Could not set webhook: ' . json_encode($set));
            }
        } else {
            $this->warn('  No HTTPS URL available — cannot force-disconnect other instances.');
        }

        // 3. Delete the webhook so we can poll
        $del = Http::timeout(10)->withoutVerifying()
            ->post("{$this->apiBase}/deleteWebhook", ['drop_pending_updates' => true])
            ->json();

        if ($del['ok'] ?? false) {
            $this->info('✅ Webhook cleared — polling mode active');
        } else {
            $this->warn('Could not clear webhook: ' . json_encode($del));
        }
    }

    /**
     * Re-register the saved webhook URL on shutdown so production (or webhook
     * mode) takes back over.
     */
    private function restoreWebhook(): void
    {
        if (! $this->savedWebhookUrl) {
            return;
        }

        $this->line('  <fg=cyan>[shutdown]</> Restoring webhook...');

        $res = Http::timeout(10)->withoutVerifying()->asJson()
            ->post("{$this->apiBase}/setWebhook", [
                'url'                => $this->savedWebhookUrl,
                'allowed_updates'    => ['message', 'callback_query'],
                'secret_token'       => config('services.telegram_user.webhook_secret') ?: null,
                'drop_pending_updates' => true,
            ])
            ->json();

        if ($res['ok'] ?? false) {
            $this->line("  <fg=green>[shutdown]</> Webhook restored: {$this->savedWebhookUrl}");
        } else {
            $this->warn('[shutdown] Failed to restore webhook: ' . json_encode($res));
        }
    }

    /**
     * Reset 409 backoff state after a successful poll with updates.
     */
    private function resetBackoff(): void
    {
        if ($this->backoffLevel !== 0 || $this->hasLogged409) {
            $this->backoffLevel = 0;
            $this->hasLogged409 = false;
            $this->line('  <fg=green>[ok]</> 409 cleared — back to normal polling');
        }
    }
}
