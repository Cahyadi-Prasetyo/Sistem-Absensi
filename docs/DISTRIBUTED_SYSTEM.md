# Sistem Absensi Terdistribusi dengan Real-Time Sync

> Panduan lengkap untuk setup, testing, dan memahami arsitektur distributed system

## Table of Contents
- [Arsitektur Sistem](#arsitektur-sistem)
- [Quick Start](#quick-start)
- [Setup Multi-Node](#setup-multi-node)
- [Testing](#testing-sistem-terdistribusi)
- [Monolith vs Distributed](#monolith-vs-distributed)
- [Monitoring & Debugging](#monitoring--debugging)
- [Troubleshooting](#troubleshooting)

---

## Arsitektur Sistem

Sistem ini dirancang untuk berjalan di beberapa node server dengan sinkronisasi data real-time menggunakan:

1. **Database Terpusat** - Semua node terhubung ke database MySQL yang sama
2. **Redis** - Untuk queue dan cache terdistribusi
3. **Laravel Reverb** - WebSocket server untuk broadcasting real-time
4. **Event Broadcasting** - Setiap perubahan data di-broadcast ke semua client

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   Node 1    │     │   Node 2    │     │   Node 3    │
│  (Server)   │     │  (Server)   │     │  (Server)   │
└──────┬──────┘     └──────┬──────┘     └──────┬──────┘
       │                   │                   │
       └───────────────────┼───────────────────┘
                           │
              ┌────────────┴────────────┐
              │                         │
       ┌──────▼──────┐         ┌───────▼────────┐
       │   MySQL     │         │  Redis + Queue │
       │  (Shared)   │         │    (Shared)    │
       └─────────────┘         └────────────────┘
                           │
              ┌────────────┴────────────┐
              │                         │
       ┌──────▼──────┐         ┌───────▼────────┐
       │   Reverb    │         │   Reverb       │
       │  WebSocket  │         │  WebSocket     │
       │   (Node 1)  │         │   (Node 2)     │
       └─────────────┘         └────────────────┘
```

## Komponen Utama

### 1. Event Broadcasting

Setiap kali ada absensi baru atau update, event akan di-broadcast:

- **AttendanceCreated** - Ketika user check-in
- **AttendanceUpdated** - Ketika user check-out

Event ini akan dikirim ke:
- Channel publik `attendances` - Untuk admin/dashboard
- Channel private `user.{id}` - Untuk user yang bersangkutan

### 2. Node Identification

Setiap server memiliki `NODE_ID` unik yang disimpan di `.env`:
```env
NODE_ID=1  # Node 1
NODE_ID=2  # Node 2
NODE_ID=3  # Node 3
```

Node ID ini disimpan di field `node_id` pada tabel `attendances` untuk tracking.

### 3. Real-Time Sync Flow

```
User Check-in di Node 1
    ↓
Data disimpan ke MySQL (dengan node_id=1)
    ↓
Event AttendanceCreated di-dispatch
    ↓
Event masuk ke Redis Queue
    ↓
Queue worker broadcast via Reverb
    ↓
Semua client (Node 1, 2, 3) menerima update via WebSocket
    ↓
UI otomatis update tanpa refresh
```

## Docker Services

### Service Overview

| Service | Container | Port | Description |
|---------|-----------|------|-------------|
| Nginx | nginx | 80, 443 | Load balancer & reverse proxy |
| App 1 | app_1 | 9000 | Laravel instance #1 (NODE_ID=1) |
| App 2 | app_2 | 9000 | Laravel instance #2 (NODE_ID=2) |
| App 3 | app_3 | 9000 | Laravel instance #3 (NODE_ID=3) |
| MySQL | mysql | 3306 | Shared database |
| Redis | redis | 6379 | Shared cache & queue |
| Reverb | reverb | 8080 | WebSocket server |
| Queue | queue | - | Background job processor |

### Environment Configuration

File `.env.docker` digunakan oleh semua container:

```env
# Application
APP_NAME="Laravel Absensi"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database (shared)
DB_CONNECTION=mysql
DB_HOST=mysql  # Docker service name
DB_PORT=3306
DB_DATABASE=laravel_absensi
DB_USERNAME=root
DB_PASSWORD=secret

# Redis (shared)
REDIS_HOST=redis  # Docker service name
REDIS_PORT=6379
QUEUE_CONNECTION=redis

# Broadcasting
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=app1
REVERB_APP_KEY=your_reverb_key
REVERB_APP_SECRET=your_reverb_secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### Node Identification

Setiap container app memiliki NODE_ID unik yang di-set via docker-compose.yml:

```yaml
services:
  app_1:
    environment:
      - NODE_ID=1
  
  app_2:
    environment:
      - NODE_ID=2
  
  app_3:
    environment:
      - NODE_ID=3
```

### Docker Commands

```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# Restart specific service
docker-compose restart app_1

# View logs
docker-compose logs -f app_1

# Execute command in container
docker exec laravel_absensi_app_1 php artisan migrate

# Access container shell
docker exec -it laravel_absensi_app_1 bash
```

## Testing Sistem Terdistribusi

### Test 1: Load Balancing
1. Buka browser ke `http://localhost`
2. Refresh halaman beberapa kali
3. Cek logs untuk melihat request didistribusikan ke node berbeda:
```bash
docker-compose logs -f app_1 app_2 app_3
```

### Test 2: Real-Time Sync Antar Node
1. Buka 3 tab browser ke `http://localhost/admin/dashboard`
2. Login sebagai admin di semua tab
3. Di tab ke-4, buka `http://localhost/attendances` dan check-in
4. ✅ Semua dashboard (tab 1, 2, 3) harus update otomatis tanpa refresh
5. Cek node_id di database untuk melihat node mana yang memproses

### Test 3: Cross-Node Check-in/Check-out
1. User A check-in (akan diproses oleh salah satu node via load balancer)
2. Refresh beberapa kali, lalu check-out (mungkin diproses node berbeda)
3. ✅ Data harus konsisten karena semua node share database yang sama

### Test 4: Node Failure Recovery
1. Stop salah satu node:
```bash
docker-compose stop app_2
```
2. Akses `http://localhost` - masih bisa diakses (load balancer route ke node lain)
3. Check-in masih berfungsi normal
4. Start kembali node yang di-stop:
```bash
docker-compose start app_2
```
5. ✅ Sistem kembali normal dengan 3 node aktif

### Test 5: WebSocket Connection
1. Buka browser console (F12)
2. Akses dashboard
3. Jalankan:
```javascript
// Cek status koneksi
window.Echo.connector.pusher.connection.state
// Expected: "connected"

// Cek channel
window.Echo.connector.channels
```
4. ✅ WebSocket harus connected ke Reverb server

## Monitoring & Debugging

### Docker Service Status
```bash
# Cek status semua services
docker-compose ps

# Expected output:
# NAME                    STATUS
# nginx                   Up
# app_1                   Up
# app_2                   Up
# app_3                   Up
# mysql                   Up (healthy)
# redis                   Up (healthy)
# reverb                  Up
# queue                   Up
```

### View Logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app_1

# Multiple services
docker-compose logs -f app_1 app_2 app_3

# Last 100 lines
docker-compose logs --tail=100 app_1
```

### Cek Load Balancing
```bash
# Monitor Nginx access logs
docker-compose logs -f nginx

# Lihat request didistribusikan ke node mana
# Output akan menunjukkan upstream (app_1, app_2, atau app_3)
```

### Cek Database Connection
```bash
# Access MySQL
docker exec -it laravel_absensi_mysql mysql -u root -p

# Show databases
SHOW DATABASES;

# Check attendances table
USE laravel_absensi;
SELECT id, user_id, node_id, check_in FROM attendances ORDER BY id DESC LIMIT 10;
```

### Cek Redis Queue
```bash
# Access Redis CLI
docker exec -it laravel_absensi_redis redis-cli

# Check queue length
LLEN queues:default

# Monitor commands
MONITOR
```

### Cek WebSocket Connection
```bash
# Di browser console
window.Echo.connector.pusher.connection.state
// Expected: "connected"

# Cek Reverb logs
docker-compose logs -f reverb
```

## Troubleshooting

### Service tidak start
```bash
# Cek status
docker-compose ps

# Cek logs untuk error
docker-compose logs app_1

# Restart service
docker-compose restart app_1

# Rebuild jika ada perubahan Dockerfile
docker-compose up -d --build
```

### WebSocket tidak connect
```bash
# Cek Reverb service
docker-compose ps reverb

# Cek logs
docker-compose logs reverb

# Restart Reverb
docker-compose restart reverb

# Test dari browser console
window.Echo.connector.pusher.connection.state
```

### Data tidak sync
```bash
# Cek queue worker
docker-compose ps queue
docker-compose logs queue

# Cek Redis
docker exec laravel_absensi_redis redis-cli ping

# Restart queue worker
docker-compose restart queue

# Clear cache
docker exec laravel_absensi_app_1 php artisan cache:clear
docker exec laravel_absensi_app_1 php artisan config:clear
```

### Database connection error
```bash
# Cek MySQL status
docker-compose ps mysql

# Wait for healthy status
docker-compose ps | grep mysql

# Test connection
docker exec laravel_absensi_app_1 php artisan migrate:status

# Restart MySQL
docker-compose restart mysql
```

### Port already in use
```bash
# Windows - Find process using port 80
netstat -ano | findstr :80

# Kill process
taskkill /PID <PID> /F

# Or change port in docker-compose.yml
ports:
  - "8080:80"  # Use port 8080 instead
```

## Keamanan

1. **HTTPS/WSS** - Untuk production, gunakan SSL:
```env
REVERB_SCHEME=https
```

2. **Private Channels** - Sudah diimplementasi untuk user-specific data

3. **Database Security** - Gunakan user database dengan privilege terbatas

4. **Firewall** - Batasi akses ke Redis dan MySQL hanya dari node yang authorized

## Scaling

### Horizontal Scaling
- Tambah node baru dengan NODE_ID unik
- Semua node share database & Redis yang sama
- Load balancer di depan untuk distribute traffic

### Vertical Scaling
- Tingkatkan resource MySQL & Redis server
- Tambah queue workers per node
- Gunakan Redis Cluster untuk high availability

---

## Quick Start

### Menjalankan dengan Docker (Recommended)

#### 1. Persiapan Environment
```bash
# Copy environment file
copy .env.docker.example .env.docker

# Generate keys
php artisan key:generate
php artisan reverb:install
```

#### 2. Start Docker Services
```bash
# Start all services (Nginx + 3 Laravel nodes + MySQL + Redis + Reverb)
docker-compose up -d

# Check status
docker-compose ps

# View logs
docker-compose logs -f
```

#### 3. Setup Database
```bash
# Run migrations
docker exec laravel_absensi_app_1 php artisan migrate --seed

# Create admin user (optional)
docker exec laravel_absensi_app_1 php artisan db:seed --class=AdminUserSeeder
```

#### 4. Test Real-Time Sync

1. Buka browser ke http://localhost

2. Login sebagai admin (admin@example.com / password)

3. Buka 2-3 tab browser ke http://localhost/admin/dashboard

4. Di tab baru, check-in di http://localhost/attendances

5. ✨ Lihat semua dashboard update otomatis tanpa refresh!

### Arsitektur Docker

```
┌─────────────────────────────────────┐
│     Nginx Load Balancer (:80)       │
│  Distributes traffic to 3 nodes     │
└──────────────┬──────────────────────┘
               │
    ┌──────────┼──────────┐
    │          │          │
┌───▼───┐  ┌───▼───┐  ┌───▼───┐
│ App 1 │  │ App 2 │  │ App 3 │
│ Node  │  │ Node  │  │ Node  │
└───┬───┘  └───┬───┘  └───┬───┘
    │          │          │
    └──────────┼──────────┘
               │
    ┌──────────┴──────────┐
    │                     │
┌───▼────┐         ┌──────▼──────┐
│ MySQL  │         │    Redis    │
│ :3306  │         │    :6379    │
└────────┘         └──────┬──────┘
                          │
                   ┌──────▼──────┐
                   │   Reverb    │
                   │ WebSocket   │
                   │   :8080     │
                   └─────────────┘
```

---

## Monolith vs Distributed

### Kapan Menggunakan Monolith?

✅ **Cocok untuk:**
- Startup / MVP
- Small to medium apps (< 1000 users)
- Limited budget
- Small team
- Simple requirements

**Contoh:** Blog, company website, internal tools, prototype

### Kapan Menggunakan Distributed?

✅ **Cocok untuk:**
- High-traffic apps (> 10,000 users)
- Mission-critical systems
- Need 99.9% uptime
- Geographic distribution
- Large team

**Contoh:** E-commerce, social media, banking, real-time collaboration

### Perbandingan

| Aspek | Monolith | Distributed |
|-------|----------|-------------|
| **Setup** | ✅ Simple | ❌ Complex |
| **Development** | ✅ Easy | ❌ Harder |
| **Debugging** | ✅ Easy | ❌ Harder |
| **Availability** | ❌ Single point of failure | ✅ High availability |
| **Scalability** | ❌ Vertical only | ✅ Horizontal |
| **Cost (small)** | ✅ Lower | ❌ Higher |
| **Cost (large)** | ❌ Higher | ✅ Lower |

### Migration Path

```
Phase 1: Monolith (MVP)
    ↓
Phase 2: Add Load Balancer
    ↓
Phase 3: Add Second Node
    ↓
Phase 4: Full Distributed (3+ nodes)
```

**Rekomendasi:**
- Start with monolith untuk MVP
- Migrate ke distributed saat traffic > 10K users
- Proyek ini: Gunakan distributed untuk learning & portfolio

---

## Advanced Topics

### Data Consistency

Sistem ini menggunakan **Eventual Consistency**:

```
T0: User check-in di Node 1
    ├─ Node 1: Data saved ✓
    ├─ Node 2: Belum tahu
    └─ Node 3: Belum tahu

T1: Event dispatched (< 10ms)
T2: Queue processed (< 100ms)
T3: Broadcast to all (< 200ms)
    ├─ Node 1: ✓ + UI updated
    ├─ Node 2: ✓ + UI updated
    └─ Node 3: ✓ + UI updated

Result: Consistency achieved in < 200ms
```

### Best Practices

**Database:**
```sql
-- Selalu include node_id untuk tracking
CREATE TABLE attendances (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,
    node_id INT,  -- Track which node created
    created_at TIMESTAMP,
    INDEX idx_node (node_id)
);
```

**Broadcasting:**
```php
class AttendanceCreated implements ShouldBroadcast
{
    public $tries = 3;
    public $timeout = 30;
    
    public function broadcastWith(): array
    {
        return [
            'attendance' => $this->attendance,
            'node_id' => env('NODE_ID'),
            'timestamp' => now()->toISOString(),
        ];
    }
}
```

**Frontend:**
```typescript
onMounted(() => {
    window.Echo.channel('attendances')
        .listen('.attendance.created', (event) => {
            localAttendances.value.unshift(event.attendance);
            showNotification(`New from Node ${event.node_id}`);
        });
});

onUnmounted(() => {
    window.Echo.leave('attendances');
});
```

### Monitoring

**Metrics to Monitor:**
1. Node health (CPU, memory, requests)
2. Database (connections, queries, locks)
3. Redis (memory, queue length, latency)
4. Broadcasting (event rate, latency, failures)

**Logging:**
```php
Log::info('Attendance created', [
    'node_id' => env('NODE_ID'),
    'attendance_id' => $attendance->id,
    'timestamp' => now(),
]);
```

---

## Resources

- [Laravel Broadcasting Docs](https://laravel.com/docs/broadcasting)
- [Laravel Reverb Docs](https://reverb.laravel.com)
- [CAP Theorem](https://en.wikipedia.org/wiki/CAP_theorem)
- [Distributed Systems Patterns](https://www.patterns.dev/posts/distributed-systems)
