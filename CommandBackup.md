Command	What happens
php artisan db:backup	Creates a dated backup (SQLite snapshot with VACUUM INTO, or .sql dump on PostgreSQL), prints path + size, prunes old ones, then uploads the latest snapshot to object storage when DB_BACKUP_DISK is set
php artisan db:backup --no-offsite	Same, but never uploads to object storage
php artisan db:restore	Lists backups, asks you to pick one, asks for confirmation, then restores (SQLite restores are validated and a pre-restore snapshot is saved)
php artisan db:restore --force	Same, without the confirmation prompt
php artisan db:restore --cloud --force	Downloads database-backups/latest.sqlite from the configured disk (s3_backup/R2 private bucket) and restores it without prompting
php artisan db:pgsql-to-sqlite	Copies data from an existing PostgreSQL database into a fresh SQLite file (migrations first, then rows; source data replaces seeded rows)
php artisan migrate:fresh	Backup fires first → if it succeeds, migrate:fresh runs normally
php artisan migrate:fresh (backup fails)	Backup error is printed, migrate:fresh is cancelled, process exits with code 1
php artisan migrate	No backup triggered (not in risky_commands)

SQLite notes
- Backups are written to storage/app/backups by default, or DB_BACKUP_PATH if set.
- On Render the persistent disk is mounted at /var/www/html/storage/data, so set
  DB_DATABASE=/var/www/html/storage/data/database.sqlite
  DB_BACKUP_PATH=/var/www/html/storage/data/backups
- The Docker entrypoint also runs php artisan db:backup automatically on every
  deploy, before migrations, when the SQLite file already has data.

Offsite backups on Render (R2)
- Create a PRIVATE R2 bucket (e.g. tronmatix-backups) and set DB_BACKUP_DISK=s3_backup,
  DB_BACKUP_BUCKET=tronmatix-backups, DB_BACKUP_S3_PATH=database-backups and
  DB_BACKUP_INTERVAL_SECONDS=900 on the service. Do not use the public s3 disk
  (the one behind the r2.dev image URL): it would expose the database publicly.
- Every backup (deploy-time and periodic) overwrites database-backups/latest.sqlite
  in the bucket. The entrypoint runs db:restore --cloud --force at boot when the
  SQLite file has no tables, so a missing/reset Render disk no longer loses data.
- The entrypoint logs a loud warning when the database directory is not a mounted
  disk. Render only auto-detects render.yaml at the repository root, so use the
  root render.yaml (or attach the disk manually at /var/www/html/storage/data).
- Render itself also snapshots attached disks daily (Disks page).

Copying PostgreSQL data into SQLite
- Pass the source as a URL: --source-url="postgres://user:pass@host:5432/db?sslmode=require"
  or override individually: --pg-host --pg-port --pg-database --pg-username --pg-password --pg-sslmode.
- Example:
  php artisan db:pgsql-to-sqlite --source-url="postgres://user:pass@host:5432/db?sslmode=require" --target=storage/app/backups/tronmatix.sqlite
- The command refuses to overwrite the live SQLite database; use --force only to replace an existing export file.
- It runs the migrations on the target, copies shared tables (converting booleans, JSON and timestamps),
  replaces migration-seeded rows with source rows, then runs SQLite integrity and foreign-key checks.
- Afterwards set DB_CONNECTION=sqlite and DB_DATABASE to the generated file (or swap it into place while the app is stopped).
