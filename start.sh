#!/bin/sh

php artisan storage:link

if [ -f database/database.sqlite ]; command -v sqlite3 >/dev/null 2>&1; then
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
    exec "$@"
fi

php artisan migrate --force
php artisan optimize

exec supervisord -c /etc/supervisor/conf.d/oxy.conf
