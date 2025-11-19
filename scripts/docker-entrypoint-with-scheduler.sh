#!/bin/sh

set -e

echo "Starting Laravel application..."
echo "Node ID: ${APP_NODE_ID:-unknown}"
echo "Environment: ${APP_ENV:-production}"

# Wait for MySQL to be ready
if [ -n "$DB_HOST" ]; then
    echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306}..."
    max_attempts=30
    attempt=0
    
    until php artisan health:check > /dev/null 2>&1; do
        attempt=$((attempt + 1))
        if [ $attempt -ge $max_attempts ]; then
            echo "ERROR: Database connection timeout after ${max_attempts} attempts"
            exit 1
        fi
        echo "Database is unavailable - attempt ${attempt}/${max_attempts}"
        sleep 2
    done
    
    echo "✓ Database is ready!"
fi

# Wait for Redis to be ready
if [ -n "$REDIS_HOST" ]; then
    echo "Waiting for Redis at ${REDIS_HOST}:${REDIS_PORT:-6379}..."
    max_attempts=15
    attempt=0
    
    until php -r "try { \$redis = new Redis(); \$redis->connect('${REDIS_HOST}', ${REDIS_PORT:-6379}); echo 'OK'; } catch (Exception \$e) { exit(1); }" > /dev/null 2>&1; do
        attempt=$((attempt + 1))
        if [ $attempt -ge $max_attempts ]; then
            echo "WARNING: Redis connection timeout, continuing anyway..."
            break
        fi
        echo "Redis is unavailable - attempt ${attempt}/${max_attempts}"
        sleep 1
    done
    
    echo "✓ Redis is ready!"
fi

# Run migrations and seeding (only on first node to avoid conflicts)
if [ "${APP_NODE_ID}" = "app-node-1" ] || [ -z "${APP_NODE_ID}" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || {
        echo "ERROR: Migration failed"
        exit 1
    }
    
    echo "Seeding database..."
    php artisan db:seed --class=ResetDatabaseSeeder --force || {
        echo "WARNING: Seeding failed or already seeded, continuing..."
    }
fi

# Clear and cache config
echo "Optimizing application..."
php artisan config:clear
php artisan cache:clear
php artisan config:cache || echo "WARNING: Config cache failed"
php artisan route:cache || echo "WARNING: Route cache failed"
php artisan view:cache || echo "WARNING: View cache failed"

echo "✓ Application ready!"

# Start background scheduler for heartbeat (in background)
echo "Starting background scheduler for heartbeat..."
(
    while true; do
        php artisan schedule:run >> /dev/null 2>&1
        sleep 30
    done
) &

# Check if custom command is provided
if [ $# -gt 0 ]; then
    echo "Executing custom command: $@"
    exec "$@"
else
    echo "Starting PHP-FPM..."
    exec php-fpm
fi
