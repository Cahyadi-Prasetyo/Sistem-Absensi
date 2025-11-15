# Docker Deployment Guide - Sistem Absensi Real-Time

Panduan lengkap untuk menjalankan Sistem Absensi Real-Time menggunakan Docker.

## 🚀 Quick Start

### Windows
```cmd
docker\deploy.bat
```

### Linux/Mac
```bash
chmod +x docker/deploy.sh
./docker/deploy.sh
```

Atau manual:
```bash
docker-compose up -d --build
docker-compose exec app-node-1 php artisan migrate --force
docker-compose exec app-node-1 php artisan db:seed --class=ResetDatabaseSeeder --force
```

## 📋 Prerequisites

- Docker 20.10+
- Docker Compose 2.0+
- RAM minimal 4GB
- Disk space minimal 10GB

## 🏗️ Arsitektur

Sistem ini terdiri dari 10 containers:

1. **nginx** - Load balancer (port 80)
2. **app-node-1, app-node-2, app-node-3** - Laravel application nodes
3. **subscriber-node-1, subscriber-node-2, subscriber-node-3** - Redis pub/sub subscribers
4. **reverb** - WebSocket server (port 8080)
5. **mysql** - Database (port 3306)
6. **redis** - Cache & pub/sub (port 6379)

## 🔧 Configuration

### Environment Variables

File `docker/.env.docker` berisi konfigurasi default untuk Docker. Anda bisa menyesuaikan:

- `APP_NAME` - Nama aplikasi
- `APP_DEBUG` - Debug mode (true/false)
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` - Database credentials
- `REVERB_APP_KEY`, `REVERB_APP_SECRET` - WebSocket credentials

### Ports

Default ports yang digunakan:
- **80** - HTTP (Nginx)
- **8080** - WebSocket (Reverb)
- **3306** - MySQL
- **6379** - Redis

Jika port sudah digunakan, edit `docker-compose.yml`:
```yaml
ports:
  - "8000:80"  # Ubah port 80 ke 8000
```

## 📝 Common Commands

### Start Services
```bash
docker-compose up -d
```

### Stop Services
```bash
docker-compose down
```

### View Logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app-node-1
docker-compose logs -f nginx
docker-compose logs -f reverb
```

### Restart Services
```bash
# All services
docker-compose restart

# Specific service
docker-compose restart app-node-1
```

### Check Status
```bash
docker-compose ps
```

### Execute Commands
```bash
# Run artisan commands
docker-compose exec app-node-1 php artisan cache:clear
docker-compose exec app-node-1 php artisan config:clear

# Access container shell
docker-compose exec app-node-1 sh

# Access MySQL
docker-compose exec mysql mysql -u absensi -psecret absensi

# Access Redis CLI
docker-compose exec redis redis-cli
```

### Database Operations
```bash
# Run migrations
docker-compose exec app-node-1 php artisan migrate --force

# Seed database
docker-compose exec app-node-1 php artisan db:seed --force

# Reset database
docker-compose exec app-node-1 php artisan migrate:fresh --seed --force
```

## 🐛 Troubleshooting

### Services tidak mau start

```bash
# Cek port yang sudah digunakan
netstat -ano | findstr :80
netstat -ano | findstr :3306

# Stop dan hapus semua containers
docker-compose down -v

# Rebuild dari awal
docker-compose up -d --build --force-recreate
```

### Error koneksi database

```bash
# Tunggu MySQL siap (butuh ~30 detik)
docker-compose logs mysql

# Cek kesehatan MySQL
docker-compose exec mysql mysqladmin ping -h localhost -u root -proot_secret

# Restart MySQL
docker-compose restart mysql
```

### Build error

```bash
# Hapus cache Docker
docker system prune -a

# Rebuild tanpa cache
docker-compose build --no-cache
docker-compose up -d
```

### WebSocket tidak connect

```bash
# Cek Reverb logs
docker-compose logs -f reverb

# Restart Reverb
docker-compose restart reverb

# Test koneksi WebSocket
curl -i -N -H "Connection: Upgrade" -H "Upgrade: websocket" http://localhost:8080/app
```

### Redis pub/sub tidak berfungsi

```bash
# Cek subscriber logs
docker-compose logs -f subscriber-node-1

# Restart subscribers
docker-compose restart subscriber-node-1 subscriber-node-2 subscriber-node-3

# Test Redis
docker-compose exec redis redis-cli ping
```

### Permission errors

```bash
# Fix permissions
docker-compose exec app-node-1 chmod -R 775 storage bootstrap/cache
docker-compose exec app-node-1 chown -R www-data:www-data storage bootstrap/cache
```

### Out of memory

```bash
# Cek resource usage
docker stats

# Tingkatkan memory limit di Docker Desktop settings
# Recommended: 4GB+ RAM
```

## 🧪 Testing

### Test Load Balancing

```bash
# Buat multiple requests
for i in {1..10}; do curl -s http://localhost | grep "node"; done
```

### Test Failover

```bash
# Stop satu node
docker-compose stop app-node-1

# Aplikasi tetap berfungsi via node-2 dan node-3
curl http://localhost

# Restart node
docker-compose start app-node-1
```

### Test Real-Time Sync

1. Buka 2 browser tabs ke http://localhost
2. Login sebagai karyawan di tab 1
3. Login sebagai admin di tab 2
4. Lakukan absensi di tab 1
5. Dashboard admin di tab 2 akan update real-time

### Monitor Redis Pub/Sub

```bash
# Subscribe ke channel
docker-compose exec redis redis-cli
> SUBSCRIBE absensi-channel

# Di terminal lain, lakukan absensi
# Anda akan melihat events yang dipublikasikan
```

## 🔒 Security Notes

### Production Deployment

Untuk production, ubah:

1. **APP_KEY** - Generate key baru:
   ```bash
   docker-compose exec app-node-1 php artisan key:generate --show
   ```

2. **Database Password** - Gunakan password yang kuat di `docker-compose.yml`

3. **REVERB_APP_SECRET** - Generate secret baru

4. **APP_DEBUG** - Set ke `false`

5. **HTTPS** - Setup SSL certificate untuk Nginx

## 📊 Monitoring

### Resource Usage

```bash
docker stats
```

### Health Checks

```bash
# Nginx
curl http://localhost/nginx-health

# Application
docker-compose exec app-node-1 php artisan health:check

# All services
docker-compose ps
```

### Logs Location

Logs disimpan di:
- Container: `/var/www/html/storage/logs`
- Host: `./storage/logs`

## 🔄 Updates

### Update Code

```bash
# Pull latest code
git pull

# Rebuild containers
docker-compose up -d --build

# Run migrations
docker-compose exec app-node-1 php artisan migrate --force
```

### Update Dependencies

```bash
# Rebuild dengan --no-cache
docker-compose build --no-cache
docker-compose up -d
```

## 🗑️ Cleanup

### Remove All Containers

```bash
docker-compose down
```

### Remove Containers and Volumes

```bash
docker-compose down -v
```

### Remove All Docker Data

```bash
docker system prune -a --volumes
```

## 📞 Support

Jika mengalami masalah:

1. Cek logs: `docker-compose logs -f`
2. Cek status: `docker-compose ps`
3. Cek resource: `docker stats`
4. Restart services: `docker-compose restart`

## 📚 Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Reverb Documentation](https://reverb.laravel.com/)
