<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Create a pg_dump backup of the PostgreSQL database';

    public function handle(): int
    {
        $connection = config('database.connections.pgsql');

        $host     = $connection['host'] ?? '127.0.0.1';
        $port     = $connection['port'] ?? 5432;
        $database = $connection['database'] ?? 'laravel';
        $username = $connection['username'] ?? 'postgres';
        $password = $connection['password'] ?? '';
        $pgDump   = config('db-backup.pg_dump_path', 'pg_dump');
        $backupDir = config('db-backup.backup_path', storage_path('app/backups'));

        if (!is_dir($backupDir)) {
            if (!mkdir($backupDir, 0700, true) && !is_dir($backupDir)) {
                $this->error("Failed to create backup directory: {$backupDir}");
                return 1;
            }
        }

        $filename = $database . '_' . now()->format('Y-m-d_His') . '.sql';
        $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $env = [];
        if ($password !== '') {
            $env['PGPASSWORD'] = $password;
        }

        $process = new Process([
            $pgDump,
            '--host',     $host,
            '--port',     (string) $port,
            '--username', $username,
            '--no-owner',
            '--no-acl',
            '--format=plain',
            '--file',     $filepath,
            $database,
        ]);

        $process->setEnv($env);
        $process->setTimeout(600);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            $this->error('Backup failed: ' . $e->getProcess()->getErrorOutput());
            return 1;
        }

        $size = is_file($filepath) ? round(filesize($filepath) / 1024, 1) . ' KB' : 'unknown size';
        $this->info("Backup created: {$filepath} ({$size})");

        $this->pruneOldBackups($backupDir);

        return 0;
    }

    private function pruneOldBackups(string $backupDir): void
    {
        $keepLast = (int) config('db-backup.keep_last', 10);

        $files = glob($backupDir . DIRECTORY_SEPARATOR . '*.sql');
        if (count($files) <= $keepLast) {
            return;
        }

        usort($files, function ($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });

        $toDelete = array_slice($files, $keepLast);
        foreach ($toDelete as $file) {
            @unlink($file);
        }

        $this->line("Pruned " . count($toDelete) . " old backup(s); kept {$keepLast} latest.");
    }
}
