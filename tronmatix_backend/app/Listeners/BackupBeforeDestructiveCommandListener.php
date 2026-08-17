<?php

namespace App\Listeners;

use App\Console\Commands\BackupDatabase;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class BackupBeforeDestructiveCommandListener
{
    public function handle(CommandStarting $event): void
    {
        $commandName = $event->command;

        if (!in_array($commandName, Config::get('db-backup.risky_commands', []), true)) {
            return;
        }

        if (!app()->runningInConsole()) {
            return;
        }

        // Replace current output with the backup banner so the user knows
        // why the destructive command is delayed.
        $event->output->writeln([
            '',
            "⚠️  <fg=yellow>DESTRUCTIVE COMMAND DETECTED: {$commandName}</>",
            '    Creating a safety backup before proceeding...',
            '',
        ]);

        $exitCode = Artisan::call(BackupDatabase::class);

        if ($exitCode !== 0) {
            $event->output->writeln([
                '',
                '❌ <fg=red>Backup failed. For safety, the destructive command was cancelled.</>',
                '   Run the backup manually with: php artisan db:backup',
                '',
            ]);

            throw new \RuntimeException(
                "Automatic backup failed for {$commandName}. Destructive command aborted for safety."
            );
        }

        $event->output->writeln([
            '',
            "✅ <fg=green>Backup complete. Proceeding with {$commandName}...</>",
            '',
        ]);
    }
}
