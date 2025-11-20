# Getting Started - Sistem Absensi Real-Time

**Panduan Lengkap Penggunaan Aplikasi dengan Docker**

---

## 📋 Prerequisites

Pastikan sudah terinstall:
- **Docker Desktop** (Windows/Mac) atau **Docker Engine** (Linux)
- **Docker Compose** v2.0+
- **Git** (untuk clone repository)
- **RAM minimum** 4GB (recommended 8GB)
- **Port tersedia**: 8000, 8081, 3307, 6379

---

## 🚀 Quick Start

### 1. Clone Repository (jika belum)
```bash
git clone <repository-url>
cd Sistem-Absensi
```

### 2. Setup Environment
File `.env` dan `docker/.env.docker` sudah dikonfigurasi. Tidak perlu diubah untuk development.

### 3. Start Aplikasi dengan Docker Compose

#### Menggunakan Docker Compose (Recommended untuk Development):
```bash
# Build images
docker-compose build

# Start semua services
docker-compose up -d

# Tunggu ~30 detik untuk database migration selesai
# Check status
docker-compose ps
```

**Semua container harus status "Up" atau "Healthy"**

### 4. Akses Aplikasi
- **URL**: http://localhost:8000
- **Login Admin**: 
  - Email: `admin@test.com`
  - Password: `password`
- **Login Karyawan**: 
  - Email: `user@test.com` (atau user lain hasil seeding)
  - Password: `password`

---

## 🎯 Fitur Utama

### Untuk Admin:
1. **Dashboard Real-Time**
   - Lihat absensi terbaru secara live
   - Statistik hari ini, minggu ini, tingkat kehadiran
   - Status 4 server (Jakarta, Bandung, Surabaya, Bali)
   
2. **Riwayat Absensi**
   - Tab "Hari Ini (Live)" → Real-time updates via WebSocket
   - Tab "Semua Riwayat" → Filter tanggal, search nama
   - Export CSV

3. **Manajemen User**
   - CRUD karyawan
   - Reset password
   - Assign role

### Untuk Karyawan:
1. **Dashboard Portal**
   - Status absensi hari ini
   - Tombol "Absen Masuk" / "Absen Pulang"
   
2. **Riwayat Pribadi**
   - Lihat riwayat absensi sendiri
   - Filter tanggal
   - Export CSV

---

## 🐳 Docker Commands

### Monitoring

#### Lihat Status Semua Container:
```bash
docker-compose ps
```

#### Lihat Logs Real-Time:
```bash
# Semua services
docker-compose logs -f

# Service tertentu
docker-compose logs -f app-node-1
docker-compose logs -f queue-worker
docker-compose logs -f reverb
docker-compose logs -f nginx
```

#### Check Container Health:
```bash
# Lihat health status
docker ps --format "table {{.Names}}\t{{.Status}}"
```

### Restart Services

#### Restart Semua:
```bash
docker-compose restart
```

#### Restart Service Tertentu:
```bash
docker-compose restart reverb
docker-compose restart queue-worker
docker-compose restart app-node-1
```

### Stop & Remove

#### Stop Semua (container tetap ada):
```bash
docker-compose stop
```

#### Stop & Remove (hapus container, tapi data tetap):
```bash
docker-compose down
```

#### Clean Total (HATI-HATI: Hapus data juga):
```bash
docker-compose down -v
```

---

## 🔧 Troubleshooting

### 1. Port Already in Use
**Error**: `ports are not available: listen tcp 0.0.0.0:8000`

**Solusi**:
```bash
# Check proses yang pakai port
netstat -ano | findstr :8000
netstat -ano | findstr :8081
netstat -ano | findstr :3307

# Kill proses (Windows, ganti PID)
taskkill /PID <PID> /F

# Atau ubah port di docker-compose.yml
```

### 2. Services Tidak Healthy
**Gejala**: Container restart terus atau status "unhealthy"

**Debug**:
```bash
# Lihat logs
docker-compose logs app-node-1

# Masuk ke container
docker exec -it sistemabsensi-app-node-1-1 sh

# Check koneksi database dari dalam container
docker exec -it sistemabsensi-app-node-1-1 php artisan tinker
# >>> DB::connection()->getPdo();
```

### 3. WebSocket Tidak Connect
**Gejala**: "Disconnected" di dashboard

**Check**:
```bash
# Reverb logs
docker-compose logs -f reverb

# Queue worker logs  
docker-compose logs -f queue-worker

# Test koneksi dari browser console
curl http://localhost:8081
```

**Fix**:
```bash
# Restart Reverb dan Queue Worker
docker-compose restart reverb queue-worker

# Jika masih gagal, rebuild
docker-compose build reverb queue-worker
docker-compose up -d reverb queue-worker
```

### 4. Migration Error
**Error**: `SQLSTATE[HY000] [2002] Connection refused`

**Solusi**:
```bash
# Tunggu MySQL fully ready
docker-compose logs mysql

# Re-run migration manual
docker-compose run --rm migration
```

### 5. Data Tidak Muncul di Dashboard
**Check Order**:
1. ✅ Queue Worker running?
   ```bash
   docker-compose ps queue-worker
   ```
2. ✅ Reverb connected? (Lihat indicator di dashboard)
3. ✅ Event dispatched? 
   ```bash
   docker-compose logs queue-worker | grep "AttendanceCreated"
   ```

---

## 📊 Database Access

### Via Docker:
```bash
# Masuk ke MySQL container
docker exec -it sistemabsensi-mysql-1 mysql -u absensi -pAbsensiSecure2025! absensi

# Query
mysql> SELECT * FROM users;
mysql> SELECT * FROM attendances WHERE date = CURDATE();
```

### Via MySQL Client (dari host):
```bash
mysql -h 127.0.0.1 -P 3307 -u absensi -pAbsensiSecure2025! absensi
```

### Via phpMyAdmin (jika ingin):
Tambahkan di `docker-compose.yml`:
```yaml
  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    ports:
      - "8888:80"
    environment:
      PMA_HOST: mysql
      PMA_PORT: 3306
    networks:
      - absensi-network
```

Akses: http://localhost:8888

---

## 🔄 Update & Maintenance

### Update Code:
```bash
# Pull latest code
git pull

# Rebuild images
docker-compose build

# Restart dengan image baru
docker-compose up -d
```

### Reset Database:
```bash
# Run migration fresh + seed
docker-compose run --rm migration sh -c "php artisan migrate:fresh --force && php artisan db:seed --force"
```

### Clear Cache:
```bash
docker-compose exec app-node-1 php artisan cache:clear
docker-compose exec app-node-1 php artisan config:clear
docker-compose exec app-node-1 php artisan route:clear
```

---

## 🎓 Testing Real-Time Features

### Test Scenario 1: Real-Time Dashboard
1. Login sebagai **Admin** di tab pertama
2. Buka **Dashboard** → Lihat "Absensi Terbaru" (kosong)
3. Di tab kedua, login sebagai **Karyawan**
4. Klik "Absen Masuk"
5. **Kembali ke tab Admin** → Entry baru muncul OTOMATIS tanpa refresh!

### Test Scenario 2: Multi-Node Load Distribution
Lihat dokumentasi **TESTING-DISTRIBUTED.md** untuk detail testing distribusi.

---

## 📱 Production Deployment

Untuk production dengan Docker Swarm, lihat:
- **DOCKER-SWARM-DEPLOYMENT.md**

---

## 🆘 Support

Jika mengalami masalah:
1. Check logs: `docker-compose logs -f`
2. Verify health: `docker-compose ps`
3. Restart services: `docker-compose restart`
4. Last resort: `docker-compose down && docker-compose up -d`

---

**Happy Coding! 🚀**
