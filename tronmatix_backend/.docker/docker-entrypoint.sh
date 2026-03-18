#!/bin/bash
set -e

# ── FIX: Render assigns a dynamic PORT via environment variable ───────────────
# Apache by default listens on port 80. Render does NOT expose port 80 —
# it expects your app to listen on the PORT it provides (default: 10000).
# Without this, Render's health check fails and deploy is marked as failed.
# ─────────────────────────────────────────────────────────────────────────────

PORT="${PORT:-10000}"

echo ">>> Starting Apache on port $PORT"

# Update Apache's Listen port dynamically
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# ── Run Laravel bootstrap tasks ───────────────────────────────────────────────
cd /var/www/html

# Cache config/routes for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations automatically on deploy (safe with --force in production)
php artisan migrate --force

# ── Start Apache in foreground ────────────────────────────────────────────────
exec apache2-foreground
