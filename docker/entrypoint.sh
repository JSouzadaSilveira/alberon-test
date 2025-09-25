#!/bin/bash

# Wait for MySQL to be available
echo "Waiting for MySQL..."
max_tries=30
count=0
while ! nc -z db 3306; do
    sleep 1
    count=$((count+1))
    if [ $count -gt $max_tries ]; then
        echo "Error: MySQL is not available after $max_tries seconds"
        exit 1
    fi
done
echo "MySQL is available"

# Test MySQL connection
php -r "
\$tries = 30;
while (\$tries > 0) {
    try {
        new PDO('mysql:host=db;port=3306;dbname=alberon', 'alberon', 'alberon_password');
        echo \"MySQL connection successful\n\";
        break;
    } catch (PDOException \$e) {
        echo \"Waiting for MySQL to be ready...\n\";
        sleep(1);
        \$tries--;
    }
}
if (\$tries == 0) {
    echo \"Error: Could not connect to MySQL after 30 attempts\n\";
    exit(1);
}"

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
