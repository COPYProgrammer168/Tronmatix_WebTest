<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PgsqlToSqlite extends Command
{
    protected $signature = 'db:pgsql-to-sqlite
                            {--source=pgsql : Source connection name (must use the pgsql driver)}
                            {--source-url= : PostgreSQL URL, e.g. postgres://user:pass@host:5432/db?sslmode=require}
                            {--pg-host= : Override the PostgreSQL host}
                            {--pg-port= : Override the PostgreSQL port}
                            {--pg-database= : Override the PostgreSQL database}
                            {--pg-username= : Override the PostgreSQL username}
                            {--pg-password= : Override the PostgreSQL password}
                            {--pg-sslmode= : Override the PostgreSQL sslmode}
                            {--schema=public : Source schema}
                            {--target= : Target SQLite file (default: storage/app/backups/tronmatix-from-pgsql-<timestamp>.sqlite)}
                            {--chunk=500 : Rows per insert batch}
                            {--force : Overwrite the target file if it exists}';

    protected $description = 'Copy PostgreSQL data into a new SQLite database';

    public function handle(): int
    {
        $targetFile = $this->targetFile();

        if ($targetFile === null) {
            return self::FAILURE;
        }

        $source = $this->sourceConnection();

        if ($source === null) {
            return self::FAILURE;
        }

        $schema = (string) $this->option('schema');

        try {
            $sourceVersion = DB::connection($source)->selectOne('select version()');
            $this->info('Source: '.($sourceVersion->version ?? $source));
        } catch (\Throwable $e) {
            $this->error('Could not connect to PostgreSQL: '.$e->getMessage());
            $this->line('Pass the connection with --source-url=... or the --pg-* options.');

            return self::FAILURE;
        }

        $chunk = max(1, (int) $this->option('chunk'));
        $target = $this->configureTarget($targetFile);

        if (! $this->prepareTargetFile($targetFile)) {
            return self::FAILURE;
        }

        $this->info('Target: '.$targetFile);

        try {
            $this->info('Creating SQLite schema (running migrations)...');
            $exitCode = $this->call('migrate', [
                '--database' => $target,
                '--force' => true,
            ]);

            if ($exitCode !== self::SUCCESS) {
                $this->error('Migrations failed on the target SQLite database.');

                return self::FAILURE;
            }

            return $this->copyData($source, $target, $schema, $chunk, $targetFile);
        } finally {
            DB::purge($target);
        }
    }

    private function copyData(string $source, string $target, string $schema, int $chunk, string $targetFile): int
    {
        $sourceTables = Schema::connection($source)->getTableListing($schema, false);
        $targetTables = Schema::connection($target)->getTableListing(null, false);

        // The target's migrations table was populated by the fresh migrate run.
        // Copying the source rows would make Laravel re-run old migrations.
        $skip = ['migrations', 'sqlite_sequence'];
        $sourceTables = array_values(array_diff($sourceTables, $skip));
        sort($sourceTables);

        if (empty($sourceTables)) {
            $this->error('No tables found in the source schema: '.$schema);

            return self::FAILURE;
        }

        $skipped = array_values(array_diff($sourceTables, $targetTables));
        if (! empty($skipped)) {
            $this->warn('Skipped source-only tables: '.implode(', ', $skipped));
        }

        $empty = array_values(array_diff($targetTables, $sourceTables, $skip));
        if (! empty($empty)) {
            $this->line('Target-only tables (left empty): '.implode(', ', $empty));
        }

        $targetConnection = DB::connection($target);
        $targetConnection->statement('PRAGMA foreign_keys = OFF');

        $totalRows = 0;
        $copiedTables = 0;

        try {
            foreach ($sourceTables as $table) {
                if (! in_array($table, $targetTables, true)) {
                    continue;
                }

                $sourceColumns = array_column(
                    Schema::connection($source)->getColumns($table),
                    'name'
                );

                $targetColumns = array_column(
                    Schema::connection($target)->getColumns($table),
                    'name'
                );

                $columns = array_values(array_intersect($sourceColumns, $targetColumns));

                if (empty($columns)) {
                    $this->warn("  {$table}: no shared columns, skipped.");

                    continue;
                }

                if (count($columns) !== count($sourceColumns)) {
                    $missing = array_values(array_diff($sourceColumns, $columns));
                    $this->warn("  {$table}: ignoring source columns not in SQLite: ".implode(', ', $missing));
                }

                $hasSourceRows = DB::connection($source)->table($table)->exists();

                $rows = $this->copyTable($source, $target, $table, $columns, $chunk, $hasSourceRows);

                $totalRows += $rows;
                $copiedTables++;

                $this->line("  {$table}: {$rows} rows");
            }
        } catch (\Throwable $e) {
            $this->error('Copy failed: '.$e->getMessage());
            $this->line('The partially written file is: '.$targetFile);

            return self::FAILURE;
        } finally {
            $targetConnection->statement('PRAGMA foreign_keys = ON');
        }

        $violations = $targetConnection->select('PRAGMA foreign_key_check');
        if (! empty($violations)) {
            $this->warn('Foreign key check reported '.count($violations).' violation(s); review the copied data.');
        }

        $integrity = $targetConnection->selectOne('PRAGMA integrity_check');
        $integrity = $integrity ? (array_values((array) $integrity)[0] ?? 'unknown') : 'unknown';

        if (strtolower((string) $integrity) !== 'ok') {
            $this->error('SQLite integrity check failed: '.$integrity);

            return self::FAILURE;
        }

        $targetConnection->statement('PRAGMA wal_checkpoint(TRUNCATE)');

        $size = is_file($targetFile) ? round(filesize($targetFile) / 1024, 1).' KB' : 'unknown size';

        $this->newLine();
        $this->info("Done. Copied {$totalRows} rows from {$copiedTables} tables.");
        $this->info("SQLite database: {$targetFile} ({$size})");
        $this->line('Point the app at it with:');
        $this->line('  DB_CONNECTION=sqlite');
        $this->line('  DB_DATABASE='.$targetFile);

        return self::SUCCESS;
    }

    private function copyTable(string $source, string $target, string $table, array $columns, int $chunk, bool $clearTarget): int
    {
        $rows = DB::connection($source)
            ->table($table)
            ->select($columns)
            ->cursor();

        $targetConnection = DB::connection($target);
        $batch = [];
        $count = 0;

        $targetConnection->transaction(function () use ($rows, $targetConnection, $table, $columns, $chunk, $clearTarget, &$batch, &$count) {
            if ($clearTarget) {
                // Fresh migrations may seed rows (e.g. admin_settings); the source wins.
                $targetConnection->table($table)->delete();
            }

            foreach ($rows as $row) {
                $data = [];

                foreach ($columns as $column) {
                    $value = $row->{$column} ?? null;

                    if (is_bool($value)) {
                        $value = $value ? 1 : 0;
                    } elseif (is_resource($value)) {
                        $value = stream_get_contents($value);
                    } elseif ($value instanceof \DateTimeInterface) {
                        $value = $value->format('Y-m-d H:i:s');
                    }

                    $data[$column] = $value;
                }

                $batch[] = $data;

                if (count($batch) >= $chunk) {
                    $targetConnection->table($table)->insert($batch);
                    $count += count($batch);
                    $batch = [];
                }
            }

            if (! empty($batch)) {
                $targetConnection->table($table)->insert($batch);
                $count += count($batch);
                $batch = [];
            }
        });

        return $count;
    }

    private function sourceConnection(): ?string
    {
        $name = (string) $this->option('source');
        $config = config("database.connections.{$name}");

        if (! is_array($config)) {
            $this->error("Unknown source connection: {$name}");

            return null;
        }

        if (($config['driver'] ?? null) !== 'pgsql') {
            $this->error("Source connection {$name} must use the pgsql driver.");

            return null;
        }

        $overrides = [
            'host' => $this->option('pg-host'),
            'port' => $this->option('pg-port'),
            'database' => $this->option('pg-database'),
            'username' => $this->option('pg-username'),
            'password' => $this->option('pg-password'),
            'sslmode' => $this->option('pg-sslmode'),
        ];

        foreach ($overrides as $key => $value) {
            if ($value !== null && $value !== false && $value !== '') {
                $config[$key] = $value;
            }
        }

        if ($url = $this->option('source-url')) {
            $config['url'] = $url;
        }

        config(["database.connections.{$name}" => $config]);
        DB::purge($name);

        return $name;
    }

    private function targetFile(): ?string
    {
        $target = $this->option('target');

        if (! is_string($target) || trim($target) === '') {
            $target = storage_path(
                'app/backups/tronmatix-from-pgsql-'.now()->format('Y-m-d_His').'.sqlite'
            );
        } elseif (! $this->isAbsolutePath($target)) {
            $target = base_path($target);
        }

        $target = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $target);
        $directory = dirname($target);

        if (DB::connection()->getDriverName() === 'sqlite') {
            $liveDatabase = DB::connection()->getConfig('database');

            if (is_string($liveDatabase) && $this->isSamePath($liveDatabase, $target)) {
                $this->error('Refusing to overwrite the live SQLite database: '.$target);
                $this->line('Choose another --target and swap the file manually afterwards.');

                return null;
            }
        }

        if (file_exists($target) && ! $this->option('force')) {
            $this->error('Target file already exists: '.$target);
            $this->line('Use --force to overwrite it.');

            return null;
        }

        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            $this->error('Could not create target directory: '.$directory);

            return null;
        }

        return $target;
    }

    private function prepareTargetFile(string $target): bool
    {
        if ($this->option('force') && is_file($target)) {
            @unlink($target);
        }

        foreach (['-wal', '-shm', '-journal'] as $suffix) {
            $sidecar = $target.$suffix;

            if (is_file($sidecar)) {
                @unlink($sidecar);
            }
        }

        if (! is_file($target) && ! touch($target)) {
            $this->error('Could not create the target file: '.$target);

            return false;
        }

        return true;
    }

    private function configureTarget(string $target): string
    {
        $name = 'sqlite_pgsql_export';

        config(["database.connections.{$name}" => [
            'driver' => 'sqlite',
            'url' => null,
            'database' => $target,
            'prefix' => '',
            'foreign_key_constraints' => true,
            'busy_timeout' => 5000,
            'journal_mode' => 'WAL',
            'synchronous' => 'NORMAL',
            'transaction_mode' => 'DEFERRED',
        ]]);

        DB::purge($name);

        return $name;
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/')
            || str_starts_with($path, '\\')
            || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1;
    }

    private function isSamePath(string $a, string $b): bool
    {
        $normalize = function (string $path): string {
            $real = realpath($path);
            $path = $real !== false ? $real : $path;

            return strtolower(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path));
        };

        return $normalize($a) === $normalize($b);
    }
}
