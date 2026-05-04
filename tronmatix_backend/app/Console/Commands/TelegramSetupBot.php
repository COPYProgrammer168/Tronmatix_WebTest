<?php

// app/Console/Commands/TelegramSetupBot.php

namespace App\Console\Commands;

use App\Services\TelegramBotService;
use Illuminate\Console\Command;

/**
 * Usage:
 *   php artisan telegram:setup
 *   php artisan telegram:setup --url=https://abc123.ngrok-free.app
 *   php artisan telegram:setup --info
 *   php artisan telegram:setup --commands-only
 *   php artisan telegram:setup --delete
 */
class TelegramSetupBot extends Command
{
    protected $signature = 'telegram:setup
        {--url=           : Custom HTTPS base URL — use ngrok URL for local dev}
        {--info           : Show current webhook info and exit}
        {--commands-only  : Skip webhook, only register bot commands}
        {--delete         : Remove the registered webhook}';

    protected $description = 'Register Telegram user-bot webhook and commands';

    public function handle(TelegramBotService $bot): int
    {
        // ── --info ────────────────────────────────────────────────────────────
        if ($this->option('info')) {
            $info = $bot->getWebhookInfo();
            $this->info('Current webhook info:');
            $this->line(json_encode($info['result'] ?? $info, JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        // ── --delete ──────────────────────────────────────────────────────────
        if ($this->option('delete')) {
            $result = $bot->deleteWebhook();
            if ($result['ok'] ?? false) {
                $this->info('✅ Webhook deleted.');
            } else {
                $this->error('❌ Failed: ' . json_encode($result));
                return self::FAILURE;
            }
            return self::SUCCESS;
        }

        // ── Webhook registration ──────────────────────────────────────────────
        if (! $this->option('commands-only')) {

            $baseUrl = rtrim($this->option('url') ?: config('app.url'), '/');

            // Telegram REQUIRES https:// — catch http:// early with a clear message
            if (! str_starts_with($baseUrl, 'https://')) {
                $this->newLine();
                $this->error('❌ Telegram requires HTTPS. Got: ' . $baseUrl);
                $this->newLine();
                $this->line('<options=bold>For local development — use ngrok:</>');
                $this->line('  Step 1: <comment>winget install ngrok</comment>');
                $this->line('  Step 2: <comment>ngrok http 8000</comment>');
                $this->line('  Step 3: Copy the https://xxxx.ngrok-free.app URL, then:');
                $this->line('          <comment>php artisan telegram:setup --url=https://xxxx.ngrok-free.app</comment>');
                $this->newLine();
                $this->line('<options=bold>For production — set in .env:</>');
                $this->line('  <comment>APP_URL=https://yourdomain.com</comment>');
                $this->newLine();
                return self::FAILURE;
            }

            $webhookUrl = $baseUrl . '/api/telegram/bot-webhook';
            $this->info("Registering webhook: {$webhookUrl}");

            $result = $bot->registerWebhook($webhookUrl);

            if ($result['ok'] ?? false) {
                $this->info('✅ Webhook registered!');
            } else {
                $this->error('❌ Registration failed:');
                $this->line(json_encode($result, JSON_PRETTY_PRINT));
                return self::FAILURE;
            }
        }

        // ── Bot commands ──────────────────────────────────────────────────────
        $this->info('Setting bot commands...');
        $cmdResult = $bot->setBotCommands();

        if ($cmdResult['ok'] ?? false) {
            $this->info('✅ Commands registered!');
            $this->table(
                ['Command', 'Description'],
                [
                    ['/start',   'Welcome message & open app'],
                    ['/orders',  'View your recent orders'],
                    ['/status',  'Track a specific order'],
                    ['/profile', 'View your account info'],
                    ['/help',    'Show all commands'],
                ]
            );
        } else {
            $this->warn('⚠️  Commands failed: ' . json_encode($cmdResult));
        }

        return self::SUCCESS;
    }
}
