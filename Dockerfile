FROM dunglas/frankenphp:1-php8.5 AS base

# ── Stage 1: Composer deps ─────────────────────────────────────────────────
FROM --platform=$BUILDPLATFORM composer:latest AS composer-deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN --mount=type=cache,id=composer,target=/root/.composer/cache \
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --no-scripts

# ── Stage 2: Node deps + Vite asset build ──────────────────────────────────
FROM --platform=$BUILDPLATFORM dunglas/frankenphp:1-php8.5 AS node-build
WORKDIR /app

COPY --from=node:24-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:24-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && npm install -g pnpm@11.18.0

COPY package.json pnpm-lock.yaml pnpm-workspace.yaml ./
RUN --mount=type=cache,id=pnpm,target=/root/.local/share/pnpm/store \
    pnpm install --frozen-lockfile

COPY --from=composer-deps /app/vendor vendor/
COPY . .

RUN php artisan wayfinder:generate && pnpm run build

# ── Stage 3: Runtime ───────────────────────────────────────────────────────
FROM dunglas/frankenphp:1-php8.5
LABEL authors="lempac"

WORKDIR /var/www/

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apt-get update && apt-get install -y --no-install-recommends \
    sqlite3 \
    supervisor \
    curl \
    && install-php-extensions zip pcntl gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

ENV PHP_UPLOAD_MAX_FILESIZE=200M \
    PHP_POST_MAX_SIZE=200M

RUN { \
    echo 'upload_max_filesize = ${PHP_UPLOAD_MAX_FILESIZE}'; \
    echo 'post_max_size = ${PHP_POST_MAX_SIZE}'; \
} > /usr/local/etc/php/conf.d/uploads.ini

# Copy app code
COPY . .

# Overlay with pre-built artifacts (overrides anything copied above)
COPY --from=composer-deps /app/vendor vendor/
COPY --from=node-build /app/public/build public/build/
COPY --from=node-build /app/bootstrap/ssr bootstrap/ssr/
COPY --from=node-build /app/resources/js/routes resources/js/routes/

RUN php artisan storage:link && \
    php artisan vendor:publish --tag=laravel-assets --force

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/database && \
    chmod +x /var/www/start.sh

COPY supervisord.conf /etc/supervisor/conf.d/oxy.conf

HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl --fail localhost:8000/up || exit 1

ENTRYPOINT ["/var/www/start.sh"]
