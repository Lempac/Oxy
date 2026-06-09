FROM dunglas/frankenphp:php8.4

ENV DEBIAN_FRONTEND=noninteractive
ENV NODE_VERSION=24

# Install basic dependencies and extensions required
RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    git \
    supervisor \
    sqlite3 \
    libsqlite3-dev \
    python3 \
    python3-pip \
    python3-venv \
    && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions
RUN install-php-extensions \
    pdo_mysql \
    pdo_sqlite \
    mbstring \
    xml \
    zip \
    bcmath \
    intl \
    opcache \
    pcntl

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_$NODE_VERSION.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy the application source code
COPY . /app/

# Install Node.js dependencies and build frontend
RUN npm ci && npm run build

# Install PHP dependencies (allowing scripts so package:discover runs)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Create directory for supervisor socket and logs
RUN mkdir -p /var/run/supervisor /var/log/supervisor

# Setup Supervisor configuration
RUN mkdir -p /etc/supervisor/conf.d && cat << 'SUP' > /etc/supervisor/supervisord.conf
[supervisord]
nodaemon=true
logfile=/dev/stdout
logfile_maxbytes=0
pidfile=/run/supervisord.pid

[program:frankenphp]
command=frankenphp run --config /etc/caddy/Caddyfile
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
user=www-data

[program:queue]
command=php /app/artisan queue:work --tries=3 --max-time=3600
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
user=www-data

[program:reverb]
command=php /app/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
user=www-data

[program:y-websocket]
command=npm run yjs
directory=/app
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
user=www-data
SUP

# Ensure permissions
RUN chown -R www-data:www-data /app /data /config /var/run/supervisor /var/log/supervisor

# Expose required ports
# 80/443: Web server
# 8080: Reverb
# 1234: Y-WebSocket (default)
EXPOSE 80 443 8080 1234

ENTRYPOINT ["/app/docker/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
