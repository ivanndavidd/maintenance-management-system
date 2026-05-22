#!/bin/sh
set -e

echo "========================================"
echo "Starting Warehouse Maintenance System"
echo "========================================"

mkdir -p /var/log/php /var/lib/php/sessions
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/log/php /var/lib/php/sessions
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

MAX_TRIES=30
TRIES=0
until php -r "new PDO('mysql:host=${DB_HOST:-db};dbname=${DB_DATABASE:-warehouse_maintenance}', '${DB_USERNAME:-warehouse_user}', '${DB_PASSWORD:-secret}');" 2>/dev/null; do
    TRIES=$((TRIES + 1))
    if [ $TRIES -ge $MAX_TRIES ]; then echo "ERROR: Could not connect to database after $MAX_TRIES attempts"; exit 1; fi
    echo "Database not ready, waiting... (attempt $TRIES/$MAX_TRIES)"
    sleep 2
done
echo "Database connection successful!"

cd /var/www/html

if [ ! -f /var/www/html/bootstrap/cache/packages.php ]; then
    php /var/www/html/docker/generate-packages.php
fi

echo "========================================"
echo "Initialization complete!"
echo "========================================"

exec "$@"
