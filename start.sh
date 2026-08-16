#!/bin/sh
set -e

mkdir -p /var/www/storage/logs \
         /var/www/storage/framework/cache/data \
         /var/www/storage/framework/sessions \
         /var/www/storage/framework/views \
         /var/www/bootstrap/cache \
         /var/www/database

chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/database 2>/dev/null || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache /var/www/database 2>/dev/null || true

php artisan storage:link 2>/dev/null || true

if [ -f database/database.sqlite ] && command -v sqlite3 >/dev/null 2>&1; then
    if ! sqlite3 database/database.sqlite "PRAGMA quick_check;" >/dev/null 2>&1; then
        echo "Database is corrupted, recreating database.sqlite..."
        rm -f database/database.sqlite database/database.sqlite-wal database/database.sqlite-shm
    fi
fi

touch database/database.sqlite
if command -v sqlite3 >/dev/null 2>&1; then
    sqlite3 database/database.sqlite 'PRAGMA journal_mode=WAL; PRAGMA synchronous=NORMAL; PRAGMA busy_timeout=5000;' 2>/dev/null || true
fi

if [ $# -gt 0 ]; then
    exec sh -c "$*"
fi

php artisan migrate --force

if [ "$APP_ENV" = "preview" ] || [ "$SEED_DATABASE" = "true" ]; then
    php artisan db:seed --force
fi

php artisan optimize

exec supervisord -c /etc/supervisor/conf.d/oxy.conf
