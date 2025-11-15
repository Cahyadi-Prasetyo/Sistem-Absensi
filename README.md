<<<<<<< HEAD
# Laravel Reverb Absensi

Aplikasi absensi real-time menggunakan Laravel 12, Inertia.js, Vue 3, dan Laravel Reverb (WebSocket).

## Requirements

- PHP 8.2 atau lebih tinggi
- Composer
- Node.js & NPM
- SQLite (default) atau MySQL/PostgreSQL

## Tech Stack

- **Backend**: Laravel 12
- **Frontend**: Vue 3 + TypeScript
- **UI Framework**: Inertia.js
- **Authentication**: Laravel Fortify
- **Real-time**: Laravel Reverb (WebSocket)
- **Routing**: Laravel Wayfinder
- **Styling**: Tailwind CSS

## Installation

1. Clone repository ini:
=======
# Sistem Absensi Real-Time Terdistribusi

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Redis](https://img.shields.io/badge/Redis-7.0+-DC382D?style=for-the-badge&logo=redis&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-20.10+-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Nginx](https://img.shields.io/badge/Nginx-Latest-009639?style=for-the-badge&logo=nginx&logoColor=white)

**Sistem absensi karyawan modern dengan arsitektur terdistribusi, real-time synchronization, dan high availability**

[Fitur](#-fitur-utama) • [Demo](#-mulai-cepat-docker) • [Dokumentasi](#-instalasi) • [Arsitektur](#-arsitektur-sistem-terdistribusi)

</div>

---

## 📖 Tentang Proyek

Sistem Absensi Real-Time Terdistribusi adalah aplikasi web enterprise-grade yang dibangun dengan **Laravel 12** dan **Laravel Reverb** untuk mengelola absensi karyawan dengan kemampuan real-time updates dan high availability. Proyek ini mendemonstrasikan implementasi **distributed system architecture** dengan multiple application nodes, load balancing, dan event-driven communication.

### 🎯 Tujuan Proyek

- **Production-Ready**: Sistem yang siap digunakan untuk kebutuhan absensi perusahaan dengan ratusan karyawan
- **Learning Resource**: Referensi implementasi distributed system, WebSocket, dan Redis pub/sub di Laravel
- **Scalable Architecture**: Desain yang dapat di-scale horizontal dengan menambahkan lebih banyak nodes
- **Modern Tech Stack**: Menggunakan teknologi terkini seperti Laravel 12, Reverb, Alpine.js, dan Tailwind CSS

### ✨ Keunggulan

- ⚡ **Real-Time Updates** - Dashboard admin terupdate otomatis saat ada absensi baru tanpa refresh
- 🔄 **Distributed System** - 3 application nodes dengan load balancing untuk high availability
- 🛡️ **Fault Tolerant** - Sistem tetap berjalan meskipun ada node yang gagal
- 📍 **Geolocation Tracking** - Mencatat lokasi GPS saat absensi masuk dan pulang
- 📊 **Analytics Dashboard** - Metrics dan visualisasi data absensi real-time
- 🐳 **Docker Ready** - Deploy dalam hitungan menit dengan Docker Compose
- 🎨 **Modern UI/UX** - Interface yang clean dan responsive dengan Tailwind CSS

### 🏗️ Arsitektur Singkat

```
Browser ←→ Nginx Load Balancer ←→ [Laravel Node 1, Node 2, Node 3]
                                           ↓
                                    Redis Pub/Sub ←→ Reverb WebSocket
                                           ↓
                                    MySQL Database
```

Sistem menggunakan **Redis pub/sub** untuk komunikasi antar-node dan **Laravel Reverb** untuk WebSocket connections, memungkinkan sinkronisasi real-time di seluruh nodes dengan eventual consistency model.

---

## ⚡ Mulai Cepat (Docker)

Jalankan sistem terdistribusi dalam 3 perintah:

```bash
# 1. Clone dan masuk ke direktori
git clone <repository-url> && cd laravel-reverb-absensi

# 2. Jalankan script deployment otomatis
# Windows:
docker\deploy.bat

# Linux/Mac:
chmod +x docker/deploy.sh && ./docker/deploy.sh

# Atau manual:
docker-compose up -d --build
docker-compose exec app-node-1 php artisan migrate --force
docker-compose exec app-node-1 php artisan db:seed --class=ResetDatabaseSeeder --force
```

**Akses aplikasi:** http://localhost

**Kredensial login:**
- Admin: `admin@absensi.com` / `password`
- Karyawan: `andi.wijaya@absensi.com` / `password`

---

## 🚀 Fitur Utama

### Untuk Karyawan:
- ✅ **Absensi Masuk/Pulang** dengan geolocation
- ✅ **Dashboard Portal Karyawan** dengan status real-time
- ✅ **Riwayat Absensi** pribadi dengan filter tanggal
- ✅ **Export Data** absensi ke CSV

### Untuk Admin:
- ✅ **Dashboard Real-Time** dengan metrics dan live updates
- ✅ **Kelola Karyawan** (CRUD: Create, Read, Update, Delete)
- ✅ **Reset Password** karyawan
- ✅ **Riwayat Absensi** semua karyawan dengan search & filter
- ✅ **Export Data** semua absensi ke CSV
- ✅ **Monitor Status Server** dan koneksi WebSocket

### Teknologi & Arsitektur:
- ✅ **Distributed System** dengan 3 Laravel application nodes
- ✅ **Load Balancing** menggunakan Nginx (round-robin)
- ✅ **Real-Time Updates** menggunakan Laravel Reverb & WebSocket
- ✅ **Redis Pub/Sub** untuk inter-node communication
- ✅ **High Availability** dengan automatic failover
- ✅ **Eventual Consistency** model untuk data synchronization
- ✅ **Alpine.js** untuk interaktivitas frontend
- ✅ **Tailwind CSS** untuk styling modern
- ✅ **Geolocation API** untuk tracking lokasi
- ✅ **Docker Compose** untuk container orchestration

## 📋 Kebutuhan Sistem

### Untuk Development Lokal (Single Node):
- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL 8.0+ atau SQLite
- Redis 7.0+

### Untuk Deployment Docker (Distributed System):
- Docker 20.10+
- Docker Compose 2.0+
- RAM 4GB+ (direkomendasikan)
- Disk space 10GB+

## 🛠️ Instalasi

### Opsi A: Deployment Docker (Sistem Terdistribusi)

Ini adalah **pendekatan yang direkomendasikan** untuk merasakan arsitektur distributed system lengkap dengan multiple nodes, load balancing, dan Redis pub/sub.

#### 1. Clone Repository
>>>>>>> dev
```bash
git clone <repository-url>
cd laravel-reverb-absensi
```

<<<<<<< HEAD
2. Install dependencies:
=======
#### 2. Build dan Jalankan Semua Services

**Cara Otomatis (Recommended):**
```bash
# Windows
docker\deploy.bat

# Linux/Mac
chmod +x docker/deploy.sh
./docker/deploy.sh
```

**Cara Manual:**
```bash
# Build Docker images dan jalankan semua containers
docker-compose up -d --build
```

Perintah tunggal ini akan menjalankan:
- **3 Laravel Application Nodes** (app-node-1, app-node-2, app-node-3)
- **3 Redis Subscriber Processes** (satu untuk setiap node)
- **1 Laravel Reverb WebSocket Server**
- **1 Nginx Load Balancer**
- **1 MySQL Database**
- **1 Redis Server**

#### 3. Inisialisasi Database
```bash
# Jalankan migrations dan seeders
docker-compose exec app-node-1 php artisan migrate --force
docker-compose exec app-node-1 php artisan db:seed --class=ResetDatabaseSeeder --force
```

#### 4. Akses Aplikasi
- **URL Aplikasi:** http://localhost
- **WebSocket Server:** ws://localhost:8080

Nginx load balancer akan secara otomatis mendistribusikan request ke 3 application nodes.

---

### Opsi B: Development Lokal (Single Node)

Untuk development lokal tanpa Docker, ikuti langkah-langkah berikut:

#### 1. Clone Repository
```bash
git clone <repository-url>
cd laravel-reverb-absensi
```

#### 2. Install Dependencies
>>>>>>> dev
```bash
composer install
npm install
```

<<<<<<< HEAD
3. Setup environment:
```bash
copy .env.example .env
php artisan key:generate
```

4. Setup database:
```bash
php artisan migrate
```

5. Link storage:
```bash
php artisan storage:link
```

## Development

Jalankan development server dengan 3 terminal terpisah:
=======
#### 3. Konfigurasi Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```env
APP_NAME="Sistem Absensi Real-Time"
APP_URL=http://localhost:8000

# Gunakan SQLite untuk development tanpa konfigurasi
DB_CONNECTION=sqlite
# Atau gunakan MySQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=absensi
# DB_USERNAME=root
# DB_PASSWORD=

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

REVERB_APP_ID=123456
REVERB_APP_KEY=reverb-key
REVERB_APP_SECRET=reverb-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

#### 4. Setup Database
```bash
# Buat file database SQLite
touch database/database.sqlite

# Jalankan migrations dan seeders
php artisan migrate
php artisan db:seed --class=ResetDatabaseSeeder
```

#### 5. Build Assets
```bash
npm run build
```

## 🚀 Menjalankan Aplikasi

### Mode Docker (Sistem Terdistribusi)

#### Jalankan Semua Services
```bash
docker-compose up -d
```

#### Lihat Logs
```bash
# Lihat semua logs
docker-compose logs -f

# Lihat logs service tertentu
docker-compose logs -f app-node-1
docker-compose logs -f nginx
docker-compose logs -f reverb
docker-compose logs -f subscriber-node-1
```

#### Hentikan Semua Services
```bash
docker-compose down
```

#### Restart Services
```bash
# Restart semua services
docker-compose restart

# Restart service tertentu
docker-compose restart app-node-1
```

#### Cek Status Services
```bash
docker-compose ps
```

#### Jalankan Perintah di Container
```bash
# Jalankan perintah artisan
docker-compose exec app-node-1 php artisan cache:clear
docker-compose exec app-node-1 php artisan config:clear

# Akses shell container
docker-compose exec app-node-1 sh
```

---

### Mode Development Lokal (Single Node)

Anda perlu menjalankan 4 services:
>>>>>>> dev

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```

<<<<<<< HEAD
**Terminal 2 - Vite Dev Server:**
```bash
npm run dev
```

**Terminal 3 - Laravel Reverb (WebSocket):**
=======
**Terminal 2 - Laravel Reverb (WebSocket):**
>>>>>>> dev
```bash
php artisan reverb:start
```

<<<<<<< HEAD
Atau gunakan script composer untuk menjalankan semuanya sekaligus:
```bash
composer dev
```

Aplikasi akan berjalan di `http://localhost:8000`

## Build untuk Production

```bash
npm run build
php artisan optimize
```

## Testing

```bash
composer test
```

atau

```bash
php artisan test
```

## Code Quality

Format code dengan Laravel Pint:
```bash
./vendor/bin/pint
```

## License

MIT License
=======
**Terminal 3 - Queue Worker:**
```bash
php artisan queue:work
```

**Terminal 4 - Redis Subscriber (for pub/sub):**
```bash
php artisan redis:subscribe
```

Akses aplikasi di: `http://localhost:8000`

## 🏗️ Arsitektur Sistem Terdistribusi

### Gambaran Umum

Sistem ini mengimplementasikan arsitektur terdistribusi dengan komponen-komponen berikut:

```
┌─────────────────────────────────────────────────────────────────┐
│                         Client Browser                           │
│                    (Blade + Alpine.js)                          │
└────────────┬────────────────────────────────────┬───────────────┘
             │                                    │
             │ HTTP/HTTPS                         │ WebSocket
             │                                    │
┌────────────▼────────────────────────────────────▼───────────────┐
│                      Nginx Load Balancer                         │
│                    (Round-robin distribution)                    │
└────┬──────────────┬──────────────┬─────────────────────────────┘
     │              │              │
     │              │              │
┌────▼─────┐  ┌────▼─────┐  ┌────▼─────┐      ┌──────────────┐
│ Laravel  │  │ Laravel  │  │ Laravel  │      │   Laravel    │
│ Node 1   │  │ Node 2   │  │ Node 3   │      │   Reverb     │
│ (App)    │  │ (App)    │  │ (App)    │      │ (WebSocket)  │
└────┬─────┘  └────┬─────┘  └────┬─────┘      └──────┬───────┘
     │              │              │                   │
     └──────────────┴──────────────┴───────────────────┘
                    │                                  │
     ┌──────────────▼──────────────┐    ┌─────────────▼────────┐
     │      Redis (Pub/Sub)        │    │   MySQL Database     │
     │   (Cache + Queue + Broker)  │    │  (Shared Storage)    │
     └─────────────────────────────┘    └──────────────────────┘
```

### Komponen-Komponen

#### 1. **Nginx Load Balancer**
- Mendistribusikan HTTP request ke 3 Laravel nodes menggunakan algoritma **least_conn**
- Mem-proxy koneksi WebSocket ke Reverb server
- Melakukan health check setiap 10 detik
- Secara otomatis menghapus node yang tidak sehat dari pool
- Menangani serving file statis dan caching

#### 2. **Laravel Application Nodes (3 instance)**
- **Desain stateless** - tidak ada data session yang disimpan di memory
- Setiap node memiliki `APP_NODE_ID` unik untuk tracking
- Menangani HTTP requests (login, absensi, dashboard, API)
- Mempublikasikan events ke Redis pub/sub channel
- Berbagi MySQL database yang sama
- Kegagalan independen - jika satu node gagal, node lain tetap melayani

#### 3. **Redis Subscribers (3 instance)**
- Satu subscriber process per application node
- Mendengarkan Redis pub/sub channel `absensi-channel`
- Menerima events dari semua nodes
- Mem-broadcast events yang diterima ke Reverb WebSocket server
- Memungkinkan sinkronisasi real-time di seluruh nodes

#### 4. **Laravel Reverb (WebSocket Server)**
- Server WebSocket khusus untuk komunikasi real-time
- Mempertahankan koneksi persisten dengan browser clients
- Menerima broadcast events dari semua nodes
- Mendorong updates ke semua clients yang terhubung
- Terpisah dari app nodes untuk skalabilitas yang lebih baik

#### 5. **Redis Server**
- **Pub/Sub**: Message broker untuk komunikasi antar-node
- **Cache**: Menyimpan data yang sering diakses (sessions, config)
- **Queue**: Pemrosesan background job

#### 6. **MySQL Database**
- Sumber kebenaran tunggal untuk semua data
- Dibagikan oleh semua Laravel nodes
- Kepatuhan ACID memastikan integritas data
- Menangani penulisan konkuren dari multiple nodes

### Implementasi Redis Pub/Sub

Sistem menggunakan Redis pub/sub untuk distribusi event real-time di seluruh nodes:

#### Alur Event:
1. **User melakukan absensi** di Node 1
2. **Node 1 menyimpan ke database** dan mempublikasikan event ke Redis channel `absensi-channel`
3. **Semua Redis subscribers** (Node 1, 2, 3) menerima event
4. **Setiap subscriber mem-broadcast** event ke Reverb WebSocket server
5. **Reverb mendorong** event ke semua browser clients yang terhubung
6. **Clients mengupdate UI** secara real-time tanpa refresh halaman

#### Struktur Event:
```json
{
  "event": "AbsensiCreated",
  "data": {
    "id": 123,
    "user_id": 45,
    "user_name": "Ahmad Rizki",
    "type": "in",
    "timestamp": "2025-11-15 08:15:00",
    "latitude": -6.2088,
    "longitude": 106.8456,
    "node_id": "app-node-1",
    "status": "Hadir"
  }
}
```

#### Keuntungan Utama:
- ✅ **Arsitektur terpisah** - nodes tidak berkomunikasi langsung
- ✅ **Dapat diskalakan** - mudah menambahkan lebih banyak nodes
- ✅ **Toleran terhadap kesalahan** - jika satu subscriber gagal, yang lain tetap berjalan
- ✅ **Konsistensi eventual** - semua nodes menerima updates dalam 1-2 detik

### Fitur Ketersediaan Tinggi

#### 1. **Failover Otomatis**
- Jika satu node crash, Nginx secara otomatis mengarahkan traffic ke nodes yang sehat
- Health checks mendeteksi kegagalan dalam 10 detik
- Tidak memerlukan intervensi manual

#### 2. **Degradasi Bertahap**
- Jika Redis gagal: absensi tetap berfungsi, tapi tidak ada update real-time
- Jika Reverb gagal: absensi tetap berfungsi, tapi memerlukan refresh halaman manual
- Jika MySQL gagal: sistem menolak request baru dengan pesan error

#### 3. **Distribusi Beban**
- Nginx menggunakan algoritma `least_conn` untuk distribusi beban optimal
- Setiap node dapat menangani requests secara independen
- Tidak ada single point of failure di application layer

### Konsistensi Data

#### Model Eventual Consistency:
- Semua nodes berbagi MySQL database yang sama
- Database memastikan properti ACID untuk penulisan
- Redis pub/sub mendistribusikan events ke semua nodes
- Delay sinkronisasi maksimum: **1-2 detik**

#### Resolusi Konflik:
- Strategi **First-Write-Wins** berdasarkan timestamp
- Database unique constraints mencegah duplikasi absensi
- Optimistic locking untuk concurrent updates

### Monitoring & Observability

#### Health Checks:
```bash
# Cek kesehatan Nginx
curl http://localhost/nginx-health

# Cek kesehatan application node
docker-compose exec app-node-1 php artisan health:check

# Cek status semua services
docker-compose ps
```

#### Logs:
```bash
# Logs aplikasi
docker-compose logs -f app-node-1

# Logs akses Nginx
docker-compose logs -f nginx

# Logs Redis subscriber
docker-compose logs -f subscriber-node-1
```

#### Metrics di Admin Dashboard:
- Total servers online/offline
- Timestamp sinkronisasi terakhir per node
- Jumlah absensi real-time
- Tingkat kehadiran

---

## 👤 Default Accounts

### Admin
- **Email:** admin@absensi.com
- **Password:** password

### Karyawan (10 users)
- **Email:** andi.wijaya@absensi.com
- **Email:** bella.safira@absensi.com
- **Email:** citra.dewi@absensi.com
- **Email:** doni.pratama@absensi.com
- **Email:** eka.putri@absensi.com
- **Email:** fajar.ramadhan@absensi.com
- **Email:** gita.maharani@absensi.com
- **Email:** hendra.gunawan@absensi.com
- **Email:** indah.permata@absensi.com
- **Email:** joko.susilo@absensi.com
- **Password:** password (untuk semua)

## 📱 Cara Penggunaan

### Untuk Karyawan:

1. **Login** dengan email dan password
2. **Dashboard** akan menampilkan status absensi hari ini
3. **Absen Masuk:**
   - Klik tombol "Absen Masuk"
   - Browser akan meminta izin akses lokasi
   - Klik "Allow" untuk melanjutkan
   - Data absensi akan tersimpan dengan timestamp dan lokasi
4. **Absen Pulang:**
   - Klik tombol "Absen Pulang" (aktif setelah absen masuk)
   - Durasi kerja akan dihitung otomatis
5. **Lihat Riwayat:**
   - Menu "Riwayat" untuk melihat history absensi
   - Filter berdasarkan tanggal
   - Export ke CSV

### Untuk Admin:

1. **Login** sebagai admin
2. **Dashboard:**
   - Lihat metrics real-time (absensi hari ini, minggu ini, dll)
   - Monitor "Absensi Terbaru" yang update secara real-time
   - Cek status koneksi Reverb (badge "Live • Connected")
3. **Kelola Karyawan:**
   - Tambah karyawan baru
   - Edit data karyawan
   - Reset password karyawan
   - Hapus karyawan
4. **Riwayat Absensi:**
   - Lihat semua absensi karyawan
   - Search berdasarkan nama
   - Filter berdasarkan tanggal
   - Export ke CSV

## 🔧 Troubleshooting

### Masalah Docker Environment

#### Services tidak mau start:
```bash
# Cek apakah port sudah digunakan
netstat -ano | findstr :80
netstat -ano | findstr :3306
netstat -ano | findstr :6379

# Hentikan semua containers dan hapus volumes
docker-compose down -v

# Rebuild dan restart
docker-compose up -d --build --force-recreate
```

#### Error koneksi database:
```bash
# Tunggu MySQL sampai siap sepenuhnya (memakan waktu ~30 detik pada start pertama)
docker-compose logs mysql

# Cek kesehatan MySQL
docker-compose exec mysql mysqladmin ping -h localhost -u root -proot_secret

# Buat ulang database
docker-compose down -v
docker-compose up -d
docker-compose exec app-node-1 php artisan migrate --force
docker-compose exec app-node-1 php artisan db:seed --class=ResetDatabaseSeeder --force
```

#### Application nodes tidak merespons:
```bash
# Cek kesehatan node
docker-compose exec app-node-1 php artisan health:check

# Lihat logs node
docker-compose logs -f app-node-1

# Restart node tertentu
docker-compose restart app-node-1

# Cek status Nginx upstream
docker-compose logs nginx | grep "upstream"
```

#### Redis pub/sub tidak berfungsi:
```bash
# Cek koneksi Redis
docker-compose exec redis redis-cli ping

# Cek proses subscriber
docker-compose ps | grep subscriber

# Lihat logs subscriber
docker-compose logs -f subscriber-node-1

# Restart subscribers
docker-compose restart subscriber-node-1 subscriber-node-2 subscriber-node-3
```

#### WebSocket tidak terkoneksi:
```bash
# Cek Reverb sedang berjalan
docker-compose ps reverb

# Lihat logs Reverb
docker-compose logs -f reverb

# Test koneksi WebSocket
curl -i -N -H "Connection: Upgrade" -H "Upgrade: websocket" http://localhost:8080/app

# Restart Reverb
docker-compose restart reverb
```

#### Load balancer tidak mendistribusikan requests:
```bash
# Cek konfigurasi Nginx
docker-compose exec nginx nginx -t

# Lihat logs Nginx
docker-compose logs -f nginx

# Test node mana yang menangani request (cek X-Node-ID header)
curl -I http://localhost

# Restart Nginx
docker-compose restart nginx
```

#### Error kehabisan memory:
```bash
# Cek penggunaan resource Docker
docker stats

# Tingkatkan batas memory Docker di pengaturan Docker Desktop
# Direkomendasikan: 4GB+ RAM

# Hentikan containers yang tidak digunakan
docker-compose down
```

#### Error permission:
```bash
# Perbaiki permission storage
docker-compose exec app-node-1 chmod -R 775 storage bootstrap/cache
docker-compose exec app-node-1 chown -R www-data:www-data storage bootstrap/cache
```

---

### Masalah Development Lokal

#### WebSocket tidak connect:
```bash
# Pastikan Reverb running
php artisan reverb:start

# Check port 8080 tidak digunakan aplikasi lain
netstat -ano | findstr :8080

# Check Redis connection
redis-cli ping
```

#### Real-time tidak update:
```bash
# Pastikan Redis subscriber running
php artisan redis:subscribe

# Restart queue worker
php artisan queue:restart
php artisan queue:work

# Clear cache
php artisan cache:clear
php artisan config:clear
```

#### Geolocation tidak bekerja:
- Pastikan menggunakan HTTPS atau localhost
- Browser harus support Geolocation API
- User harus memberikan permission
- Check browser console for errors

#### Database error:
```bash
# Reset database
php artisan migrate:fresh --seed

# Or for SQLite
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate --seed
```

#### Koneksi Redis gagal:
```bash
# Cek Redis sedang berjalan
redis-cli ping

# Jalankan Redis (Windows dengan WSL)
sudo service redis-server start

# Jalankan Redis (macOS dengan Homebrew)
brew services start redis

# Cek konfigurasi Redis di .env
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

---

### Testing Sistem Terdistribusi

#### Test load balancing:
```bash
# Buat multiple requests dan cek node mana yang menanganinya
for i in {1..10}; do curl -s http://localhost | grep "node"; done

# Atau cek response headers
for i in {1..10}; do curl -I http://localhost 2>&1 | grep "X-Node"; done
```

#### Test failover:
```bash
# Hentikan satu node
docker-compose stop app-node-1

# Aplikasi seharusnya tetap berfungsi via node-2 dan node-3
curl http://localhost

# Cek logs Nginx untuk perubahan upstream
docker-compose logs nginx | tail -20

# Restart node tersebut
docker-compose start app-node-1
```

#### Test sinkronisasi real-time antar nodes:
1. Buka browser tab 1: http://localhost (mungkin mengenai node-1)
2. Buka browser tab 2: http://localhost (mungkin mengenai node-2)
3. Login sebagai karyawan di tab 1
4. Lakukan absensi di tab 1
5. Login sebagai admin di tab 2
6. Verifikasi dashboard admin terupdate secara real-time di tab 2

#### Monitor Redis pub/sub:
```bash
# Subscribe ke Redis channel untuk melihat events
docker-compose exec redis redis-cli
> SUBSCRIBE absensi-channel

# Di terminal lain, lakukan absensi
# Anda akan melihat events yang dipublikasikan ke channel
```

## 📊 Database Schema

### Users Table
```sql
- id: BIGINT (Primary Key)
- name: VARCHAR(255)
- email: VARCHAR(255) UNIQUE
- password: VARCHAR(255)
- role: ENUM('admin', 'karyawan')
- created_at, updated_at: TIMESTAMP
```

### Attendances Table
```sql
- id: BIGINT (Primary Key)
- user_id: BIGINT (Foreign Key → users.id)
- date: DATE
- jam_masuk: TIMESTAMP
- jam_pulang: TIMESTAMP (nullable)
- latitude_masuk: DECIMAL(10,8)
- longitude_masuk: DECIMAL(11,8)
- latitude_pulang: DECIMAL(10,8) (nullable)
- longitude_pulang: DECIMAL(11,8) (nullable)
- duration_minutes: INT (nullable)
- status: ENUM('Hadir', 'Terlambat', 'Alpha')
- node_id: VARCHAR(50) - tracks which node processed the request
- created_at, updated_at: TIMESTAMP
- UNIQUE KEY: (user_id, date) - prevents duplicate absensi
```

### Attendance Logs Table (Event Sourcing)
```sql
- id: BIGINT (Primary Key)
- attendance_id: BIGINT (Foreign Key → attendances.id)
- user_id: BIGINT (Foreign Key → users.id)
- event_type: VARCHAR(50) - 'clock_in', 'clock_out', 'update'
- node_id: VARCHAR(50) - which node generated the event
- payload: JSON - complete event data
- created_at: TIMESTAMP
```

Field `node_id` di tabel attendances memungkinkan tracking node aplikasi mana yang memproses setiap request absensi, berguna untuk debugging dan monitoring distribusi beban.

## 🔐 Security Features

- ✅ CSRF Protection
- ✅ Rate Limiting (10 requests/minute untuk absensi)
- ✅ Password Hashing (Bcrypt)
- ✅ Role-Based Authorization
- ✅ Policy-Based Access Control
- ✅ Input Validation
- ✅ SQL Injection Prevention (Eloquent ORM)

## � Setruktur Folder Docker

Semua file terkait Docker ada di folder `docker/`:

```
docker/
├── .env.docker      # Template environment variables untuk Docker
├── deploy.sh        # Script deployment otomatis (Linux/Mac)
├── deploy.bat       # Script deployment otomatis (Windows)
└── README.md        # Panduan lengkap Docker deployment
```

Untuk panduan lengkap Docker, lihat: **[docker/README.md](docker/README.md)**

---

## 🐳 Referensi Perintah Docker Compose

### Perintah Dasar
```bash
# Start all services in background
docker-compose up -d

# Start with build (after code changes)
docker-compose up -d --build

# Stop all services
docker-compose down

# Stop and remove volumes (clean slate)
docker-compose down -v

# View all running services
docker-compose ps

# View logs (all services)
docker-compose logs -f

# View logs (specific service)
docker-compose logs -f app-node-1
docker-compose logs -f nginx
docker-compose logs -f reverb
```

```bash
# Jalankan semua services di background
docker-compose up -d

# Jalankan dengan build (setelah perubahan kode)
docker-compose up -d --build

# Hentikan semua services
docker-compose down

# Hentikan dan hapus volumes (mulai dari awal)
docker-compose down -v

# Lihat semua services yang berjalan
docker-compose ps

# Lihat logs (semua services)
docker-compose logs -f

# Lihat logs (service tertentu)
docker-compose logs -f app-node-1
docker-compose logs -f nginx
docker-compose logs -f reverb
```

### Manajemen Services
```bash
# Restart semua services
docker-compose restart

# Restart service tertentu
docker-compose restart app-node-1

# Hentikan service tertentu
docker-compose stop app-node-1

# Jalankan service tertentu
docker-compose start app-node-1

# Scale application nodes (tambah lebih banyak nodes)
docker-compose up -d --scale app-node-1=2
```

### Perintah Maintenance
```bash
# Jalankan perintah artisan
docker-compose exec app-node-1 php artisan cache:clear
docker-compose exec app-node-1 php artisan config:clear
docker-compose exec app-node-1 php artisan migrate
docker-compose exec app-node-1 php artisan db:seed

# Akses shell container
docker-compose exec app-node-1 sh
docker-compose exec mysql bash

# Akses MySQL database
docker-compose exec mysql mysql -u absensi -psecret absensi

# Akses Redis CLI
docker-compose exec redis redis-cli

# Lihat penggunaan resource
docker stats
```

### Perintah Debugging
```bash
# Cek kesehatan service
docker-compose exec app-node-1 php artisan health:check

# Test koneksi database
docker-compose exec app-node-1 php artisan tinker
>>> DB::connection()->getPdo();

# Test koneksi Redis
docker-compose exec redis redis-cli ping

# Cek konfigurasi Nginx
docker-compose exec nginx nginx -t

# Ikuti logs secara real-time
docker-compose logs -f --tail=100
```

---

## 🎨 Tech Stack

**Backend:**
- Laravel 12
- Laravel Reverb (WebSocket)
- Redis (Pub/Sub, Cache, Queue)
- MySQL 8.0

**Frontend:**
- Alpine.js
- Tailwind CSS
- Laravel Echo
- Vite

**Infrastructure:**
- Docker & Docker Compose
- Nginx (Load Balancer)
- PHP-FPM

## 📝 API Endpoints

### Authentication
- `POST /login` - Login
- `POST /logout` - Logout

### Absensi (Karyawan)
- `POST /absensi/masuk` - Clock in
- `POST /absensi/pulang` - Clock out
- `GET /absensi/status` - Get today's status

### Dashboard (Admin)
- `GET /api/dashboard/metrics` - Get metrics
- `GET /api/dashboard/latest` - Get latest attendances

### Riwayat
- `GET /admin/riwayat` - Admin view all
- `GET /karyawan/riwayat` - Karyawan view own
- `GET /riwayat/export` - Export to CSV

### User Management (Admin)
- `GET /admin/users` - List karyawan
- `POST /admin/users` - Create karyawan
- `PUT /admin/users/{id}` - Update karyawan
- `DELETE /admin/users/{id}` - Delete karyawan
- `POST /admin/users/{id}/reset-password` - Reset password

## 🤝 Kontribusi

Kontribusi sangat diterima! Silakan submit Pull Request.

## 📄 Lisensi

Proyek ini adalah software open-source dengan lisensi MIT.

## � Deploymoent Modes

### Development (Lokal)
- Single Laravel node
- SQLite database (tanpa konfigurasi)
- Redis dan Reverb lokal
- Terbaik untuk: Pengembangan fitur dan testing

### Distributed (Docker)
- 3 Laravel application nodes
- Nginx load balancer
- MySQL database (shared)
- Redis pub/sub untuk komunikasi antar-node
- Reverb WebSocket server terpisah
- Terbaik untuk: Production, high availability, demonstrasi distributed systems

### Pertimbangan Production
- Gunakan file `.env` spesifik untuk environment
- Aktifkan HTTPS/SSL untuk koneksi WebSocket
- Konfigurasi firewall rules yang tepat
- Setup monitoring dan alerting
- Gunakan Docker secrets untuk data sensitif
- Konfigurasi log rotation
- Setup automated backups untuk MySQL
- Gunakan Redis persistence (AOF atau RDB)

---

## 📚 Sumber Belajar

Proyek ini mendemonstrasikan beberapa konsep distributed system:

- **Load Balancing**: Nginx mendistribusikan traffic ke multiple nodes
- **Horizontal Scaling**: Tambahkan lebih banyak nodes untuk menangani beban yang meningkat
- **High Availability**: Sistem tetap berfungsi meskipun ada nodes yang gagal
- **Eventual Consistency**: Data tersinkronisasi di seluruh nodes dalam hitungan detik
- **Event-Driven Architecture**: Redis pub/sub untuk komunikasi yang terpisah
- **Stateless Design**: Nodes tidak menyimpan state session
- **Health Checks**: Deteksi otomatis dan penghapusan nodes yang tidak sehat
- **Fault Tolerance**: Degradasi bertahap ketika komponen gagal

---

## 👨‍💻 Developer

Dikembangkan dengan ❤️ menggunakan Laravel 12, Laravel Reverb, dan Docker

**Arsitektur**: Sistem Terdistribusi dengan Load Balancing dan Sinkronisasi Real-Time
>>>>>>> dev
