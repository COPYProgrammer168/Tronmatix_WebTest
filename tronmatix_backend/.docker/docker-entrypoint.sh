#!/bin/bash
set -e

PORT="${PORT:-10000}"
echo ">>> Starting on port $PORT"

sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf 2>/dev/null || true

cd /var/www/html

# Generate APP_KEY if missing
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=" ]; then
    echo ">>> Generating APP_KEY..."
    php artisan key:generate --force || echo "WARN: key:generate failed"
fi

# Prepare SQLite data + backup directories on the persistent disk.
DB_FILE=""
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_FILE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    DB_DIR="$(dirname "$DB_FILE")"
    BACKUP_DIR="${DB_BACKUP_PATH:-/var/www/html/storage/app/backups}"

    echo ">>> Ensuring SQLite paths: $DB_DIR and $BACKUP_DIR"
    mkdir -p "$DB_DIR" "$BACKUP_DIR"
    [ -f "$DB_FILE" ] || touch "$DB_FILE"

    chown -R www-data:www-data "$DB_DIR" "$BACKUP_DIR" 2>/dev/null || true
    chmod -R 775 "$DB_DIR" "$BACKUP_DIR" 2>/dev/null || true

    # A Render persistent disk is a separate mount at $DB_DIR. If it is not
    # mounted, everything written there is wiped on the next deploy.
    DISK_MOUNTED=0
    if command -v mountpoint >/dev/null 2>&1 && mountpoint -q "$DB_DIR" 2>/dev/null; then
        DISK_MOUNTED=1
    elif [ "$(stat -c %d "$DB_DIR" 2>/dev/null)" != "$(stat -c %d "$(dirname "$DB_DIR")" 2>/dev/null)" ]; then
        DISK_MOUNTED=1
    fi

    if [ "$DISK_MOUNTED" -eq 1 ]; then
        echo ">>> Persistent storage OK: $DB_DIR is a mounted disk."
    else
        echo ">>> ####################################################################"
        echo ">>> WARNING: $DB_DIR is NOT a persistent disk mount."
        echo ">>> SQLite data and local backups will be lost on every deploy/restart."
        echo ">>> Attach a Render disk at that path (Blueprint: render.yaml,"
        echo ">>> disk name tronmatix-sqlite-data) or set DB_BACKUP_DISK so data"
        echo ">>> is restored automatically from object storage at boot."
        echo ">>> ####################################################################"
    fi
fi

# Clear ALL caches FIRST before re-caching.
echo ">>> Clearing old caches..."
php artisan optimize:clear 2>/dev/null || true

echo ">>> Caching config..."
php artisan config:cache || echo "WARN: config:cache failed"

echo ">>> Caching routes..."
php artisan route:cache || echo "WARN: route:cache failed"

echo ">>> Caching views..."
php artisan view:cache || echo "WARN: view:cache failed"

# Restore from object storage when the SQLite file has no tables yet
# (fresh container, missing disk, or a recreated service). This must run
# BEFORE the pre-deploy backup, so an empty database never replaces the
# good offsite snapshot.
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ] && [ -n "$DB_FILE" ] && [ -n "${DB_BACKUP_DISK:-}" ]; then
    TABLE_COUNT=$(php -r '
        try {
            $pdo = new PDO("sqlite:".$argv[1]);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo (int) $pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type = \"table\"")->fetchColumn();
        } catch (Throwable $e) {
            echo 0;
        }
    ' "$DB_FILE" 2>/dev/null || echo 0)

    if [ "${TABLE_COUNT:-0}" -eq 0 ]; then
        echo ">>> Database is empty - attempting restore from offsite backup..."
        php artisan db:restore --cloud --force || echo "WARN: no offsite backup restored - continuing with a fresh database"
    else
        echo ">>> Database has ${TABLE_COUNT} table(s) - no offsite restore needed."
    fi
fi

# Pre-deploy SQLite backup (safety net before migrations run).
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ] && [ -n "$DB_FILE" ] && [ -s "$DB_FILE" ]; then
    echo ">>> Creating pre-deploy database backup..."
    php artisan db:backup || echo "WARN: database backup failed - continuing deploy"
fi

echo ">>> Running migrations..."
php artisan migrate --force || echo "WARN: migrate failed - check DB"

# Seed data (safe) — disabled by default. DatabaseSeeder is idempotent and
# only seeds an empty database, so uncomment on a first deploy if needed.
# echo ">>> Seeding Database (idempotent)..."
# php artisan db:seed --force || echo "WARN: DatabaseSeeder failed"
#
# echo ">>> Seeding provinces..."
# php artisan db:seed --class=ProvinceSeeder --force || echo "WARN: ProvinceSeeder failed"
#
# echo ">>> Seeding marquee messages..."
# php artisan db:seed --class=MarqueeSeeder --force || echo "WARN: MarqueeSeeder failed"

# Storage symlink (local disk only)
if [ "${FILESYSTEM_DISK}" != "s3" ]; then
    echo ">>> Storage link..."
    php artisan storage:link 2>/dev/null || echo "WARN: storage:link failed"
fi

# Set permissions
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Telegram — prefer webhook, fallback to polling
if [ -n "$TELEGRAM_USER_BOT_TOKEN" ]; then

    # Kill any lingering poll process from the previous deploy.
    echo ">>> Killing any existing telegram:poll processes..."
    pkill -SIGTERM -f "artisan telegram:poll" 2>/dev/null || true
    sleep 5

    # Try webhook mode first — production has a public URL, so Telegram can
    # POST updates directly. This avoids 409 conflicts with local dev polling.
    #
    # The webhook MUST point at this service (the Laravel backend), never the
    # static frontend: a frontend URL just rewrites to index.html and drops the
    # update. Render injects RENDER_EXTERNAL_URL automatically, so prefer it
    # over APP_URL (which may be misconfigured in the dashboard).
    WEBHOOK_OK=0
    WEBHOOK_BASE_URL="${TELEGRAM_WEBHOOK_BASE_URL:-${RENDER_EXTERNAL_URL:-$APP_URL}}"
    WEBHOOK_BASE_URL="${WEBHOOK_BASE_URL%/}"
    WEBHOOK_URL="${WEBHOOK_BASE_URL}/api/telegram/bot-webhook"

    if [[ "$WEBHOOK_BASE_URL" =~ ^https:// ]]; then
        echo ">>> Registering webhook: ${WEBHOOK_URL}"
        php artisan telegram:setup --url="${WEBHOOK_BASE_URL}" 2>/dev/null || true

        # NOTE: `telegram:setup --info` prints only the `result` object — there
        # is no "ok" flag in it. Match the registered URL instead.
        WEBHOOK_INFO=$(php artisan telegram:setup --info 2>/dev/null || true)
        if printf '%s' "$WEBHOOK_INFO" | grep -Fq "$WEBHOOK_URL"; then
            WEBHOOK_OK=1
            echo ">>> Webhook mode active - no poller needed."

            # Watchdog: a poller left over from an older deploy can clear the
            # webhook while handling 409s. Re-register it if it goes missing.
            WATCHDOG_SECONDS="${TELEGRAM_WEBHOOK_WATCHDOG_SECONDS:-60}"
            if [ "$WATCHDOG_SECONDS" -gt 0 ] 2>/dev/null; then
                (
                    while true; do
                        sleep "$WATCHDOG_SECONDS"
                        CURRENT_INFO=$(php artisan telegram:setup --info 2>/dev/null || true)
                        if ! printf '%s' "$CURRENT_INFO" | grep -Fq "$WEBHOOK_URL"; then
                            echo ">>> Webhook missing - re-registering ${WEBHOOK_URL}"
                            php artisan telegram:setup --url="${WEBHOOK_BASE_URL}" >/dev/null 2>&1 || true
                        fi
                    done
                ) &
                echo ">>> Webhook watchdog PID: $!"
            fi
        else
            echo ">>> Webhook registration failed - falling back to polling mode."
            echo ">>> Current webhook info: ${WEBHOOK_INFO}"
            # Fall through to polling below
        fi
    fi

    # Polling mode (fallback or when WEBHOOK_BASE_URL is not HTTPS)
    if [[ ! "$WEBHOOK_BASE_URL" =~ ^https:// ]] || [ "$WEBHOOK_OK" -eq 0 ]; then
        echo ">>> Starting Telegram poll worker in background..."
        (
            while true; do
                echo "[telegram-poll] $(date '+%Y-%m-%d %H:%M:%S') - starting..."
                php /var/www/html/artisan telegram:poll --timeout=25 --limit=10
                EXIT_CODE=$?

                if [ "$EXIT_CODE" -eq 0 ]; then
                    echo "[telegram-poll] $(date '+%Y-%m-%d %H:%M:%S') - clean exit, stopping loop."
                    break
                fi

                echo "[telegram-poll] $(date '+%Y-%m-%d %H:%M:%S') - crashed (exit $EXIT_CODE), restarting in 5s..."
                sleep 5
            done
        ) &
        echo ">>> Telegram poll PID: $!"
    fi

else
    echo ">>> TELEGRAM_USER_BOT_TOKEN not set - skipping telegram setup"
fi

# Periodic backup (uploads to object storage when DB_BACKUP_DISK is set).
# Keeps the offsite snapshot fresh even if the disk is missing or was reset.
if [ "${DB_BACKUP_INTERVAL_SECONDS:-0}" -gt 0 ] 2>/dev/null; then
    echo ">>> Starting periodic database backup every ${DB_BACKUP_INTERVAL_SECONDS}s"
    (
        while true; do
            sleep "$DB_BACKUP_INTERVAL_SECONDS"
            echo "[db-backup] $(date '+%Y-%m-%d %H:%M:%S') - creating scheduled backup..."
            php artisan db:backup || echo "[db-backup] WARN: scheduled backup failed"
        done
    ) &
    echo ">>> Periodic backup PID: $!"
fi

echo ">>> Launching Apache on port $PORT..."
exec apache2-foreground
