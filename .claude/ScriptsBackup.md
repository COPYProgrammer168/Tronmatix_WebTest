Build an automatic PostgreSQL backup system for this Laravel project (Tronmatix). Goal: before any destructive database command runs, automatically create a pg_dump backup first, so an accidental migrate:fresh or db:wipe is always recoverable.

1. Create config/db-backup.php with:
   - 'risky_commands' => ['migrate:fresh', 'migrate:refresh', 'db:wipe', 'migrate:reset']
   - 'keep_last' => 10 (number of backups to retain before pruning oldest)
   - 'backup_path' => storage_path('app/backups')
   - 'pg_dump_path' => env('PG_DUMP_PATH', 'pg_dump') — allow overriding the binary path via .env, since on Windows pg_dump.exe may not be in PATH
   - 'psql_path' => env('PSQL_PATH', 'psql') — same reasoning, used for restore

2. Create app/Console/Commands/BackupDatabase.php as `php artisan db:backup`:
   - Read connection details from config('database.connections.pgsql') (host, port, database, username, password).
   - Build the pg_dump command using Symfony Process (already available via Laravel), passing PGPASSWORD as an environment variable to the process (not as a CLI arg, to avoid it showing in process lists).
   - Output format: plain SQL (--format=plain), so the dump is portable and human-readable, not tied to a specific pg_dump/pg_restore version.
   - Filename: {database}_{Y-m-d_His}.sql inside config('db-backup.backup_path').
   - After a successful dump, prune old backups: list files in the backup directory sorted by modified time, delete any beyond config('db-backup.keep_last').
   - On success, print the backup file path and size. On failure, print the process error output clearly and return a non-zero exit code (do not swallow the error).
   - Make sure storage/app/backups/ is created automatically if it doesn't exist (use Storage::makeDirectory or mkdir with proper permissions check).

3. Create app/Console/Commands/RestoreDatabase.php as `php artisan db:restore {file?}`:
   - If no {file} argument given, list all backups in the backup directory (newest first, with filename + size + date) and prompt the user to pick one interactively (use $this->choice()).
   - Before restoring, show a clear confirmation prompt ("This will overwrite the current database with {filename}. Continue?") and abort if not confirmed — do not skip this confirmation even if a filename was passed as an argument.
   - Restore using psql (since the dump is plain SQL format): pipe the file into psql using the same connection details as the backup command, again passing PGPASSWORD via process env, not CLI args.
   - Print clear success/failure output.

4. Register an event listener for Illuminate\Console\Events\CommandStarting in AppServiceProvider::boot() (or a dedicated listener class if this project already has a pattern for listeners — check app/Listeners first and match the existing convention if one exists):
   - On every command start, check if the command name is in config('db-backup.risky_commands').
   - If it matches, run the db:backup command programmatically (Artisan::call('db:backup')) BEFORE allowing the original command to proceed.
   - If the backup command returns a non-zero exit code (failure), block the risky command entirely: print a clear error explaining the backup failed so the destructive command was cancelled for safety, and prevent it from executing (throwing an exception in the listener or using the event's ability to halt is fine — verify Illuminate\Console\Events\CommandStarting supports this in this Laravel version, and if it doesn't allow blocking directly, fall back to checking the risky-command list at the very start of the AppServiceProvider boot cycle before the console kernel dispatches, and exit(1) with a clear message instead).
   - Do NOT trigger a backup for normal `migrate` (non-destructive, additive) — only the commands explicitly listed in risky_commands.

5. Add storage/app/backups/ to .gitignore if it isn't already covered by an existing storage/app/* ignore rule — check the current .gitignore first rather than assuming.

6. Test the flow and show me the output of:
   - php artisan db:backup (manual backup)
   - php artisan migrate:fresh (should trigger an automatic backup first, then proceed — or block if backup fails)
   - php artisan db:restore (should list available backups)

Show me the code for the config file, both commands, and the event listener before running any test that would actually touch the database, so I can confirm the risky-command list and confirmation-prompt behavior are correct first.
