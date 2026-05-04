#!/bin/bash
set -e

PORT="${PORT:-10000}"
echo ">>> Starting on port $PORT"

# ── FIX: Only patch ports.conf — apache.conf already hardcodes 10000 ──────────
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf 2>/dev/null || true

cd /var/www/html

# ── Generate APP_KEY if missing ───────────────────────────────────────────────
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=" ]; then
    echo ">>> Generating APP_KEY..."
    php artisan key:generate --force || echo "⚠️ key:generate failed"
fi

# ── Clear caches FIRST before re-caching ──────────────────────────────────────
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
if [ -n "$TELEGRAM_USER_BOT_TOKEN" ]; then

    # FIX: Kill any lingering poll process from the previous deploy.
    # Without this, old instance keeps calling getUpdates → Telegram Error 409.
    echo ">>> Killing any existing telegram:poll processes..."
    pkill -SIGTERM -f "artisan telegram:poll" 2>/dev/null || true

    # Wait for Telegram's server to release the long-poll session.
    # Telegram holds the connection for up to `timeout` seconds after SIGTERM.
    # 5s is safe for --timeout=25 because SIGTERM breaks the HTTP read immediately.
    echo ">>> Waiting for Telegram session to release..."
    sleep 5

    echo ">>> Starting Telegram poll worker in background..."
    (
        while true; do
            echo "[telegram-poll] $(date '+%Y-%m-%d %H:%M:%S') — starting..."
            php /var/www/html/artisan telegram:poll --timeout=25 --limit=10
            EXIT_CODE=$?

            # Exit code 0 = clean SIGTERM shutdown — do NOT restart.
            # Any other code = crash — restart after delay.
            if [ "$EXIT_CODE" -eq 0 ]; then
                echo "[telegram-poll] $(date '+%Y-%m-%d %H:%M:%S') — clean exit, stopping loop."
                break
            fi

            echo "[telegram-poll] $(date '+%Y-%m-%d %H:%M:%S') — crashed (exit $EXIT_CODE), restarting in 5s..."
            sleep 5
        done
    ) &
    echo ">>> Telegram poll PID: $!"

else
    echo ">>> TELEGRAM_USER_BOT_TOKEN not set — skipping telegram:poll"
fi

echo ">>> Launching Apache on port $PORT..."
exec apache2-foreground