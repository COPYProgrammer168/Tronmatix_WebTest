# ================================================================
# REPO STRUCTURE:
#   /                          ← Laravel backend (root package.json = Inertia, NOT used)
#   /tronmatix_frontend/       ← React SPA (this is what we build)
#   /Dockerfile                ← this file
#   /.docker/apache.conf
#   /.docker/docker-entrypoint.sh
# ================================================================


# ==============================
# 1. Node Stage — Build React SPA
# ==============================
# FIX: Use node:20 (not alpine) — Vite 8 + rolldown requires glibc.
# Alpine uses musl libc which causes native binding failures with
# @rollup/rollup-linux-x64-gnu and lightningcss-linux-x64-gnu.
FROM node:20 AS node_builder

WORKDIR /app

# FIX: Copy from tronmatix_frontend/ — NOT the repo root.
# The root package.json is Laravel/Inertia and has no vite.config.js for the SPA.
COPY tronmatix_frontend/package*.json ./

# Install with legacy-peer-deps to handle any React 18/19 peer dep conflicts
RUN npm ci --legacy-peer-deps

# Copy entire frontend source
COPY tronmatix_frontend/ .

# Inject production API URL into the build
ARG VITE_API_URL
ENV VITE_API_URL=${VITE_API_URL}

RUN npm run build
# Output: /app/dist


# ==============================
# 2. Composer Stage — PHP deps
# ==============================
FROM composer:2.7 AS composer_builder

WORKDIR /app

COPY . .

# Create a minimal .env so post-autoload-dump (php artisan package:discover)
# can boot Laravel without crashing — real secrets come from Render env vars
RUN if [ ! -f .env ]; then \
        cp .env.example .env 2>/dev/null || \
        printf "APP_NAME=Laravel\nAPP_ENV=production\nAPP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=\nAPP_DEBUG=false\nAPP_URL=http://localhost\nDB_CONNECTION=mysql\n" > .env; \
    fi

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader \
    --no-progress \
    --ignore-platform-reqs

RUN composer dump-autoload --optimize --no-scripts


# ==============================
# 3. Final Runtime — Apache + PHP
# ==============================
FROM php:8.4-apache

# Install system deps + PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev libicu-dev libonig-dev libxml2-dev \
    unzip zip git curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd intl mbstring exif pcntl bcmath zip pdo_mysql soap sockets opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite headers

WORKDIR /var/www/html

# Copy Laravel backend source (excludes tronmatix_frontend/ via .dockerignore)
COPY . .

# Copy PHP vendor from composer stage
COPY --from=composer_builder /app/vendor ./vendor

# FIX: React SPA builds to /app/dist — copy into public/spa
# so Apache serves it separately from Laravel's public/
# Laravel API routes stay at /api/*, React SPA is served at /*
COPY --from=node_builder /app/dist ./public/spa

# Apache config + entrypoint from .docker/ folder
COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# PHP opcache production settings
RUN printf "opcache.enable=1\nopcache.memory_consumption=128\nopcache.validate_timestamps=0\n" \
    > /usr/local/etc/php/conf.d/opcache.ini

# Create .env from example — Render injects real values via environment variables
RUN if [ ! -f .env ] && [ -f .env.example ]; then cp .env.example .env; fi

# Laravel permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000

ENTRYPOINT ["docker-entrypoint.sh"]
