FROM php:8.3-fpm-alpine

# Build arguments for Vite
ARG VITE_REVERB_APP_KEY=reverb-key
ARG VITE_REVERB_HOST=localhost
ARG VITE_REVERB_PORT=8080
ARG VITE_REVERB_SCHEME=http

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-dev \
    nodejs \
    npm \
    bash \
    mysql-client

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip pcntl

# Install Redis extension
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del pcre-dev $PHPIZE_DEPS

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies (without dev dependencies for production)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy package files for npm
COPY package*.json ./

# Install npm dependencies
RUN npm ci --legacy-peer-deps || npm install --legacy-peer-deps

# Copy application files
COPY . .

# Copy .env.production for Vite build
COPY .env.production .env

# Complete composer installation
RUN composer dump-autoload --optimize

# Create necessary directories with proper permissions
RUN mkdir -p \
    storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public \
    bootstrap/cache \
    public/build

# Set environment variables for Vite build
ENV VITE_REVERB_APP_KEY=${VITE_REVERB_APP_KEY}
ENV VITE_REVERB_HOST=${VITE_REVERB_HOST}
ENV VITE_REVERB_PORT=${VITE_REVERB_PORT}
ENV VITE_REVERB_SCHEME=${VITE_REVERB_SCHEME}

# Build frontend assets with environment variables
RUN echo "Building assets with VITE_REVERB_APP_KEY=${VITE_REVERB_APP_KEY}" && \
    npm run build || { \
    echo "ERROR: npm build failed"; \
    echo "Checking if vite.config.js exists..."; \
    ls -la vite.config.js || echo "vite.config.js not found"; \
    exit 1; \
}

# Verify build output
RUN if [ ! -d "public/build" ]; then \
    echo "ERROR: Build directory not created"; \
    exit 1; \
    fi && \
    echo "✓ Build successful" && \
    ls -la public/build

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set proper permissions
RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    /var/www/html/public && \
    chmod -R 775 \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

# Configure PHP-FPM to listen on all interfaces
RUN sed -i 's/listen = 127.0.0.1:9000/listen = 0.0.0.0:9000/' /usr/local/etc/php-fpm.d/www.conf

# Expose PHP-FPM port
EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD php artisan health:check || exit 1

ENTRYPOINT ["docker-entrypoint.sh"]
