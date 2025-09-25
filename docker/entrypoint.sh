#!/bin/bash

# Wait for MySQL to be available
echo "Waiting for MySQL..."
while ! nc -z db 3306; do
    sleep 1
done
echo "MySQL is available"

# Wait for Redis to be available
echo "Waiting for Redis..."
while ! nc -z redis 6379; do
    sleep 1
done
echo "Redis is available"

# Generate application key if it doesn't exist
php artisan key:generate --no-interaction --force

# Run migrations
php artisan migrate --no-interaction --force

# Run seeders
php artisan db:seed --no-interaction --force

# Install dependencies and build assets
npm install --legacy-peer-deps
npm run build

# Start PHP-FPM
php-fpm
