Command	What happens
php artisan db:backup	Creates a dated backup (SQLite snapshot with VACUUM INTO, or .sql dump on PostgreSQL), prints path + size, prunes old ones
php artisan db:restore	Lists backups, asks you to pick one, asks for confirmation, then restores (SQLite restores are validated and a pre-restore snapshot is saved)
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

Copying PostgreSQL data into SQLite
- Pass the source as a URL: --source-url="postgres://user:pass@host:5432/db?sslmode=require"
  or override individually: --pg-host --pg-port --pg-database --pg-username --pg-password --pg-sslmode.
- Example:
  php artisan db:pgsql-to-sqlite --source-url="postgres://user:pass@host:5432/db?sslmode=require" --target=storage/app/backups/tronmatix.sqlite
- The command refuses to overwrite the live SQLite database; use --force only to replace an existing export file.
- It runs the migrations on the target, copies shared tables (converting booleans, JSON and timestamps),
  replaces migration-seeded rows with source rows, then runs SQLite integrity and foreign-key checks.
- Afterwards set DB_CONNECTION=sqlite and DB_DATABASE to the generated file (or swap it into place while the app is stopped).
