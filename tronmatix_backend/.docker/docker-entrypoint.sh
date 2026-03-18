#!/bin/bash
set -e

PORT="${PORT:-10000}"

echo ">>> Starting Apache on port $PORT"

# Patch Apache port at runtime (Render assigns dynamic PORT)
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

cd /var/www/html

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=" ]; then
    echo ">>> Generating APP_KEY..."
    php artisan key:generate --force
fi

# Cache config/routes for production performance
echo ">>> Caching config and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations — uses DB_* env vars injected by Render
echo ">>> Running migrations..."
php artisan migrate --force

echo ">>> Launching Apache..."
exec apache2-foreground
