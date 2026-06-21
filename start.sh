#!/bin/sh

php artisan storage:link
touch database/database.sqlite
php artisan migrate --force
php artisan optimize

exec supervisord -c /etc/supervisor/conf.d/oxy.conf
