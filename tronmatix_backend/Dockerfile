# ==============================
# 1. Node Stage (Build Frontend)
# ==============================
FROM node:20-alpine AS node_builder

WORKDIR /app

COPY package*.json ./
RUN npm ci --frozen-lockfile

COPY . .
RUN npm run build


# ==============================
# 2. Composer Stage (PHP deps)
# ==============================
FROM composer:2.7 AS composer_builder

WORKDIR /app

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
    fi

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader \
    --no-progress \
    --ignore-platform-reqs

# Regenerate autoloader without scripts (clean final state)
RUN composer dump-autoload --optimize --no-scripts


# ==============================
# 3. Final Runtime (Apache + PHP)
# ==============================
FROM php:8.4-apache

# Install system dependencies + PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev libxml2-dev \
    unzip zip git curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd intl mbstring exif pcntl bcmath zip pdo_mysql soap sockets opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite headers

WORKDIR /var/www/html

# Copy full app source
COPY . .

# Copy vendor (with compiled autoloader) from composer stage
COPY --from=composer_builder /app/vendor ./vendor

# Copy built frontend assets from node stage
COPY --from=node_builder /app/public/build ./public/build

# Copy Apache + entrypoint from .docker/ folder
COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# PHP opcache production config
RUN printf "opcache.enable=1\nopcache.memory_consumption=128\nopcache.validate_timestamps=0\n" \
    > /usr/local/etc/php/conf.d/opcache.ini

# Create .env from example if not present — real secrets injected at runtime via Render env vars
RUN if [ ! -f .env ] && [ -f .env.example ]; then cp .env.example .env; fi

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000

ENTRYPOINT ["docker-entrypoint.sh"]
