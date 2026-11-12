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
    WEBHOOK_OK=0
    WEBHOOK_URL="${APP_URL}/api/telegram/bot-webhook"
    if [[ "$APP_URL" =~ ^https:// ]]; then
        echo ">>> Registering webhook: ${WEBHOOK_URL}"
        php artisan telegram:setup --url="${APP_URL}" --commands-only 2>/dev/null || true
        WEBHOOK_OK=$(php artisan telegram:setup --info 2>/dev/null | grep -c '"ok":true' || true)

        if [ "$WEBHOOK_OK" -gt 0 ]; then
            # Also register bot commands
            php artisan telegram:setup --commands-only 2>/dev/null || true
            echo ">>> Webhook mode active - no poller needed."
        else
            echo ">>> Webhook registration failed - falling back to polling mode."
            # Fall through to polling below
        fi
    fi

    # Polling mode (fallback or when APP_URL is not HTTPS)
    if [[ ! "$APP_URL" =~ ^https:// ]] || [ "$WEBHOOK_OK" -eq 0 ]; then
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

echo ">>> Launching Apache on port $PORT..."
exec apache2-foreground
