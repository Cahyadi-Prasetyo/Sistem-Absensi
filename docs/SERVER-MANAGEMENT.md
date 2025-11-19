# 🏢 Server Management Guide

## Mapping Server

| Container Name | Server Name | Lokasi | Fungsi |
|----------------|-------------|---------|---------|
| `app-node-1` | **Server Jakarta** | Jakarta | Application Server |
| `app-node-2` | **Server Bandung** | Bandung | Application Server |
| `app-node-3` | **Server Surabaya** | Surabaya | Application Server |
| `app-node-4` | **Server Bali** | Bali | Application Server |
| `subscriber-node-1` | Subscriber 1 | - | Redis Pub/Sub Listener |
| `subscriber-node-2` | Subscriber 2 | - | Redis Pub/Sub Listener |
| `subscriber-node-3` | Subscriber 3 | - | Redis Pub/Sub Listener |
| `subscriber-node-4` | Subscriber 4 | - | Redis Pub/Sub Listener |

## 🎛️ Mengaktifkan/Menonaktifkan Server

### Menonaktifkan Server (Stop)

```bash
# Matikan Server Jakarta
docker-compose stop app-node-1

# Matikan Server Bandung
docker-compose stop app-node-2

# Matikan Server Surabaya
docker-compose stop app-node-3

# Matikan Server Bali
docker-compose stop app-node-4

# Matikan beberapa server sekaligus
docker-compose stop app-node-1 app-node-3
```

### Mengaktifkan Server (Start)

```bash
# Nyalakan Server Jakarta
docker-compose start app-node-1

# Nyalakan Server Bandung
docker-compose start app-node-2

# Nyalakan Server Surabaya
docker-compose start app-node-3

# Nyalakan Server Bali
docker-compose start app-node-4

# Nyalakan semua server
docker-compose start app-node-1 app-node-2 app-node-3 app-node-4
```

### Restart Server

```bash
# Restart Server Jakarta
docker-compose restart app-node-1

# Restart semua app servers
docker-compose restart app-node-1 app-node-2 app-node-3 app-node-4
```

### Hapus Server (Remove)

```bash
# Hapus container Server Jakarta (akan dibuat ulang saat start)
docker-compose rm -f app-node-1

# Rebuild dan start ulang
docker-compose up -d --build app-node-1
```

## 📊 Monitoring Server

### Cek Status Semua Server

```bash
docker-compose ps
```

### Cek Status Server Tertentu

```bash
docker-compose ps app-node-1
docker-compose ps app-node-2
docker-compose ps app-node-3
docker-compose ps app-node-4
```

### Cek Logs Server

```bash
# Logs Server Jakarta
docker-compose logs -f app-node-1

# Logs Server Bandung (20 baris terakhir)
docker-compose logs --tail=20 app-node-2

# Logs semua app servers
docker-compose logs -f app-node-1 app-node-2 app-node-3 app-node-4
```

### Cek Resource Usage

```bash
# Resource usage semua containers
docker stats

# Resource usage server tertentu
docker stats sistemabsensi-app-node-1-1
```

## 📡 Subscriber Nodes - Apa Fungsinya?

**Subscriber nodes** adalah service yang mendengarkan Redis pub/sub untuk sinkronisasi data real-time antar nodes.

### Fungsi Utama:
1. **Real-time Sync**: Menyinkronkan data antar semua application nodes
2. **Event Broadcasting**: Mendengarkan event dari Redis dan broadcast ke semua nodes
3. **Distributed System**: Memastikan konsistensi data di semua nodes

### Kenapa Perlu Subscriber?
Ketika ada data baru di Server Jakarta, subscriber akan memastikan Server Bandung, Surabaya, dan Bali juga mendapat update yang sama secara real-time.

### Management Subscriber

```bash
# Stop subscriber
docker-compose stop subscriber-node-1

# Start subscriber
docker-compose start subscriber-node-1

# Logs subscriber
docker-compose logs -f subscriber-node-1

# Restart semua subscribers
docker-compose restart subscriber-node-1 subscriber-node-2 subscriber-node-3 subscriber-node-4
```

## 🧪 Testing High Availability

### Test 1: Matikan 1 Server

```bash
# Matikan Server Jakarta
docker-compose stop app-node-1

# Akses aplikasi di browser - masih bisa diakses via server lain
# Cek di dashboard - Server Jakarta akan offline
```

### Test 2: Matikan 2 Server

```bash
# Matikan Server Jakarta dan Bandung
docker-compose stop app-node-1 app-node-2

# Aplikasi masih bisa diakses via Surabaya dan Bali
```

### Test 3: Nyalakan Kembali

```bash
# Nyalakan semua server
docker-compose start app-node-1 app-node-2 app-node-3 app-node-4

# Tunggu beberapa detik, semua server akan online kembali
```

## 🔧 Troubleshooting

### Server Tidak Online di Dashboard

1. Cek apakah container running:
   ```bash
   docker-compose ps app-node-1
   ```

2. Cek logs untuk error:
   ```bash
   docker-compose logs --tail=50 app-node-1
   ```

3. Cek heartbeat di Redis:
   ```bash
   docker-compose exec redis redis-cli KEYS "node:app*"
   docker-compose exec redis redis-cli GET "node:app-node-1:heartbeat"
   ```

4. Restart scheduler (yang mengirim heartbeat):
   ```bash
   docker-compose restart scheduler
   ```

### Server Lambat

1. Cek resource usage:
   ```bash
   docker stats sistemabsensi-app-node-1-1
   ```

2. Restart server:
   ```bash
   docker-compose restart app-node-1
   ```

3. Rebuild jika perlu:
   ```bash
   docker-compose up -d --build app-node-1
   ```

### Load Balancing Tidak Merata

1. Cek nginx logs:
   ```bash
   docker-compose logs --tail=50 nginx
   ```

2. Restart nginx:
   ```bash
   docker-compose restart nginx
   ```

## 📈 Scaling

### Menambah Server Baru (Node 5)

1. Edit `docker-compose.yml`, tambahkan:
   ```yaml
   app-node-5:
     # Copy config dari app-node-4
     environment:
       APP_NODE_ID: app-node-5
   ```

2. Update `nginx/nginx.conf`, tambahkan:
   ```nginx
   server app-node-5:9000 max_fails=3 fail_timeout=30s;
   ```

3. Update `app/Services/ServerStatusService.php`:
   ```php
   'app-node-5' => 'Server Medan',
   ```

4. Start services:
   ```bash
   docker-compose up -d --build app-node-5
   docker-compose restart nginx
   ```

## 🎯 Best Practices

1. **Jangan matikan semua server sekaligus** - Minimal 1 server harus online
2. **Restart server satu per satu** - Untuk zero-downtime deployment
3. **Monitor logs secara berkala** - Untuk deteksi masalah lebih awal
4. **Backup database** - Sebelum maintenance besar
5. **Test di staging** - Sebelum apply ke production

## 🚨 Emergency Commands

### Restart Semua Services

```bash
docker-compose restart
```

### Stop Semua Services

```bash
docker-compose down
```

### Start Semua Services

```bash
docker-compose up -d
```

### Rebuild Semua Services

```bash
docker-compose up -d --build
```

### Clean Restart (Hapus semua data)

```bash
docker-compose down -v
docker-compose up -d --build
```

⚠️ **Warning**: `docker-compose down -v` akan menghapus semua data di database!
