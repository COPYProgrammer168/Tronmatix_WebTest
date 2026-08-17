<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class RestoreDatabase extends Command
{
    protected $signature = 'db:restore
                            {file? : Specific backup file to restore}';

    protected $description = 'Restore the PostgreSQL database from a pg_dump backup';

    public function handle(): int
    {
        $connection = Config::get('database.connections.pgsql');

        $host     = $connection['host']     ?? '127.0.0.1';
        $port     = $connection['port']     ?? '5432';
        $database = $connection['database'] ?? 'laravel';
        $username = $connection['username'] ?? 'postgres';
        $password = $connection['password'] ?? '';

        $psqlPath   = Config::get('db-backup.psql_path', 'psql');
        $backupPath = Config::get('db-backup.backup_path', storage_path('app/backups'));

        if (! is_dir($backupPath)) {
            $this->error("Backup directory not found: {$backupPath}");

            return self::FAILURE;
        }

        $backupFile = $this->argument('file')
            ? $this->resolveBackupFile($this->argument('file'), $backupPath)
            : $this->selectBackupFile($backupPath);

        if ($backupFile === null) {
            return self::FAILURE;
        }

        $this->warn(
            'This will overwrite the current database "' . $database
            . '" with the contents of: ' . basename($backupFile)
        );

        if (! $this->confirm('Continue with restore?', false)) {
            $this->info('Restore cancelled.');

            return self::SUCCESS;
        }

        $this->info('Restoring database from: ' . basename($backupFile));

        // pg_dump (without --clean) cannot drop the tables it is about to
        // create. Dump the existing schema first so both old and new
        // backups restore cleanly into a database that is not empty.
        $this->info('Dropping existing tables...');
        $this->dropAllTables($connection);

        $command = [
            $psqlPath,
            '-h', $host,
            '-p', $port,
            '-U', $username,
            '-d', $database,
            '-v', 'ON_ERROR_STOP=1',
            '-f', $backupFile,
        ];

        $process = new Process($command);
        $process->setEnv(['PGPASSWORD' => $password]);
        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            $errorOutput = trim($process->getErrorOutput());
            $this->error('Database restore failed.');
            $this->line('Error output: ' . ($errorOutput ?: '(no error output)'));

            return self::FAILURE;
        }

        $this->info('Database restored successfully from: ' . basename($backupFile));

        return self::SUCCESS;
    }

    private function dropAllTables(array $connection): void
    {
        $connectionName = $connection['name'] ?? config('database.default');
        $schema         = $connection['search_path'] ?? 'public';

        try {
            $tables = DB::connection($connectionName)
                ->select("SELECT tablename FROM pg_tables WHERE schemaname = ?", [$schema]);
        } catch (\Throwable $e) {
            $this->error('Could not list tables: ' . $e->getMessage());

            return;
        }

        if (empty($tables)) {
            $this->info('No existing tables to drop.');

            return;
        }

        $conn = DB::connection($connectionName);
        $conn->statement('SET session_replication_role = replica;');

        try {
            foreach ($tables as $table) {
                $tableName = $table->tablename;
                $this->line("  Dropping {$schema}.{$tableName}");
                $conn->statement("DROP TABLE IF EXISTS {$schema}.{$tableName} CASCADE");
            }
        } finally {
            $conn->statement('SET session_replication_role = DEFAULT;');
        }
    }

    private function selectBackupFile(string $backupPath): ?string
    {
        $files = glob($backupPath . '/*.sql');

        if (empty($files)) {
            $this->error('No backup files found in: ' . $backupPath);

            return null;
        }

        usort($files, function ($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });

        $choices = [];
        foreach ($files as $file) {
            $size     = $this->formatBytes(filesize($file));
            $modified = date('Y-m-d H:i:s', filemtime($file));
            $choices[] = basename($file) . '  (' . $size . ', ' . $modified . ')';
        }

        $selected = $this->choice('Select a backup to restore:', $choices);

        $selectedBasename = explode('  (', $selected)[0];

        return $backupPath . DIRECTORY_SEPARATOR . $selectedBasename;
    }

    private function resolveBackupFile(string $argument, string $backupPath): ?string
    {
        if (file_exists($argument)) {
            return $argument;
        }

        $candidate = $backupPath . DIRECTORY_SEPARATOR . $argument;

        if (file_exists($candidate)) {
            return $candidate;
        }

        $this->error("Backup file not found: {$argument}");
        $this->line("Looked in: {$argument}");
        $this->line("Also tried: {$candidate}");

        return null;
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
