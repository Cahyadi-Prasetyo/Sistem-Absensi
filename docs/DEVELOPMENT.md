# 💻 Development Guide

Guide for developing Laravel Reverb Absensi.

---

## 🚀 Development Workflow

### Start Development Environment

```bash
# Start all services
docker-compose up -d

# Watch logs
docker-compose logs -f app_1 reverb

# Watch frontend changes
npm run dev
```

### Make Changes

1. Edit code in your IDE
2. Changes auto-sync via Docker volumes
3. Frontend hot-reloads (if using `npm run dev`)
4. Backend requires container restart for some changes

### Test Changes

```bash
# Run tests
docker exec laravel_absensi_app_1 php artisan test

# Check code style
docker exec laravel_absensi_app_1 ./vendor/bin/pint

# Type check
npm run type-check
```

---

## 🐳 Docker Commands

### Container Management

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# Restart specific service
docker-compose restart app_1

# View logs
docker-compose logs -f [service]

# Check status
docker-compose ps
```

### Execute Commands

```bash
# Run artisan
docker exec laravel_absensi_app_1 php artisan [command]

# Access shell
docker exec -it laravel_absensi_app_1 bash

# Run composer
docker exec laravel_absensi_app_1 composer [command]
```

---

## 🗄️ Database Management

### Migrations

```bash
# Run migrations
docker exec laravel_absensi_app_1 php artisan migrate

# Rollback
docker exec laravel_absensi_app_1 php artisan migrate:rollback

# Fresh migration
docker exec laravel_absensi_app_1 php artisan migrate:fresh

# With seeding
docker exec laravel_absensi_app_1 php artisan migrate:fresh --seed
```

### Seeding

```bash
# Run all seeders
docker exec laravel_absensi_app_1 php artisan db:seed

# Run specific seeder
docker exec laravel_absensi_app_1 php artisan db:seed --class=AttendanceSeeder
```

### Database Access

```bash
# MySQL CLI
docker exec -it laravel_absensi_mysql mysql -u root -p

# Tinker
docker exec -it laravel_absensi_app_1 php artisan tinker
```

---

## 🎨 Frontend Development

### Build Commands

```bash
# Development (with hot reload)
npm run dev

# Production build
npm run build

# Type check
npm run type-check

# Lint
npm run lint
```

### Component Development

**Location:** `resources/js/components/`

**Example:**
```vue
<script setup lang="ts">
import { ref } from 'vue'

const count = ref(0)
</script>

<template>
  <button @click="count++">
    Count: {{ count }}
  </button>
</template>
```

---

## 🔧 Troubleshooting

### Port Already in Use

```bash
# Windows
netstat -ano | findstr :80

# Kill process
taskkill /PID [PID] /F
```

### Permission Issues

```bash
# Fix storage permissions
docker exec laravel_absensi_app_1 chmod -R 775 storage bootstrap/cache
docker exec laravel_absensi_app_1 chown -R www-data:www-data storage bootstrap/cache
```

### Database Connection Error

```bash
# Check MySQL
docker exec laravel_absensi_mysql mysqladmin ping -h localhost

# Restart MySQL
docker-compose restart mysql

# Check .env
DB_HOST=mysql  # NOT 127.0.0.1
```

### Redis Connection Error

```bash
# Check Redis
docker exec laravel_absensi_redis redis-cli ping

# Should return: PONG

# Restart Redis
docker-compose restart redis
```

### Echo/WebSocket Not Working

```bash
# Check Reverb logs
docker-compose logs reverb

# Restart Reverb
docker-compose restart reverb

# Rebuild frontend
npm run build

# Check browser console for errors
```

### Container Won't Start

```bash
# Check logs
docker-compose logs [service]

# Rebuild
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

---

## 🧪 Testing

### Run Tests

```bash
# All tests
docker exec laravel_absensi_app_1 php artisan test

# Specific test
docker exec laravel_absensi_app_1 php artisan test --filter=AttendanceTest

# With coverage
docker exec laravel_absensi_app_1 php artisan test --coverage
```

### Test Real-Time

1. Open 2 browser tabs
2. Tab 1: Login, check-in
3. Tab 2: Watch dashboard auto-update

### Load Testing

```bash
# Multiple requests
for /L %i in (1,1,100) do curl http://localhost/health

# Check distribution
docker-compose logs app_1 app_2 app_3 | findstr "GET"
```

---

## 📝 Code Style

### PHP (Laravel Pint)

```bash
# Check style
docker exec laravel_absensi_app_1 ./vendor/bin/pint --test

# Fix style
docker exec laravel_absensi_app_1 ./vendor/bin/pint
```

### JavaScript/TypeScript (ESLint)

```bash
# Check
npm run lint

# Fix
npm run lint:fix
```

---

## 🔍 Debugging

### Laravel Debugbar

```bash
# Install
composer require barryvdh/laravel-debugbar --dev

# Enable in .env
APP_DEBUG=true
```

### Vue DevTools

Install browser extension:
- Chrome: Vue.js devtools
- Firefox: Vue.js devtools

### Logs

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Docker logs
docker-compose logs -f

# Specific service
docker-compose logs -f app_1
```

---

## 📚 Useful Commands

### Cache Management

```bash
# Clear all cache
docker exec laravel_absensi_app_1 php artisan optimize:clear

# Clear specific cache
docker exec laravel_absensi_app_1 php artisan cache:clear
docker exec laravel_absensi_app_1 php artisan config:clear
docker exec laravel_absensi_app_1 php artisan route:clear
docker exec laravel_absensi_app_1 php artisan view:clear
```

### Queue Management

```bash
# Start queue worker
docker exec laravel_absensi_app_1 php artisan queue:work

# List failed jobs
docker exec laravel_absensi_app_1 php artisan queue:failed

# Retry failed jobs
docker exec laravel_absensi_app_1 php artisan queue:retry all
```

---

## 🤝 Contributing

1. Fork repository
2. Create feature branch
3. Make changes
4. Write tests
5. Submit pull request

---

**Next:** [Deployment Guide](DEPLOYMENT.md) for production deployment
