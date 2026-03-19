# ================================================================
# Backend Dockerfile — Laravel + Apache
# Database : PostgreSQL (Render managed Postgres)
# Storage  : S3 / Cloudflare R2
# Port     : 10000  (Render default)
# ================================================================

# ══════════════════════════════════════════
# Stage 1 — Composer: install PHP deps
# ══════════════════════════════════════════
FROM composer:2.7 AS composer_builder

WORKDIR /app

# Pre-create Laravel writable dirs before COPY so permissions are clean
RUN mkdir -p bootstrap/cache \
             storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
    && chmod -R 777 bootstrap storage

COPY composer.json composer.lock ./

# FIX: install deps from lock file only (faster, reproducible, no full COPY needed yet)
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader \
    --no-progress \
    --ignore-platform-reqs \
    --no-scripts

# Now copy the full source and run dump-autoload with scripts
COPY . .
RUN chmod -R 777 bootstrap/cache storage \
    && composer dump-autoload --optimize --classmap-authoritative


# ══════════════════════════════════════════
# Stage 2 — Runtime: Apache + PHP 8.3
# ══════════════════════════════════════════
# FIX: Use php:8.3-apache (8.4 is bleeding-edge, some extensions unstable)
FROM php:8.3-apache

# ── System deps + PHP extensions ──────────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev libjpeg-dev libfreetype6-dev \
        libzip-dev libicu-dev libonig-dev libxml2-dev \
        libpq-dev \
        unzip zip git curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd intl mbstring exif pcntl bcmath zip \
        pdo pdo_mysql pdo_pgsql pgsql \
        soap sockets opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ── Apache: enable rewrite + headers ──────────────────────────────────────────
RUN a2enmod rewrite headers

# ── OPcache tuning ────────────────────────────────────────────────────────────
RUN printf "opcache.enable=1\n\
opcache.memory_consumption=256\n\
opcache.interned_strings_buffer=16\n\
opcache.max_accelerated_files=10000\n\
opcache.validate_timestamps=0\n\
opcache.save_comments=1\n\
opcache.fast_shutdown=1\n" \
    > /usr/local/etc/php/conf.d/opcache.ini

# ── PHP upload/memory limits ──────────────────────────────────────────────────
# FIX: Render free tier has limited RAM — keep memory_limit reasonable
RUN printf "upload_max_filesize=64M\n\
post_max_size=64M\n\
memory_limit=256M\n\
max_execution_time=60\n" \
    > /usr/local/etc/php/conf.d/limits.ini

WORKDIR /var/www/html

# ── Pre-create storage dirs ────────────────────────────────────────────────────
RUN mkdir -p bootstrap/cache \
             storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             storage/app/public/avatars

# ── Copy app source + vendor from builder ─────────────────────────────────────
COPY . .
COPY --from=composer_builder /app/vendor ./vendor

# ── Apache vhost config + entrypoint ──────────────────────────────────────────
COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# ── .env fallback (Render injects real env vars — this is just for build) ─────
RUN if [ ! -f .env ] && [ -f .env.example ]; then cp .env.example .env; fi

# ── Permissions ───────────────────────────────────────────────────────────────
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# ── Port (must match Render service port setting) ─────────────────────────────
EXPOSE 10000

ENTRYPOINT ["docker-entrypoint.sh"]
