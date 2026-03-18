#!/bin/bash
set -e

PORT="${PORT:-10000}"
echo ">>> Starting on port $PORT"

# Patch Apache port
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

cd /var/www/html

# Generate APP_KEY if missing or placeholder
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=" ]; then
    echo ">>> Generating APP_KEY..."
    php artisan key:generate --force || echo "⚠️ key:generate failed — continuing"
fi

# Cache config — if this fails, log but continue (don't crash Apache)
echo ">>> Caching config..."
php artisan config:cache || echo "⚠️ config:cache failed — continuing without cache"

echo ">>> Caching routes..."
php artisan route:cache || echo "⚠️ route:cache failed — continuing without cache"

echo ">>> Caching views..."
php artisan view:cache || echo "⚠️ view:cache failed — continuing without cache"

# Run migrations — if fails, log but DON'T crash (let Apache start)
echo ">>> Running migrations..."
php artisan migrate --force || echo "⚠️ migrate failed — check DB connection"

# Create storage symlink
echo ">>> Storage link..."
php artisan storage:link || echo "⚠️ storage:link failed — continuing"

echo ">>> Launching Apache..."
exec apache2-foreground
