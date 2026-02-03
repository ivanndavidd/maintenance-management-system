#!/bin/sh
set -e

echo "========================================"
echo "Starting Warehouse Maintenance System"
echo "========================================"

# Create required directories
echo "Creating required directories..."
mkdir -p /var/log/php
mkdir -p /var/lib/php/sessions
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/bootstrap/cache

# Set permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/log/php
chown -R www-data:www-data /var/lib/php/sessions
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Wait for database to be ready
echo "Waiting for database connection..."
MAX_TRIES=30
TRIES=0
until php -r "new PDO('mysql:host=${DB_HOST:-db};dbname=${DB_DATABASE:-warehouse_maintenance}', '${DB_USERNAME:-warehouse_user}', '${DB_PASSWORD:-secret}');" 2>/dev/null; do
    TRIES=$((TRIES + 1))
    if [ $TRIES -ge $MAX_TRIES ]; then
        echo "ERROR: Could not connect to database after $MAX_TRIES attempts"
        exit 1
    fi
    echo "Database not ready, waiting... (attempt $TRIES/$MAX_TRIES)"
    sleep 2
done
echo "Database connection successful!"

# Run Laravel setup commands
cd /var/www/html

# Check if .env exists
if [ ! -f .env ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
fi

# Generate application key if not set
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Clear and cache configuration
echo "Optimizing Laravel..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Skip auto-migration on startup to avoid conflicts
# Run migrations manually after container is up: docker compose exec app php artisan migrate --force
# echo "Running database migrations..."
# php artisan migrate --force

# Create storage link if not exists
if [ ! -L /var/www/html/public/storage ]; then
    echo "Creating storage link..."
    php artisan storage:link
fi

echo "========================================"
echo "Initialization complete!"
echo "========================================"

# Execute the main command
exec "$@"
