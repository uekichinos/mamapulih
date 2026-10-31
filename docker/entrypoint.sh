#!/bin/sh
set -e

echo "Waiting for the database..."
until pg_isready -h "${DB_HOST:-pgsql}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-sail}" >/dev/null 2>&1; do
    sleep 1
done

echo "Running migrations..."
php artisan migrate --force

echo "Caching config/routes/views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

if [ ! -e /var/www/html/public/storage ]; then
    php artisan storage:link
fi

exec "$@"
