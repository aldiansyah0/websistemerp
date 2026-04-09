#!/bin/sh
set -e

echo "==> Caching Laravel config..."
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction
php artisan event:cache --no-interaction

echo "==> Running migrations..."
php artisan migrate --force --no-interaction

echo "==> Linking storage..."
php artisan storage:link 2>/dev/null || true

echo "==> Starting supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/laravel-worker.conf
