#!/bin/bash
set -e

# Wait for database if necessary (skipped here for simplicity)

# Discover packages and optimize application at runtime
php artisan package:discover --ansi
php artisan optimize

# Start supervisor
exec "$@"
