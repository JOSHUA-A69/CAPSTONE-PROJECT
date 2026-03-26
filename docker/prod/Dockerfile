# Stage 1: Build Frontend Assets
FROM node:20 as frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: Build Backend Dependencies
FROM composer:2.6 as vendor
WORKDIR /app
COPY composer.json composer.lock ./
# Install non-dev dependencies, optimize autoloader
RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --no-dev \
    --prefer-dist \
    --ignore-platform-reqs

# Stage 3: Final Production Image
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd intl zip \
    && a2enmod rewrite

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy Apache configuration
COPY docker/prod/vhost.conf /etc/apache2/sites-available/000-default.conf

# Copy backend dependencies from vendor stage
COPY --from=vendor /app/vendor/ /var/www/html/vendor/

# Copy frontend assets from frontend stage
COPY --from=frontend /app/public/build/ /var/www/html/public/build/

# Copy application source
COPY . .

# Ensure permissions are correct
# We assume the container runs as root or www-data.
# Apache usually runs as www-data.
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Copy entrypoint
COPY docker/prod/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port (Render sets $PORT, but Apache defaults to 80.
# We configure Apache to listen on port 80. Render's load balancer maps to this.)
EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
