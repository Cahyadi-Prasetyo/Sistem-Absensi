# 🚀 Deployment Guide

Guide for deploying Laravel Reverb Absensi to production.

---

## ✅ Pre-Deployment Checklist

### Environment
- [ ] `.env.production` configured
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] Strong `APP_KEY` generated
- [ ] Strong `DB_PASSWORD` set
- [ ] `REVERB_SCHEME=https` (if using SSL)

### Security
- [ ] All `.env` files in `.gitignore`
- [ ] No sensitive data in repository
- [ ] SSL certificate installed
- [ ] Firewall configured

### Infrastructure
- [ ] Docker installed
- [ ] Docker Compose installed
- [ ] Sufficient resources (CPU, RAM, Disk)
- [ ] Backup strategy in place

---

## 🐳 Docker Deployment

### 1. Setup Environment

```bash
# Copy template
cp .env.docker.example .env.production

# Generate keys
php artisan key:generate
php artisan reverb:install

# Edit .env.production
nano .env.production
```

**Required changes:**
```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your-generated-key
DB_PASSWORD=strong-password-here
REVERB_APP_SECRET=your-generated-secret
REVERB_SCHEME=https
```

### 2. Build & Start

```bash
# Build images
docker-compose build

# Start services
docker-compose up -d

# Check status
docker-compose ps
```

### 3. Setup Database

```bash
# Run migrations
docker exec laravel_absensi_app_1 php artisan migrate --force

# Seed (optional)
docker exec laravel_absensi_app_1 php artisan db:seed
```

### 4. Optimize

```bash
# Cache config
docker exec laravel_absensi_app_1 php artisan config:cache

# Cache routes
docker exec laravel_absensi_app_1 php artisan route:cache

# Cache views
docker exec laravel_absensi_app_1 php artisan view:cache
```

---

## 🔒 SSL/HTTPS Setup

### Using Let's Encrypt

1. Install Certbot
2. Generate certificate
3. Update nginx config
4. Update `.env`: `REVERB_SCHEME=https`

### Nginx SSL Configuration

```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    # ... rest of config
}
```

---

## 📊 Monitoring

### Health Checks

```bash
# Application
curl https://your-domain.com/health

# WebSocket
curl https://your-domain.com:8080

# Database
docker exec laravel_absensi_mysql mysqladmin ping

# Redis
docker exec laravel_absensi_redis redis-cli ping
```

### Logs

```bash
# View all logs
docker-compose logs -f

# Specific service
docker-compose logs -f app_1

# Laravel logs
docker exec laravel_absensi_app_1 tail -f storage/logs/laravel.log
```

---

## 🔄 Updates & Maintenance

### Update Application

```bash
# 1. Pull latest code
git pull origin main

# 2. Rebuild containers
docker-compose build

# 3. Restart services
docker-compose up -d

# 4. Run migrations
docker exec laravel_absensi_app_1 php artisan migrate --force

# 5. Clear cache
docker exec laravel_absensi_app_1 php artisan optimize:clear
docker exec laravel_absensi_app_1 php artisan config:cache
```

### Zero-Downtime Deployment

```bash
# 1. Scale up
docker-compose up -d --scale app_1=4

# 2. Update one by one
docker-compose stop app_1
docker-compose up -d app_1

# 3. Scale back
docker-compose up -d --scale app_1=3
```

---

## 💾 Backup Strategy

### Database Backup

```bash
# Manual backup
docker exec laravel_absensi_mysql mysqldump -u root -p laravel_absensi > backup.sql

# Automated backup (cron)
0 2 * * * docker exec laravel_absensi_mysql mysqldump -u root -p laravel_absensi > /backups/$(date +\%Y\%m\%d).sql
```

### Application Backup

```bash
# Backup storage
tar -czf storage-backup.tar.gz storage/

# Backup .env
cp .env.production .env.backup
```

---

## 🔧 Troubleshooting Production

### High CPU Usage

```bash
# Check processes
docker stats

# Scale up
docker-compose up -d --scale app_1=5
```

### Memory Issues

```bash
# Check memory
docker stats

# Restart services
docker-compose restart
```

### Database Issues

```bash
# Check connections
docker exec laravel_absensi_mysql mysql -u root -p -e "SHOW PROCESSLIST;"

# Optimize tables
docker exec laravel_absensi_app_1 php artisan db:optimize
```

---

## 📈 Performance Optimization

### Application

- Enable OPcache
- Use Redis for sessions
- Queue long-running tasks
- Optimize database queries

### Database

- Add indexes
- Optimize queries
- Regular maintenance
- Connection pooling

### Frontend

- Minify assets
- Use CDN
- Enable gzip
- Browser caching

---

## 🚨 Disaster Recovery

### If Application Down

1. Check logs: `docker-compose logs`
2. Restart services: `docker-compose restart`
3. Check resources: `docker stats`
4. Restore from backup if needed

### If Database Corrupted

1. Stop application
2. Restore from backup
3. Run migrations
4. Restart application

---

**Status:** ✅ Production Ready  
**Version:** 1.0.0 MVP
