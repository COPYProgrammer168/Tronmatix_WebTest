# ================================================================
# REPO STRUCTURE EXPECTED:
#   /                              ← Laravel backend + this Dockerfile
#   /tronmatix_frontend/           ← React SPA
#   /.docker/apache.conf
#   /.docker/docker-entrypoint.sh
#
# If your frontend folder has a different name, change the
# ARG FRONTEND_DIR value below to match exactly.
# ================================================================

ARG FRONTEND_DIR=tronmatix_frontend


# ==============================
# 1. Node Stage — Build React SPA
# ==============================
# Use node:20 (NOT alpine) — Vite 8 uses rolldown which requires glibc.
# Alpine uses musl libc and will fail with native binding errors.
FROM node:20 AS node_builder

ARG FRONTEND_DIR

WORKDIR /frontend

# FIX: Copy the entire repo into Docker first, then navigate into the
# frontend subfolder. This avoids "not found" errors if Docker's build
# context doesn't resolve subfolder COPY paths correctly on some hosts.
COPY . /repo

# Verify the frontend folder exists — print clear error if not
RUN if [ ! -d "/repo/${FRONTEND_DIR}" ]; then \
        echo ""; \
        echo "========================================"; \
        echo "ERROR: Frontend folder not found!"; \
        echo "Expected: /repo/${FRONTEND_DIR}"; \
        echo ""; \
        echo "Contents of /repo:"; \
        ls -la /repo; \
        echo "========================================"; \
        exit 1; \
    fi

# Copy frontend files into working directory
RUN cp -r /repo/${FRONTEND_DIR}/. .

# Install dependencies
RUN npm ci --legacy-peer-deps

# Inject production API URL at build time
ARG VITE_API_URL
ENV VITE_API_URL=${VITE_API_URL}

RUN npm run build
# Output: /frontend/dist


# ==============================
# 2. Composer Stage — PHP deps
# ==============================
FROM composer:2.7 AS composer_builder

WORKDIR /app

COPY . .

# Create minimal .env so php artisan can boot during post-autoload-dump
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

COPY . .
COPY --from=composer_builder /app/vendor ./vendor
COPY --from=node_builder /frontend/dist ./public/spa

COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

RUN printf "opcache.enable=1\nopcache.memory_consumption=128\nopcache.validate_timestamps=0\n" \
    > /usr/local/etc/php/conf.d/opcache.ini

RUN if [ ! -f .env ] && [ -f .env.example ]; then cp .env.example .env; fi

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000

ENTRYPOINT ["docker-entrypoint.sh"]
