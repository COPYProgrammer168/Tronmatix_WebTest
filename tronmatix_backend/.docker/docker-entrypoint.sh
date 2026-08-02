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

# ── Clear ALL caches FIRST before re-caching ─────────────────────────────────
echo ">>> Clearing old caches..."
php artisan optimize:clear 2>/dev/null || true

echo ">>> Caching config..."
php artisan config:cache || echo "⚠️ config:cache failed"

echo ">>> Caching routes..."
php artisan route:cache  || echo "⚠️ route:cache failed"

echo ">>> Caching views..."
php artisan view:cache   || echo "⚠️ view:cache failed"

# ── Run migrations ────────────────────────────────────────────────────────────
echo ">>> Running migrations..."
php artisan migrate --force || echo "⚠️ migrate failed — check DB"

# ── Seed delivery zones, provinces & marquees (idempotent — deletes + re-inserts) ─
echo ">>> Seeding Database..."
php artisan db:seed --force || echo "⚠️ DatabaseSeeder failed"

# echo ">>> Seeding provinces..."
# php artisan db:seed --class=ProvinceSeeder --force || echo "⚠️ ProvinceSeeder failed"

# echo ">>> Seeding marquee messages..."
# php artisan db:seed --class=MarqueeSeeder --force || echo "⚠️ MarqueeSeeder failed"

# ── Storage symlink (local disk only) ─────────────────────────────────────────
if [ "${FILESYSTEM_DISK}" != "s3" ]; then
    echo ">>> Storage link..."
    php artisan storage:link 2>/dev/null || echo "⚠️ storage:link failed"
fi

# ── Set permissions ───────────────────────────────────────────────────────────
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# ── Telegram — prefer webhook, fallback to polling ──────────────────────────
if [ -n "$TELEGRAM_USER_BOT_TOKEN" ]; then

    # FIX: Kill any lingering poll process from the previous deploy.
    echo ">>> Killing any existing telegram:poll processes..."
    pkill -SIGTERM -f "artisan telegram:poll" 2>/dev/null || true
    sleep 5

    # Try webhook mode first — production has a public URL, so Telegram can
    # POST updates directly. This avoids 409 conflicts with local dev polling.
    WEBHOOK_URL="${APP_URL}/api/telegram/bot-webhook"
    if [[ "$APP_URL" =~ ^https:// ]]; then
        echo ">>> Registering webhook: ${WEBHOOK_URL}"
        php artisan telegram:setup --url="${APP_URL}" --commands-only 2>/dev/null || true
        WEBHOOK_OK=$(php artisan telegram:setup --info 2>/dev/null | grep -c '"ok":true' || true)

        if [ "$WEBHOOK_OK" -gt 0 ]; then
            # Also register bot commands
            php artisan telegram:setup --commands-only 2>/dev/null || true
            echo ">>> ✅ Webhook mode active — no poller needed."
        else
            echo ">>> Webhook registration failed — falling back to polling mode."
            # Fall through to polling below
        fi
    fi

    # Polling mode (fallback or when APP_URL is not HTTPS)
    if [[ ! "$APP_URL" =~ ^https:// ]] || [ "$WEBHOOK_OK" -eq 0 ]; then
        echo ">>> Starting Telegram poll worker in background..."
        (
            while true; do
                echo "[telegram-poll] $(date '+%Y-%m-%d %H:%M:%S') — starting..."
                php /var/www/html/artisan telegram:poll --timeout=25 --limit=10
                EXIT_CODE=$?

                if [ "$EXIT_CODE" -eq 0 ]; then
                    echo "[telegram-poll] $(date '+%Y-%m-%d %H:%M:%S') — clean exit, stopping loop."
                    break
                fi

                echo "[telegram-poll] $(date '+%Y-%m-%d %H:%M:%S') — crashed (exit $EXIT_CODE), restarting in 5s..."
                sleep 5
            done
        ) &
        echo ">>> Telegram poll PID: $!"
    fi

else
    echo ">>> TELEGRAM_USER_BOT_TOKEN not set — skipping telegram setup"
fi

echo ">>> Launching Apache on port $PORT..."
exec apache2-foreground
