#!/bin/bash
set -e

# Run initial commands to prepare environment
# Note: Migrations might be better run in a "Release Phase" command on Render,
# but can be included here if using a single instance and careful about locks.
# We'll skip auto-migrations on startup to avoid issues with zero-downtime,
# relying on the user to run them or configure a release command.

# Cache configuration, routes, and views for production performance
# (Ensure APP_KEY is set in environment)
if [ -n "$APP_KEY" ]; then
    echo "Debugging: Checking Environment Variables..."
    echo "MAIL_HOST is set to: ${MAIL_HOST:-'NOT SET'}"
    echo "MAIL_USERNAME is set to: ${MAIL_USERNAME:-'NOT SET'}"
    
    echo "Clearing caches..."
    php artisan config:clear
    
    echo "Caching configuration..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Link storage (idempotent)
php artisan storage:link

# Fix permissions for storage and cache (Crucial for uploads in prod)
# Ensure www-data (Apache user) owns the directories
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Run migrations automatically (Required for Free Tier with no Shell access)
echo "Running migrations..."
php artisan migrate --force

# Run March Scenario Seeder (Enabled temporarily)
# echo "Running March Scenario Seeder..."
# php artisan seed:march-scenario

# Run seeds automatically (Disabled to prevent duplicate data issues on re-deploy)
# echo "Running seeds..."
# php artisan db:seed --force

# Start Apache
exec docker-php-entrypoint apache2-foreground
