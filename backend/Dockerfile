# ─── Stage 1: Base image ──────────────────────────────────────────────────────
FROM php:8.4-fpm-alpine AS base

WORKDIR /var/www/html

# Runtime system dependencies and security updates
RUN apk upgrade --no-cache && \
    apk add --no-cache \
    libpng \
    libzip \
    libpq \
    supervisor \
    nginx \
    bash

# Download and use php-extension-installer for robust extension compilation
# This automatically installs -dev packages for compiling and removes them afterwards
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
    pdo \
    pdo_pgsql \
    opcache \
    pcntl \
    bcmath \
    zip \
    gd

# ─── Stage 2: Build dependencies ─────────────────────────────────────────────
FROM base AS build

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

# ─── Stage 3: Production image ────────────────────────────────────────────────
FROM base AS production

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
RUN mkdir -p /var/log/supervisor \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
