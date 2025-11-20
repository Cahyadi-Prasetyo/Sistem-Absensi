# Testing Distributed System

**Panduan Lengkap Mengecek Distribusi Data Across Multi-Node**

---

## 🎯 Tujuan Testing

Memastikan bahwa:
1. **Load balancing** berfungsi (request tersebar ke 4 app nodes)
2. **Data consistency** terjaga (semua node akses database yang sama)
3. **Real-time sync** bekerja (event sampai ke semua subscriber)
4. **Heartbeat system** aktif (semua server report "Online")

---

##anya Lihat Node Mana yang Akses Database

Setiap kali ada absensi masuk/pulang, aplikasi mencatat `node_id` di database.

### Check via Docker Logs:
```bash
# Monitor logs semua app nodes
docker-compose logs -f app-node-1 app-node-2 app-node-3 app-node-4
```

Lalu:
1. **Refresh halaman dashboard** beberapa kali
2. **Lihat logs** → Node mana yang respond

Contoh output:
```
app-node-1 | [2025-11-20 22:00:01] GET /
app-node-3 | [2025-11-20 22:00:05] GET /
app-node-2 | [2025-11-20 22:00:10] GET /admin/riwayat
app-node-4 | [2025-11-20 22:00:15] POST /absensi/masuk
```

**✅ Distributed**: Request disebar ke node berbeda

---

## 2. Check Database `node_id` Field

Setiap attendance record punya field `node_id` yang menunjukkan node mana yang process request.

### Via MySQL:
```bash
# Masuk ke MySQL
docker exec -it sistemabsensi-mysql-1 mysql -u absensi -pAbsensiSecure2025! absensi

# Query
SELECT id, user_id, date, jam_masuk, jam_pulang, status, node_id 
FROM attendances 
WHERE date = CURDATE() 
ORDER BY created_at DESC 
LIMIT 10;
```

**Expected Output**:
```
+----+---------+------------+-----------+------------+--------+------------+
| id | user_id | date       | jam_masuk | jam_pulang | status | node_id    |
+----+---------+------------+-----------+------------+--------+------------+
|  5 |       2 | 2025-11-20 | 08:15:00  | NULL       | Hadir  | app-node-3 |
|  4 |       3 | 2025-11-20 | 08:10:00  | 17:00:00   | Hadir  | app-node-1 |
|  3 |       4 | 2025-11-20 | 08:05:00  | NULL       | Hadir  | app-node-2 |
|  2 |       5 | 2025-11-20 | 08:00:00  | 17:05:00   | Hadir  | app-node-4 |
+----+---------+------------+-----------+------------+--------+------------+
```

**✅ Distributed**: node_id berbeda untuk setiap entry

### Via PHP Artisan:
```bash
docker exec -it sistemabsensi-app-node-1-1 php artisan tinker

# Di tinker
>>> \App\Models\Attendance::whereDate('date', today())
...    ->get(['id', 'user_id', 'node_id', 'status'])
...    ->groupBy('node_id')
...    ->map->count();

# Output:
=> Illuminate\Support\Collection {
     all: [
       "app-node-1" => 3,
       "app-node-2" => 2,
       "app-node-3" => 4,
       "app-node-4" => 1,
     ],
   }
```

**✅ Load Distribution**: Request tersebar relatif merata

---

## 3. Test Load Balancer Nginx

Nginx menggunakan `ip_hash` untuk load balancing. Ini berarti:
- **User yang sama** akan selalu ke node yang sama (sticky session)
- **User berbeda** akan ke node berbeda

### Check Nginx Config:
```bash
cat nginx/nginx.conf | grep -A 10 "upstream laravel_backend"
```

Output:
```
upstream laravel_backend {
    ip_hash;  # <- Sticky session
    server app-node-1:9000;
    server app-node-2:9000;
    server app-node-3:9000;
    server app-node-4:9000;
}
```

### Test dengan Multiple IPs/Browsers:
1. **Browser 1 (Chrome)**: Login → Refresh 5x → Check logs
2. **Browser 2 (Firefox)**: Login → Refresh 5x → Check logs
3. **Incognito Mode**: Login → Refresh 5x → Check logs

**Command untuk monitor**:
```bash
docker-compose logs -f nginx | grep "GET /"
```

**✅ Distributed**: Different source IP → Different node

---

## 4. Check Server Status Dashboard

Dashboard admin menampilkan status 4 server (Jakarta, Bandung, Surabaya, Bali).

### Via Web:
1. Login sebagai admin
2. Lihat di dashboard bagian "Status Server"
3. **All 4 servers** harus hijau "Online"

### Via Redis (Heartbeat Check):
```bash
# Masuk ke Redis
docker exec -it sistemabsensi-redis-1 redis-cli

# Check heartbeat keys
127.0.0.1:6379> KEYS "absensi_database_node:*:heartbeat"

# Output:
1) "absensi_database_node:app-node-1:heartbeat"
2) "absensi_database_node:app-node-2:heartbeat"
3) "absensi_database_node:app-node-3:heartbeat"
4) "absensi_database_node:app-node-4:heartbeat"

# Check value (timestamp)
127.0.0.1:6379> GET "absensi_database_node:app-node-1:heartbeat"
"2025-11-20T15:30:45+07:00"

# Check TTL (should be < 60 seconds)
127.0.0.1:6379> TTL "absensi_database_node:app-node-1:heartbeat"
(integer) 45
```

**✅ All Nodes Alive**: Semua key ada dan TTL < 60 detik

---

## 5. Test Real-Time Event Distribution

Event harus sampai ke **semua subscribers** (4 subscriber nodes).

### Monitor Subscriber Logs:
```bash
docker-compose logs -f subscriber-node-1 subscriber-node-2 subscriber-node-3 subscriber-node-4
```

### Test Scenario:
1. **Monitor logs** di terminal
2. **Login karyawan** di browser
3. **Klik "Absen Masuk"**
4. **Cek logs subscriber** → Semua harus menerima event

**Expected Output**:
```
subscriber-node-1 | [2025-11-20 15:30:00] Event received: AttendanceCreated
subscriber-node-2 | [2025-11-20 15:30:00] Event received: AttendanceCreated
subscriber-node-3 | [2025-11-20 15:30:00] Event received: AttendanceCreated
subscriber-node-4 | [2025-11-20 15:30:00] Event received: AttendanceCreated
```

**✅ Pub/Sub Working**: Event broadcast ke semua subscriber

---

## 6. Check Queue Worker Processing

Queue worker harus memproses broadcast job dan kirim ke Reverb.

### Monitor Queue Logs:
```bash
docker-compose logs -f queue-worker
```

### Test:
1. **Absen masuk/pulang**
2. **Cek logs**

**Expected Output**:
```
[2025-11-20 15:30:00] Processing: App\Events\AbsensiCreated
[2025-11-20 15:30:01] Processed:  App\Events\AbsensiCreated
```

### Check Queue Status:
```bash
docker exec -it sistemabsensi-queue-worker-1 php artisan queue:work redis --once --verbose
```

**✅ Queue Healthy**: Jobs diproses tanpa error

---

## 7. End-to-End Test: Distributed Real-Time Flow

### Setup:
- **Tab 1**: Admin dashboard (observe)
- **Tab 2**: Karyawan login
- **Terminal**: Monitor logs

### Steps:
```bash
# Terminal 1: Monitor all critical services
docker-compose logs -f queue-worker reverb | grep -E "AttendanceCreated|pusher"

# Terminal 2: Monitor app nodes
docker-compose logs -f app-node-1 app-node-2 app-node-3 app-node-4 | grep "POST /absensi"
```

### Execute:
1. **Tab 2 (Karyawan)**: Klik "Absen Masuk"
2. **Check Terminal 2**: Lihat node mana yang handle (e.g., app-node-3)
3. **Check MySQL**: 
   ```sql
   SELECT node_id FROM attendances ORDER BY id DESC LIMIT 1;
   -- Should show: app-node-3
   ```
4. **Check Terminal 1**: 
   - Queue worker process event
   - Reverb broadcast ke client
5. **Tab 1 (Admin)**: Entry muncul di "Absensi Terbaru" **tanpa refresh**

**✅ Full Distributed Flow Verified**

---

## 8. Stress Test: Concurrent Requests

### Using Apache Bench:
```bash
# Install ab (if not installed)
# Windows: Download Apache httpd binary
# Linux: sudo apt install apache2-utils

# Test load distribution
ab -n 1000 -c 10 http://localhost:8000/
```

### Check Distribution:
```bash
# Count requests per node
docker-compose logs nginx | grep "GET /" | grep -oP 'upstream: \K[^:]+' | sort | uniq -c
```

**Expected Output**:
```
250 app-node-1
250 app-node-2
250 app-node-3
250 app-node-4
```

**✅ Even Distribution** (± 10% variance acceptable due to ip_hash)

---

## 9. Failover Test (Advanced)

Test apa yang terjadi jika satu node mati.

### Scenario:
```bash
# Stop node-2
docker-compose stop app-node-2

# Check dashboard server status
# Node 2 should show "Offline" after 30 seconds

# Try to access app → Should still work (other nodes handle)
curl http://localhost:8000

# Check logs
docker-compose logs nginx | tail -20
```

**Expected**: Request di-route ke node 1, 3, 4 (skip node 2)

### Restore:
```bash
docker-compose start app-node-2
```

**✅ High Availability**: System tetap jalan meski 1 node down

---

## 10. Summary Commands (Copy-Paste Ready)

### Quick Health Check:
```bash
# All containers status
docker-compose ps

# Check all services healthy
docker ps --format "table {{.Names}}\t{{.Status}}" | grep -i healthy

# Redis heartbeat check
docker exec -it sistemabsensi-redis-1 redis-cli KEYS "absensi_database_node:*:heartbeat"

# Database distribution check
docker exec -it sistemabsensi-mysql-1 mysql -u absensi -pAbsensiSecure2025! -e "SELECT node_id, COUNT(*) as total FROM absensi.attendances WHERE date = CURDATE() GROUP BY node_id;"

# Queue worker check
docker-compose logs queue-worker --tail 20 | grep -i "processed"

# Reverb connection check
docker-compose logs reverb --tail 20 | grep -i "connected"
```

### Comprehensive Test Script:
```bash
#!/bin/bash
echo "=== Distributed System Health Check ==="

echo "\n1. Container Status:"
docker-compose ps

echo "\n2. Node Heartbeats:"
docker exec -it sistemabsensi-redis-1 redis-cli KEYS "absensi_database_node:*:heartbeat"

echo "\n3. Today's Attendance Distribution:"
docker exec -it sistemabsensi-mysql-1 mysql -u absensi -pAbsensiSecure2025! -e "SELECT node_id, COUNT(*) as count FROM absensi.attendances WHERE date = CURDATE() GROUP BY node_id;"

echo "\n4. Queue Status:"
docker-compose logs queue-worker --tail 5

echo "\n5. Reverb Status:"
docker-compose logs reverb --tail 5

echo "\n=== Test Complete ==="
```

---

## 📊 Success Criteria

Sistem dianggap **fully distributed** jika:

- [x] **4 app nodes** semua status "Healthy"
- [x] **4 server status** di dashboard semua "Online" (hijau)
- [x] **Database `node_id`** menunjukkan distribusi ke node berbeda
- [x] **Nginx logs** menunjukkan request ke multiple nodes
- [x] **Real-time events** diterima semua subscriber
- [x] **Queue worker** memproses jobs tanpa error
- [x] **Reverb** broadcast sukses ke browser clients
- [x] **Failover** tetap jalan jika 1 node mati

---

**Status**: 🟢 **DISTRIBUTED SYSTEM VERIFIED**
