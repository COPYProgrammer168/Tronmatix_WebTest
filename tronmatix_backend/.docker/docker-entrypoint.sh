#!/bin/bash
set -e

PORT="${PORT:-10000}"
echo ">>> Starting on port $PORT"

# ── FIX: Only patch ports.conf — apache.conf already hardcodes 10000 ──────────
# Patching 000-default.conf breaks VirtualHost if port already correct
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf 2>/dev/null || true

cd /var/www/html

# ── Generate APP_KEY if missing ───────────────────────────────────────────────
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=" ]; then
    echo ">>> Generating APP_KEY..."
    php artisan key:generate --force || echo "⚠️ key:generate failed"
fi

# ── FIX: Clear caches FIRST before re-caching ─────────────────────────────────
# Stale cache from previous deploy can cause config/CORS issues
echo ">>> Clearing old caches..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear  2>/dev/null || true
php artisan view:clear   2>/dev/null || true

echo ">>> Caching config..."
php artisan config:cache || echo "⚠️ config:cache failed"

echo ">>> Caching routes..."
php artisan route:cache  || echo "⚠️ route:cache failed"

echo ">>> Caching views..."
php artisan view:cache   || echo "⚠️ view:cache failed"

# ── Run migrations ────────────────────────────────────────────────────────────
echo ">>> Running migrations..."
php artisan migrate --force || echo "⚠️ migrate failed — check DB"

# ── Storage symlink (local disk only) ─────────────────────────────────────────
if [ "${FILESYSTEM_DISK}" != "s3" ]; then
    echo ">>> Storage link..."
    php artisan storage:link 2>/dev/null || echo "⚠️ storage:link failed"
fi

# ── Set permissions ───────────────────────────────────────────────────────────
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# ── Telegram poll — background daemon ────────────────────────────────────────
# Runs telegram:poll in a restart loop so it stays alive
# alongside Apache inside the same container.
if [ -n "$TELEGRAM_USER_BOT_TOKEN" ]; then
    echo ">>> Starting Telegram poll worker in background..."
    (
        while true; do
            echo "[telegram-poll] $(date '+%Y-%m-%d %H:%M:%S') — starting..."
            php /var/www/html/artisan telegram:poll --timeout=25 --limit=10
            echo "[telegram-poll] $(date '+%Y-%m-%d %H:%M:%S') — exited, restarting in 3s..."
            sleep 3
        done
    ) &
    echo ">>> Telegram poll PID: $!"
else
    echo ">>> TELEGRAM_USER_BOT_TOKEN not set — skipping telegram:poll"
fi

echo ">>> Launching Apache on port $PORT..."
exec apache2-foreground
