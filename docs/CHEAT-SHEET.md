# ⚡ Cheat Sheet: Command & Verification

Panduan cepat untuk menjalankan dan memverifikasi sistem absensi, baik menggunakan Docker Compose (Dev) maupun Docker Swarm (Prod).

---

## 🐳 1. Docker Compose (Development)

Gunakan ini untuk pengembangan lokal di satu mesin.

### ➤ Start & Stop
```bash
# Start semua service (background)
docker-compose up -d

# Stop & Hapus container
docker-compose down

# Restart service tertentu (misal: reverb)
docker-compose restart reverb
```

### ➤ Cek Status
```bash
# Lihat daftar container yang jalan
docker-compose ps

# Lihat logs semua service (real-time)
docker-compose logs -f

# Lihat logs service tertentu
docker-compose logs -f app-node-1
```

### ➤ Verifikasi Distribusi Data (Database)
Melihat node mana yang mencatat absensi.
```bash
docker exec -it sistemabsensi-mysql-1 mysql -u absensi -pAbsensiSecure2025! -e "SELECT node_id, COUNT(*) as total FROM absensi.attendances GROUP BY node_id;"
```

### ➤ Verifikasi Heartbeat (Redis)
Melihat node mana yang online (harus ada 4 key).
```bash
docker exec -it sistemabsensi-redis-1 redis-cli KEYS "absensi_database_node:*:heartbeat"
```

---

## 🐝 2. Docker Swarm (Production)

Gunakan ini untuk simulasi production atau deployment cluster.

### ➤ Start & Stop
```bash
# Inisialisasi Swarm (sekali saja)
docker swarm init

# Deploy Stack (Start)
docker stack deploy -c docker-stack-production.yml sistemabsensi

# Hapus Stack (Stop)
docker stack rm sistemabsensi

# Keluar dari Swarm Mode (Reset)
docker swarm leave --force
```

### ➤ Cek Status
```bash
# Lihat daftar service & replika (misal 4/4)
docker service ls

# Lihat detail penyebaran task
docker stack ps sistemabsensi

# Lihat logs (HANYA BISA SATU PER SATU)
docker service logs -f sistemabsensi_app-node-1
```

### ➤ Verifikasi Distribusi Data (Database)
Karena nama container di Swarm acak, kita harus cari ID-nya dulu.

**Cara Manual:**
1. Cari ID Container: `docker ps --filter name=sistemabsensi_mysql`
2. Jalankan Query:
```bash
winpty docker exec -it <CONTAINER_ID> mysql -u absensi -pAbsensiSecure2025! -e "SELECT node_id, COUNT(*) as total FROM absensi.attendances GROUP BY node_id;"
```

**Cara Otomatis (One-Liner):**
```bash
winpty docker exec -it $(docker ps -q -f name=sistemabsensi_mysql) mysql -u absensi -pAbsensiSecure2025! -e "SELECT node_id, COUNT(*) as total FROM absensi.attendances GROUP BY node_id;"
```

### ➤ Verifikasi Heartbeat (Redis)
Sama seperti database, cari ID container Redis dulu.

**Cara Otomatis (One-Liner):**
```bash
winpty docker exec -it $(docker ps -q -f name=sistemabsensi_redis) redis-cli KEYS "absensi_database_node:*:heartbeat"
```

---

## 🔍 3. Monitoring Detail

### Cek Logs Spesifik (Compose)
```bash
# Cek Reverb (WebSocket)
docker-compose logs -f reverb

# Cek Queue Worker
docker-compose logs -f queue-worker

# Cek Nginx (Load Balancer)
docker-compose logs -f nginx
```

### Cek Logs Spesifik (Swarm)
```bash
# Cek Reverb
docker service logs -f sistemabsensi_reverb

# Cek Queue Worker
docker service logs -f sistemabsensi_queue-worker
```

---

## 🛠️ 4. Troubleshooting

### Masalah Port Bentrok
Jika Nginx atau Reverb gagal start karena port sudah dipakai.
```bash
# Cek port 8000
netstat -ano | findstr :8000

# Cek port 8081
netstat -ano | findstr :8081
```

### WebSocket Connection Failed
Jika real-time update tidak jalan.
1. Cek logs Reverb: `docker-compose logs reverb`
2. Cek logs Queue Worker: `docker-compose logs queue-worker`
3. Restart keduanya: `docker-compose restart reverb queue-worker`

### Reset Total (Jika Error Aneh/Database Corrupt)
Jika database error atau cache nyangkut, hapus volume dan rebuild.

```bash
# 1. Matikan semua
docker-compose down
docker stack rm sistemabsensi

# 2. Hapus Volume (DATA HILANG!)
docker volume rm sistemabsensi_mysql_data sistemabsensi_redis_data

# 3. Rebuild tanpa cache
docker-compose build --no-cache

# 4. Start ulang
docker-compose up -d
```
