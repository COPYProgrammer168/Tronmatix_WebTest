<?php

namespace App\Listeners;

use App\Console\Commands\BackupDatabase;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class BackupBeforeDestructiveCommandListener
{
    private array $riskyCommands;

    private ?string $pendingCommand = null;

    private ?int $backupExitCode = null;

    public function __construct()
    {
        $this->riskyCommands = Config::get('db-backup.risky_commands', []);
    }

    public function handle(CommandStarting|CommandFinished $event): void
    {
        if ($event instanceof CommandStarting) {
            $commandName = $event->command;

            if (! in_array($commandName, $this->riskyCommands, true)) {
                return;
            }

            $this->pendingCommand = $commandName;
            $this->backupExitCode = Artisan::call('db:backup');
        }

        if ($event instanceof CommandFinished) {
            if ($this->pendingCommand === null) {
                return;
            }

            if ($this->backupExitCode !== 0) {
                $backupOutput = Artisan::output();

                $event->output->writeln('');
                $event->output->writeln('<fg=red;options=bold>ERROR: Pre-command backup failed.</>');
                $event->output->writeln(
                    '<fg=red>The risky command "' . $this->pendingCommand . '" has been cancelled for safety.</>'
                );
                $event->output->writeln('<fg=red>Backup output:</>');
                $event->output->writeln($backupOutput);

                exit(1);
            }

            $this->pendingCommand = null;
            $this->backupExitCode = null;
        }
    }
}
