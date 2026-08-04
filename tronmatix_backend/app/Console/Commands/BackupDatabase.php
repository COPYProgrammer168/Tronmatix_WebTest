<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';

    protected $description = 'Create a pg_dump backup of the PostgreSQL database';

    public function handle(): int
    {
        $connection = Config::get('database.connections.pgsql');

        $host     = $connection['host']     ?? '127.0.0.1';
        $port     = $connection['port']     ?? '5432';
        $database = $connection['database'] ?? 'laravel';
        $username = $connection['username'] ?? 'postgres';
        $password = $connection['password'] ?? '';
        $schema   = $connection['search_path'] ?? 'public';

        $pgDumpPath = Config::get('db-backup.pg_dump_path', 'pg_dump');
        $backupPath = Config::get('db-backup.backup_path', storage_path('app/backups'));

        if (! is_dir($backupPath) && ! mkdir($backupPath, 0755, true) && ! is_dir($backupPath)) {
            $this->error("Unable to create backup directory: {$backupPath}");

            return self::FAILURE;
        }

        $filename   = $database . '_' . now()->format('Y-m-d_His') . '.sql';
        $outputFile = $backupPath . DIRECTORY_SEPARATOR . $filename;

        $command = [
            $pgDumpPath,
            '-h', $host,
            '-p', $port,
            '-U', $username,
            '-d', $database,
            '--format=plain',
            '--no-owner',
            '--no-acl',
            '-f', $outputFile,
        ];

        if ($schema !== 'public') {
            $command[] = '--schema=' . $schema;
        }

        $process = new Process($command);
        $process->setEnv(['PGPASSWORD' => $password]);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            $errorOutput = trim($process->getErrorOutput());
            $this->error('Database backup failed.');
            $this->line('Error output: ' . ($errorOutput ?: '(no error output)'));

            return self::FAILURE;
        }

        $this->info('Backup created: ' . $outputFile);
        $this->line('Size: ' . $this->formatBytes(filesize($outputFile)));

        $this->pruneOldBackups($backupPath);

        return self::SUCCESS;
    }

    private function pruneOldBackups(string $backupPath): void
    {
        $keepLast = (int) Config::get('db-backup.keep_last', 10);

        $files = glob($backupPath . '/*.sql');

        if (count($files) <= $keepLast) {
            return;
        }

        usort($files, function ($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });

        $toDelete = array_slice($files, $keepLast);

        foreach ($toDelete as $file) {
            @unlink($file);
            $this->line("Pruned old backup: " . basename($file));
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        foreach ($units as $unit) {
            if ($bytes < 1024) {
                return round($bytes, 2) . ' ' . $unit;
            }
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' TB';
    }
}
