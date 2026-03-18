# ==============================
# 1. Node Stage (Build Frontend)
# ==============================
FROM node:20-alpine AS node_builder

WORKDIR /app

# Copy only package files first (layer cache optimization)
COPY package*.json ./
RUN npm ci --frozen-lockfile

# Copy full project and build
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

# Copy rest of app AFTER install
COPY . .

# Run post-install scripts now that full app is present
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

# Enable Apache modules
RUN a2enmod rewrite headers

WORKDIR /var/www/html

# Copy app source
COPY . .

# Copy vendor from composer stage
COPY --from=composer_builder /app/vendor ./vendor

# Copy built frontend assets
COPY --from=node_builder /app/public/build ./public/build

# Use the external apache config file (cleaner than inline echo)
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# PHP config for production
RUN echo "opcache.enable=1\nopcache.memory_consumption=128\nopcache.validate_timestamps=0" \
    > /usr/local/etc/php/conf.d/opcache.ini

# Set correct permissions for Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# FIX: Render injects PORT env variable — Apache must listen on $PORT, not hardcoded 80
# This start script replaces the port at runtime before starting Apache
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 10000

ENTRYPOINT ["docker-entrypoint.sh"]
