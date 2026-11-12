<?php

// app/Console/Commands/DevStart.php
// ─────────────────────────────────────────────────────────────────────────────
// Composite dev command — runs `php artisan serve` + `php artisan telegram:poll`
// in one terminal so backend devs don't need the root-level `npm run dev`.
//
// Usage:
//   php artisan dev:start
//   php artisan dev:start --no-poll
//   php artisan dev:start --port=8000 --poll-timeout=10
// ─────────────────────────────────────────────────────────────────────────────

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class DevStart extends Command
{
    protected $signature = 'dev:start
        {--host=127.0.0.1 : The host address to serve on}
        {--port=8000 : The port to serve on}
        {--poll-timeout=25 : Telegram poll timeout in seconds}
        {--no-poll : Skip starting the Telegram poller}';

    protected $description = 'Start the dev server + Telegram poller together';

    public function handle(): int
    {
        $processes = [];

        // ── 1. Laravel dev server ─────────────────────────────────────────────
        $serve = new Process([
            PHP_BINARY, 'artisan', 'serve',
            '--host=' . $this->option('host'),
            '--port=' . $this->option('port'),
        ], base_path());
        $serve->setTimeout(null);
        $serve->start(function ($type, $buffer) {
            foreach (explode("\n", trim($buffer)) as $line) {
                if ($line !== '') {
                    $this->output->writeln("  <fg=cyan>[serve]</> {$line}");
                }
            }
        });
        $processes[] = $serve;
        $this->info('Dev server starting on http://' . $this->option('host') . ':' . $this->option('port'));

        // ── 2. Telegram poller (optional) ─────────────────────────────────────
        if (! $this->option('no-poll')) {
            $token = config('services.telegram_user.bot_token');
            if ($token) {
                $poll = new Process([
                    PHP_BINARY, 'artisan', 'telegram:poll',
                    '--timeout=' . $this->option('poll-timeout'),
                    '--limit=10',
                ], base_path());
                $poll->setTimeout(null);
                $poll->start(function ($type, $buffer) {
                    foreach (explode("\n", trim($buffer)) as $line) {
                        if ($line !== '') {
                            $this->output->writeln("  <fg=yellow>[poller]</> {$line}");
                        }
                    }
                });
                $processes[] = $poll;
                $this->info('Telegram poller started.');
            } else {
                $this->warn('TELEGRAM_USER_BOT_TOKEN not set — skipping poller.');
            }
        }

        // ── 3. Wait for Ctrl+C ───────────────────────────────────────────────
        $this->info('Press Ctrl+C to stop all processes.');
        $this->newLine();

        // Register signal handler for graceful shutdown
        if (extension_loaded('pcntl')) {
            pcntl_signal(SIGINT, function () use ($processes) {
                $this->newLine();
                $this->warn('Shutting down...');
                foreach ($processes as $p) {
                    if ($p->isRunning()) {
                        $p->stop(10, SIGTERM);
                    }
                }
            });
        }

        // Monitor processes
        while (true) {
            if (extension_loaded('pcntl')) {
                pcntl_signal_dispatch();
            }

            $allDead = true;
            foreach ($processes as $i => $p) {
                if ($p->isRunning()) {
                    $allDead = false;
                } elseif ($p->isTerminated()) {
                    $this->warn("Process #{$i} exited ({$p->getExitCode()})");
                }
            }

            if ($allDead) {
                break;
            }

            usleep(200_000); // 200ms
        }

        return self::SUCCESS;
    }
}
