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

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-scripts \
    --optimize-autoloader

COPY . .

RUN composer dump-autoload --optimize


# ==============================
# 3. Final Runtime (Apache + PHP)
# ==============================
FROM php:8.4-apache

# Install PHP extensions
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

# Copy app source
COPY . .

# Copy vendor from composer stage
COPY --from=composer_builder /app/vendor ./vendor

# Copy built frontend assets
COPY --from=node_builder /app/public/build ./public/build

# FIX: apache.conf and docker-entrypoint.sh live in .docker/ — paths updated
COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# PHP opcache config for production
RUN echo "opcache.enable=1\nopcache.memory_consumption=128\nopcache.validate_timestamps=0" \
    > /usr/local/etc/php/conf.d/opcache.ini

# Set correct permissions for Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000

ENTRYPOINT ["docker-entrypoint.sh"]
