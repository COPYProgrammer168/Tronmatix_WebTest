Command	What happens
php artisan db:backup	Creates a dated .sql dump, prints path + size, prunes old ones
php artisan db:restore	Lists backups, asks you to pick one, asks for confirmation, then restores
php artisan migrate:fresh	Backup fires first → if it succeeds, migrate:fresh runs normally
php artisan migrate:fresh (backup fails)	Backup error is printed, migrate:fresh is cancelled, process exits with code 1
php artisan migrate	No backup triggered (not in risky_commands)