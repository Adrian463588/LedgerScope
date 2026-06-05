# ─── Stage 1: Build dependencies ─────────────────────────────────────────────
FROM php:8.4-fpm-alpine AS build

WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    postgresql-dev \
    zip \
    unzip \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    opcache \
    pcntl \
    bcmath \
    zip \
    gd

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copy composer files and install dependencies
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --optimize-autoloader \
    --prefer-dist \
    --no-interaction

# Copy application source
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --no-dev

# ─── Stage 2: Production image ────────────────────────────────────────────────
FROM php:8.4-fpm-alpine AS production

WORKDIR /var/www/html

# Runtime system dependencies
RUN apk add --no-cache \
    libpng \
    libzip \
    libpq \
    supervisor \
    nginx \
    bash

# PHP extensions (runtime)
RUN docker-php-ext-install pdo pdo_pgsql opcache pcntl bcmath zip gd

# Copy build artifacts
COPY --from=build /var/www/html /var/www/html

# PHP config: opcache tuned for production
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Nginx config
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Supervisor config
COPY docker/supervisor/horizon.conf /etc/supervisor.d/horizon.ini
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
