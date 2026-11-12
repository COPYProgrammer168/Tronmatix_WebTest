# ================================================================
# Backend-only Dockerfile (Laravel + Apache)
# Database: SQLite, stored on a Render persistent disk
# (mount /var/www/html/storage/data — see render.yaml)
# ================================================================


# ==============================
# 1. Composer Stage — PHP deps
# ==============================
FROM composer:2.7 AS composer_builder

WORKDIR /app

# Create required Laravel directories before COPY
RUN mkdir -p bootstrap/cache \
    && mkdir -p storage/framework/cache/data \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/logs \
    && chmod -R 777 bootstrap storage

COPY . .

# Restore permissions after COPY
RUN chmod -R 777 bootstrap/cache storage

# Create minimal .env — SQLite is the production database
RUN if [ ! -f .env ]; then \
        cp .env.example .env 2>/dev/null || \
        printf "APP_NAME=Laravel\nAPP_ENV=production\nAPP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=\nAPP_DEBUG=false\nAPP_URL=http://localhost\nDB_CONNECTION=sqlite\n" > .env; \
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
# 2. Final Runtime — Apache + PHP
# ==============================
FROM php:8.4-apache

# pdo_sqlite + sqlite3 ship with the official PHP image. No PostgreSQL
# client tools are needed for the SQLite deployment.
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev libicu-dev libonig-dev libxml2-dev \
    unzip zip git curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd intl mbstring exif pcntl bcmath zip \
        pdo_mysql \
        soap sockets opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite headers

WORKDIR /var/www/html

# Create directories before COPY
RUN mkdir -p bootstrap/cache \
    && mkdir -p storage/framework/cache/data \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/logs \
    && mkdir -p storage/data/backups \
    && mkdir -p database

COPY . .
COPY --from=composer_builder /app/vendor ./vendor

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
