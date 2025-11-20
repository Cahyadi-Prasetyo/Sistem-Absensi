#!/bin/sh

set -e

echo "🚀 Starting Laravel application..."
echo "📍 Node ID: ${APP_NODE_ID:-unknown}"
echo "🌍 Environment: ${APP_ENV:-production}"

# Wait for MySQL to be ready
if [ -n "$DB_HOST" ]; then
    echo "⏳ Waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306}..."
    max_attempts=60
    attempt=0
    
    until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT:-3306};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" > /dev/null 2>&1; do
        attempt=$((attempt + 1))
        if [ $attempt -ge $max_attempts ]; then
            echo "❌ Database connection timeout after ${max_attempts} attempts"
            exit 1
        fi
        echo "⏳ Database unavailable - attempt ${attempt}/${max_attempts}"
        sleep 2
    done
    
    echo "✅ Database is ready!"
fi

# Wait for Redis to be ready
if [ -n "$REDIS_HOST" ]; then
    echo "⏳ Waiting for Redis at ${REDIS_HOST}:${REDIS_PORT:-6379}..."
    max_attempts=30
    attempt=0
    
    until php -r "try { \$redis = new Redis(); \$redis->connect('${REDIS_HOST}', ${REDIS_PORT:-6379}); echo 'OK'; } catch (Exception \$e) { exit(1); }" > /dev/null 2>&1; do
        attempt=$((attempt + 1))
        if [ $attempt -ge $max_attempts ]; then
            echo "⚠️ Redis connection timeout, continuing anyway..."
            break
        fi
        echo "⏳ Redis unavailable - attempt ${attempt}/${max_attempts}"
        sleep 1
    done
    
    echo "✅ Redis is ready!"
fi

# Run migrations and seeding (SKIP if flagged to prevent conflicts)
if [ "${SKIP_MIGRATION}" != "true" ]; then
    if [ "${APP_NODE_ID}" = "migration-runner" ]; then
        echo "🔄 Running database migrations..."
        php artisan migrate --force || {
            echo "❌ Migration failed"
            exit 1
        }
        
        echo "🌱 Seeding database..."
        php artisan db:seed --class=ResetDatabaseSeeder --force || {
            echo "⚠️ Seeding failed or already seeded, continuing..."
        }
        
        echo "✅ Database initialized successfully!"
        exit 0  # Migration service exits after completion
    fi
else
    echo "⏭️ Skipping migrations (SKIP_MIGRATION=true)"
fi

# Clear and cache config (SKIP if flagged to prevent file conflicts)
if [ "${SKIP_CACHE}" != "true" ]; then
    echo "🧹 Optimizing application..."
    php artisan config:clear || true
    php artisan cache:clear || true
    php artisan config:cache || echo "⚠️ Config cache failed"
    php artisan route:cache || echo "⚠️ Route cache failed"
    php artisan view:cache || echo "⚠️ View cache failed"
else
    echo "⏭️ Skipping cache generation (SKIP_CACHE=true)"
    php artisan config:clear || true
    php artisan cache:clear || true
fi

echo "✅ Application ready!"

# Check if custom command is provided
if [ $# -gt 0 ]; then
    echo "▶️ Executing custom command: $@"
    exec "$@"
else
    echo "▶️ Starting PHP-FPM..."
    exec php-fpm
fi
