# Use official PHP + Apache image (or PHP-FPM + Nginx if you prefer)
FROM php:8.2-apache

# Install system dependencies + PHP extensions Laravel needs
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql exif pcntl bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . .

# Install PHP dependencies (no dev packages)
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Give permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Enable Apache mod_rewrite (important for Laravel routing)
RUN a2enmod rewrite

# Copy custom Apache config (optional but recommended)
COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
