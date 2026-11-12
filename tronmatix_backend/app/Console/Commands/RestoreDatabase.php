<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class RestoreDatabase extends Command
{
    protected $signature = 'db:restore
                            {file? : Specific backup file to restore}
                            {--cloud : Restore the latest backup uploaded to object storage}
                            {--force : Restore without asking for confirmation}';

    protected $description = 'Restore the database from a backup (SQLite snapshot or PostgreSQL dump)';

    public function handle(): int
    {
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'sqlite' => $this->restoreSqlite(),
            'pgsql' => $this->restorePostgres(),
            default => $this->unsupportedDriver($driver),
        };
    }

    private function restoreSqlite(): int
    {
        $database = DB::connection()->getConfig('database');

        if (! is_string($database) || $database === '' || $database === ':memory:') {
            $this->error('SQLite database is not a file: '.($database ?: '(not configured)'));

            return self::FAILURE;
        }

        $backupPath = Config::get('db-backup.backup_path', storage_path('app/backups'));

        if (! is_dir($backupPath)) {
            $this->error("Backup directory not found: {$backupPath}");

            return self::FAILURE;
        }

        $temporaryFile = null;

        if ($this->option('cloud')) {
            $temporaryFile = $this->downloadLatestOffsite('sqlite', $backupPath);

            if ($temporaryFile === null) {
                return self::FAILURE;
            }

            $backupFile = $temporaryFile;
        } else {
            $backupFile = $this->argument('file')
                ? $this->resolveBackupFile($this->argument('file'), $backupPath)
                : $this->selectBackupFile($backupPath, 'sqlite');
        }

        if ($backupFile === null) {
            return self::FAILURE;
        }

        if (! $this->isValidSqliteBackup($backupFile)) {
            $this->removeTemporaryFile($temporaryFile);

            return self::FAILURE;
        }

        $this->warn(
            'This will overwrite the current database "'.$database
            .'" with the contents of: '.basename($backupFile)
        );

        if (! $this->option('force') && ! $this->confirm('Continue with restore?', false)) {
            $this->info('Restore cancelled.');
            $this->removeTemporaryFile($temporaryFile);

            return self::SUCCESS;
        }

        $safetyFile = $this->createSafetySnapshot($database, $backupPath);

        if ($safetyFile === null) {
            $this->removeTemporaryFile($temporaryFile);

            return self::SUCCESS;
        }

        $this->info('Restoring database from: '.basename($backupFile));

        // Close this process's connection before replacing the file, and
        // remove stale journal files belonging to the old database.
        DB::purge();

        foreach (['-wal', '-shm', '-journal'] as $suffix) {
            $sidecar = $database.$suffix;

            if (is_file($sidecar)) {
                @unlink($sidecar);
            }
        }

        if (! @copy($backupFile, $database)) {
            $this->error('Could not write the backup to: '.$database);
            $this->removeTemporaryFile($temporaryFile);

            return self::FAILURE;
        }

        @chmod($database, 0664);

        $integrity = $this->sqliteIntegrityCheck($database);

        $this->removeTemporaryFile($temporaryFile);

        if ($integrity !== 'ok') {
            $this->error('Database restored, but the integrity check reported: '.$integrity);

            if ($safetyFile !== '') {
                $this->line('A snapshot of the previous database is available at: '.$safetyFile);
            }

            return self::FAILURE;
        }

        $this->info('Database restored successfully from: '.basename($backupFile));

        if ($safetyFile !== '') {
            $this->line('Previous database snapshot: '.$safetyFile);
        }

        return self::SUCCESS;
    }

    private function restorePostgres(): int
    {
        $connection = DB::connection()->getConfig();

        $host = $connection['host'] ?? '127.0.0.1';
        $port = $connection['port'] ?? 5432;
        $database = $connection['database'] ?? 'laravel';
        $username = $connection['username'] ?? 'postgres';
        $password = $connection['password'] ?? '';

        $psqlPath = Config::get('db-backup.psql_path', 'psql');
        $backupPath = Config::get('db-backup.backup_path', storage_path('app/backups'));

        if (! is_dir($backupPath)) {
            $this->error("Backup directory not found: {$backupPath}");

            return self::FAILURE;
        }

        $temporaryFile = null;

        if ($this->option('cloud')) {
            $temporaryFile = $this->downloadLatestOffsite('pgsql', $backupPath);

            if ($temporaryFile === null) {
                return self::FAILURE;
            }

            $backupFile = $temporaryFile;
        } else {
            $backupFile = $this->argument('file')
                ? $this->resolveBackupFile($this->argument('file'), $backupPath)
                : $this->selectBackupFile($backupPath, 'sql');
        }

        if ($backupFile === null) {
            return self::FAILURE;
        }

        $this->warn(
            'This will overwrite the current database "'.$database
            .'" with the contents of: '.basename($backupFile)
        );

        if (! $this->option('force') && ! $this->confirm('Continue with restore?', false)) {
            $this->info('Restore cancelled.');
            $this->removeTemporaryFile($temporaryFile);

            return self::SUCCESS;
        }

        $this->info('Restoring database from: '.basename($backupFile));

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

        $this->removeTemporaryFile($temporaryFile);

        if (! $process->isSuccessful()) {
            $errorOutput = trim($process->getErrorOutput());
            $this->error('Database restore failed.');
            $this->line('Error output: '.($errorOutput ?: '(no error output)'));

            return self::FAILURE;
        }

        $this->info('Database restored successfully from: '.basename($backupFile));

        return self::SUCCESS;
    }

    private function unsupportedDriver(string $driver): int
    {
        $this->error("Database restores are not supported for driver: {$driver}");

        return self::FAILURE;
    }

    /**
     * @return string|null The snapshot path ('' when skipped), or null when the user cancels.
     */
    private function createSafetySnapshot(string $database, string $backupPath): ?string
    {
        if (! is_file($database) || filesize($database) === 0) {
            return '';
        }

        $safetyFile = $backupPath.DIRECTORY_SEPARATOR
            .pathinfo($database, PATHINFO_FILENAME)
            .'_pre-restore_'.now()->format('Y-m-d_His_u').'.sqlite';

        try {
            DB::statement('VACUUM INTO ?', [$safetyFile]);
            $this->info('Current database snapshot saved: '.basename($safetyFile));

            return $safetyFile;
        } catch (\Throwable $e) {
            $this->warn('Could not create a safety snapshot: '.$e->getMessage());

            if (! $this->option('force') && ! $this->confirm('Continue without a safety snapshot?', false)) {
                $this->info('Restore cancelled.');

                return null;
            }

            return '';
        }
    }

    private function downloadLatestOffsite(string $extension, string $backupPath): ?string
    {
        $diskName = config('db-backup.offsite_disk');

        if (! is_string($diskName) || $diskName === '') {
            $this->error('No offsite backup disk configured (set DB_BACKUP_DISK).');

            return null;
        }

        $prefix = trim((string) config('db-backup.offsite_path', 'database-backups'), '/');
        $latest = (string) config('db-backup.offsite_latest');

        if ($latest === '') {
            $latest = $extension === 'pgsql' ? 'latest.sql' : 'latest.sqlite';
        }

        $remote = ($prefix !== '' ? $prefix.'/' : '').$latest;

        try {
            $disk = Storage::disk($diskName);

            if (! $disk->exists($remote)) {
                $this->error("Offsite backup not found: {$diskName}:{$remote}");

                return null;
            }

            $targetExtension = $extension === 'pgsql' ? 'sql' : 'sqlite';
            $target = $backupPath.DIRECTORY_SEPARATOR
                .'cloud_restore_'.now()->format('Y-m-d_His_u').'.'.$targetExtension;
            $source = $disk->readStream($remote);

            if (! is_resource($source)) {
                $this->error("Could not read offsite backup: {$diskName}:{$remote}");

                return null;
            }

            $destination = @fopen($target, 'wb');

            if ($destination === false) {
                fclose($source);
                $this->error('Could not create a temporary file in: '.$backupPath);

                return null;
            }

            $bytes = stream_copy_to_stream($source, $destination);
            fclose($source);
            fclose($destination);

            if ($bytes === false || $bytes === 0) {
                @unlink($target);
                $this->error("Offsite backup is empty: {$diskName}:{$remote}");

                return null;
            }

            $this->info("Downloaded offsite backup: {$diskName}:{$remote}");

            return $target;
        } catch (\Throwable $e) {
            $this->error('Offsite restore failed: '.$e->getMessage());

            return null;
        }
    }

    private function removeTemporaryFile(?string $path): void
    {
        if (is_string($path) && $path !== '' && is_file($path)) {
            @unlink($path);
        }
    }

    private function isValidSqliteBackup(string $file): bool
    {
        $header = @file_get_contents($file, false, null, 0, 16);

        if ($header !== "SQLite format 3\0") {
            $this->error('Not a valid SQLite database file: '.basename($file));

            return false;
        }

        $integrity = $this->sqliteIntegrityCheck($file);

        if ($integrity !== 'ok') {
            $this->error('Backup integrity check failed: '.$integrity);

            return false;
        }

        return true;
    }

    private function sqliteIntegrityCheck(string $file): string
    {
        try {
            $pdo = new \PDO('sqlite:'.$file, null, null, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);
            $result = $pdo->query('PRAGMA integrity_check')->fetchColumn();
            $pdo = null;

            return strtolower((string) $result) === 'ok' ? 'ok' : (string) $result;
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    private function dropAllTables(array $connection): void
    {
        $connectionName = $connection['name'] ?? config('database.default');
        $schema = $connection['search_path'] ?? 'public';

        try {
            $tables = DB::connection($connectionName)
                ->select('SELECT tablename FROM pg_tables WHERE schemaname = ?', [$schema]);
        } catch (\Throwable $e) {
            $this->error('Could not list tables: '.$e->getMessage());

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

    private function selectBackupFile(string $backupPath, string $extension): ?string
    {
        $files = glob($backupPath.DIRECTORY_SEPARATOR.'*.'.$extension) ?: [];

        if (empty($files)) {
            $this->error('No backup files found in: '.$backupPath);

            return null;
        }

        usort($files, function ($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });

        $choices = [];
        foreach ($files as $file) {
            $size = $this->formatBytes(filesize($file));
            $modified = date('Y-m-d H:i:s', filemtime($file));
            $choices[] = basename($file).'  ('.$size.', '.$modified.')';
        }

        $selected = $this->choice('Select a backup to restore:', $choices);

        $selectedBasename = explode('  (', $selected)[0];

        return $backupPath.DIRECTORY_SEPARATOR.$selectedBasename;
    }

    private function resolveBackupFile(string $argument, string $backupPath): ?string
    {
        if (file_exists($argument)) {
            return $argument;
        }

        $candidate = $backupPath.DIRECTORY_SEPARATOR.$argument;

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
                return round($bytes, 2).' '.$unit;
            }
            $bytes /= 1024;
        }

        return round($bytes, 2).' TB';
    }
}
