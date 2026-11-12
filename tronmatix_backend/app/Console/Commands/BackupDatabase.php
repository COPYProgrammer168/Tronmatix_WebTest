<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup
                            {--no-offsite : Skip uploading the backup to object storage}';

    protected $description = 'Create a database backup (SQLite snapshot or PostgreSQL dump)';

    private bool $nothingToBackup = false;

    public function handle(): int
    {
        $driver = DB::connection()->getDriverName();
        $backupDir = config('db-backup.backup_path', storage_path('app/backups'));

        if (! $this->ensureBackupDirectory($backupDir)) {
            return self::FAILURE;
        }

        $filepath = match ($driver) {
            'sqlite' => $this->backupSqlite($backupDir),
            'pgsql' => $this->backupPostgres($backupDir),
            default => $this->unsupportedDriver($driver),
        };

        if ($filepath === null) {
            return $this->nothingToBackup ? self::SUCCESS : self::FAILURE;
        }

        $this->pruneOldBackups($backupDir);

        if (! $this->option('no-offsite')) {
            $this->uploadToOffsite($filepath);
        }

        return self::SUCCESS;
    }

    private function backupSqlite(string $backupDir): ?string
    {
        $database = DB::connection()->getConfig('database');

        if (! is_string($database) || $database === '' || $database === ':memory:') {
            $this->error('SQLite database is not a file: '.($database ?: '(not configured)'));

            return null;
        }

        if (! is_file($database) || filesize($database) === 0) {
            // Nothing to protect yet (fresh install); don't block migrations.
            $this->nothingToBackup = true;
            $this->warn("SQLite database file not found, nothing to back up: {$database}");

            return null;
        }

        $filename = pathinfo($database, PATHINFO_FILENAME)
            .'_'.now()->format('Y-m-d_His_u').'.sqlite';
        $filepath = $backupDir.DIRECTORY_SEPARATOR.$filename;

        try {
            // VACUUM INTO creates a consistent, standalone snapshot of the
            // database without blocking readers/writers for long.
            DB::statement('VACUUM INTO ?', [$filepath]);
        } catch (\Throwable $e) {
            @unlink($filepath);
            $this->error('SQLite backup failed: '.$e->getMessage());

            return null;
        }

        if (! is_file($filepath) || filesize($filepath) === 0) {
            @unlink($filepath);
            $this->error('SQLite backup failed: the snapshot was not created.');

            return null;
        }

        $this->info("Backup created: {$filepath} ({$this->formatSize(filesize($filepath))})");

        return $filepath;
    }

    private function backupPostgres(string $backupDir): ?string
    {
        $connection = DB::connection()->getConfig();

        $host = $connection['host'] ?? '127.0.0.1';
        $port = $connection['port'] ?? 5432;
        $database = $connection['database'] ?? 'laravel';
        $username = $connection['username'] ?? 'postgres';
        $password = $connection['password'] ?? '';
        $pgDump = config('db-backup.pg_dump_path', 'pg_dump');

        $filename = $database.'_'.now()->format('Y-m-d_His').'.sql';
        $filepath = $backupDir.DIRECTORY_SEPARATOR.$filename;

        $env = [];
        if ($password !== '') {
            $env['PGPASSWORD'] = $password;
        }

        $process = new Process([
            $pgDump,
            '--host', $host,
            '--port', (string) $port,
            '--username', $username,
            '--no-owner',
            '--no-acl',
            '--format=plain',
            '--clean',
            '--if-exists',
            '--file', $filepath,
            $database,
        ]);

        $process->setEnv($env);
        $process->setTimeout(600);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            $this->error('Backup failed: '.$e->getProcess()->getErrorOutput());

            return null;
        }

        $size = is_file($filepath) ? $this->formatSize(filesize($filepath)) : 'unknown size';
        $this->info("Backup created: {$filepath} ({$size})");

        return $filepath;
    }

    private function unsupportedDriver(string $driver): ?string
    {
        $this->error("Database backups are not supported for driver: {$driver}");

        return null;
    }

    private function uploadToOffsite(string $filepath): void
    {
        $diskName = config('db-backup.offsite_disk');

        if (! is_string($diskName) || $diskName === '') {
            return;
        }

        $prefix = trim((string) config('db-backup.offsite_path', 'database-backups'), '/');
        $latest = (string) config('db-backup.offsite_latest');

        if ($latest === '') {
            $latest = DB::connection()->getDriverName() === 'pgsql' ? 'latest.sql' : 'latest.sqlite';
        }

        $remote = ($prefix !== '' ? $prefix.'/' : '').$latest;

        $stream = @fopen($filepath, 'rb');

        if ($stream === false) {
            $this->warn('Offsite backup skipped: could not open the snapshot.');

            return;
        }

        try {
            $uploaded = Storage::disk($diskName)->put($remote, $stream);
        } catch (\Throwable $e) {
            $this->warn('Offsite backup failed: '.$e->getMessage());

            return;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if (! $uploaded) {
            $this->warn("Offsite backup failed: could not upload {$remote} to disk \"{$diskName}\".");

            return;
        }

        $this->info("Offsite backup uploaded: {$diskName}:{$remote}");
    }

    private function ensureBackupDirectory(string $backupDir): bool
    {
        if (is_dir($backupDir)) {
            return true;
        }

        if (! mkdir($backupDir, 0700, true) && ! is_dir($backupDir)) {
            $this->error("Failed to create backup directory: {$backupDir}");

            return false;
        }

        return true;
    }

    private function pruneOldBackups(string $backupDir): void
    {
        $keepLast = (int) config('db-backup.keep_last', 10);

        $files = array_merge(
            glob($backupDir.DIRECTORY_SEPARATOR.'*.sqlite') ?: [],
            glob($backupDir.DIRECTORY_SEPARATOR.'*.sql') ?: [],
        );

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

        $this->line('Pruned '.count($toDelete)." old backup(s); kept {$keepLast} latest.");
    }

    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        foreach ($units as $unit) {
            if ($bytes < 1024) {
                return round($bytes, 2).' '.$unit;
            }
            $bytes /= 1024;
        }

        return round($bytes, 2).' TB';
    }
}
