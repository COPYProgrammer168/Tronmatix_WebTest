# ================================================================
# Backend-only Dockerfile (Laravel + Apache)
# Frontend is deployed separately as a Render Static Site.
# No Node/Vite build stage needed here.
# ================================================================


# ==============================
# 1. Composer Stage — PHP deps
# ==============================
FROM composer:2.7 AS composer_builder

WORKDIR /app

COPY . .

# Create minimal .env so post-autoload-dump (php artisan package:discover)
# can boot Laravel — real secrets are injected via Render environment variables
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
# 2. Final Runtime — Apache + PHP
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

COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# PHP opcache production config
RUN printf "opcache.enable=1\nopcache.memory_consumption=128\nopcache.validate_timestamps=0\n" \
    > /usr/local/etc/php/conf.d/opcache.ini

RUN if [ ! -f .env ] && [ -f .env.example ]; then cp .env.example .env; fi

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000

ENTRYPOINT ["docker-entrypoint.sh"]
