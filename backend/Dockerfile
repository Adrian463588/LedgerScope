# syntax=docker/dockerfile:1.7

# ─── Stage 1: Base image ──────────────────────────────────────────────────────
FROM php:8.4-fpm AS base

WORKDIR /var/www/html

# Runtime system dependencies and security updates using apt-get (Debian)
RUN apt-get update && apt-get upgrade -y && \
    apt-get install -y --no-install-recommends \
    supervisor \
    nginx \
    bash \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Download and use php-extension-installer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# install-php-extensions on Debian downloads precompiled .so files directly
# (No heavy resource-intensive source compilation = no OOM crash on small VPS)
RUN install-php-extensions \
    pdo_pgsql \
    pcntl \
    bcmath \
    zip \
    gd \
    redis

# ─── Stage 2: Build dependencies ─────────────────────────────────────────────
FROM base AS build

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_CACHE_DIR=/tmp/composer-cache \
    COMPOSER_MAX_PARALLEL_HTTP=1

# Copy composer files and install dependencies
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/composer-cache composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --optimize-autoloader \
    --prefer-dist \
    --no-progress \
    --no-interaction

# Copy application source
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --no-dev

# ─── Stage 3: Development image ──────────────────────────────────────────────
FROM base AS development

# Static analysis and the full test suite need more than PHP's conservative
# CLI default when PHPStan fans out across the Laravel application graph.
RUN printf 'memory_limit=512M\n' > /usr/local/etc/php/conf.d/zz-ledgerscope-development.ini

# Development and test containers need the full Composer dependency graph.
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_CACHE_DIR=/tmp/composer-cache \
    COMPOSER_MAX_PARALLEL_HTTP=1
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/composer-cache composer install \
    --no-scripts \
    --prefer-dist \
    --no-progress \
    --no-interaction

COPY . .
RUN composer dump-autoload --optimize

RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    storage/app/private \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

# ─── Stage 4: Production image ────────────────────────────────────────────────
FROM base AS production

# Copy build artifacts
COPY --from=build /var/www/html /var/www/html

# PHP config: opcache tuned for production
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Nginx config
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
RUN rm -f /etc/nginx/sites-enabled/default

# Supervisor config
COPY docker/supervisor/horizon.conf /etc/supervisor.d/horizon.ini
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

# Permissions
RUN mkdir -p /var/log/supervisor \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/framework/cache/data \
    && mkdir -p /var/www/html/storage/app/private \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
