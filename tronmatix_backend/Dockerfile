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

<<<<<<< HEAD
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

=======
COPY package*.json ./
RUN npm ci --frozen-lockfile

COPY . .
>>>>>>> 2782b9c52fa113d1f3df06c6166e5d12792b181b
RUN npm run build
# Output: /app/dist


# ==============================
# 2. Composer Stage — PHP deps
# ==============================
FROM composer:2.7 AS composer_builder

WORKDIR /app

<<<<<<< HEAD
COPY . .

# Create a minimal .env so post-autoload-dump (php artisan package:discover)
# can boot Laravel without crashing — real secrets come from Render env vars
RUN if [ ! -f .env ]; then \
        cp .env.example .env 2>/dev/null || \
        printf "APP_NAME=Laravel\nAPP_ENV=production\nAPP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=\nAPP_DEBUG=false\nAPP_URL=http://localhost\nDB_CONNECTION=mysql\n" > .env; \
=======
# Copy entire app first — composer scripts need app files to exist
COPY . .

# FIX: The composer.json "post-autoload-dump" script runs:
#   php artisan package:discover --ansi
# This requires a valid APP_KEY and .env file to exist, which don't exist
# during a Docker build → exit code 2.
#
# Solution: set COMPOSER_ALLOW_SUPERUSER + provide a minimal .env with a
# placeholder APP_KEY so artisan can boot far enough to run package:discover.
# We also pass --ignore-platform-reqs in case the builder PHP version differs
# from the runtime php:8.4-apache image.
RUN if [ ! -f .env ]; then \
        cp .env.example .env 2>/dev/null || \
        echo "APP_NAME=Laravel\nAPP_ENV=production\nAPP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=\nAPP_DEBUG=false\nAPP_URL=http://localhost\nDB_CONNECTION=mysql" > .env; \
>>>>>>> 2782b9c52fa113d1f3df06c6166e5d12792b181b
    fi

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader \
    --no-progress \
    --ignore-platform-reqs

<<<<<<< HEAD
=======
# Regenerate autoloader without scripts (clean final state)
>>>>>>> 2782b9c52fa113d1f3df06c6166e5d12792b181b
RUN composer dump-autoload --optimize --no-scripts


# ==============================
# 3. Final Runtime — Apache + PHP
# ==============================
FROM php:8.4-apache

<<<<<<< HEAD
# Install system deps + PHP extensions
=======
# Install system dependencies + PHP extensions
>>>>>>> 2782b9c52fa113d1f3df06c6166e5d12792b181b
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

<<<<<<< HEAD
# Copy Laravel backend source (excludes tronmatix_frontend/ via .dockerignore)
COPY . .

# Copy PHP vendor from composer stage
COPY --from=composer_builder /app/vendor ./vendor

# FIX: React SPA builds to /app/dist — copy into public/spa
# so Apache serves it separately from Laravel's public/
# Laravel API routes stay at /api/*, React SPA is served at /*
COPY --from=node_builder /app/dist ./public/spa

# Apache config + entrypoint from .docker/ folder
=======
# Copy full app source
COPY . .

# Copy vendor (with compiled autoloader) from composer stage
COPY --from=composer_builder /app/vendor ./vendor

# Copy built frontend assets from node stage
COPY --from=node_builder /app/public/build ./public/build

# Copy Apache + entrypoint from .docker/ folder
>>>>>>> 2782b9c52fa113d1f3df06c6166e5d12792b181b
COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

<<<<<<< HEAD
# PHP opcache production settings
RUN printf "opcache.enable=1\nopcache.memory_consumption=128\nopcache.validate_timestamps=0\n" \
    > /usr/local/etc/php/conf.d/opcache.ini

# Create .env from example — Render injects real values via environment variables
RUN if [ ! -f .env ] && [ -f .env.example ]; then cp .env.example .env; fi

# Laravel permissions
=======
# PHP opcache production config
RUN printf "opcache.enable=1\nopcache.memory_consumption=128\nopcache.validate_timestamps=0\n" \
    > /usr/local/etc/php/conf.d/opcache.ini

# Create .env from example if not present — real secrets injected at runtime via Render env vars
RUN if [ ! -f .env ] && [ -f .env.example ]; then cp .env.example .env; fi

# Permissions
>>>>>>> 2782b9c52fa113d1f3df06c6166e5d12792b181b
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000

ENTRYPOINT ["docker-entrypoint.sh"]
