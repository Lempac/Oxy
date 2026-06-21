FROM dunglas/frankenphp:1.12.4-php8.5.7-alpine AS base

# ── Stage 1: Composer deps ─────────────────────────────────────────────────
FROM composer:latest AS composer-deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --no-scripts

# ── Stage 2: Node deps + Vite asset build ──────────────────────────────────
FROM dunglas/frankenphp:1.12.4-php8.5.7-alpine AS node-build
WORKDIR /app
RUN apk add --no-cache sqlite nodejs npm && npm install -g pnpm
COPY package.json pnpm-lock.yaml pnpm-workspace.yaml ./
RUN pnpm install --frozen-lockfile
COPY . .
COPY --from=composer-deps /app/vendor vendor/
RUN pnpm run build

# ── Stage 3: Runtime ───────────────────────────────────────────────────────
FROM dunglas/frankenphp:1.12.4-php8.5.7-alpine
LABEL authors="lempac"

WORKDIR /var/www/

RUN apk add --no-cache sqlite libzip-dev supervisor && \
    docker-php-ext-install zip pcntl

RUN { \
    echo 'upload_max_filesize = 200M'; \
    echo 'post_max_size = 200M'; \
} > /usr/local/etc/php/conf.d/uploads.ini

# Copy app code
COPY . .

# Overlay with pre-built artifacts (overrides anything copied above)
COPY --from=composer-deps /app/vendor vendor/
COPY --from=node-build /app/public/build public/build/

RUN php artisan storage:link && \
    php artisan vendor:publish --tag=laravel-assets --force

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/database && \
    chmod +x /var/www/start.sh

COPY supervisord.conf /etc/supervisor/conf.d/oxy.conf

HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl --fail localhost:8000/up || exit 1

ENTRYPOINT ["/var/www/start.sh"]
