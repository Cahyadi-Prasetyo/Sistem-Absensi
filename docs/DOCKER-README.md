# Docker Setup - Sistem Absensi Real-Time

## 🚀 Quick Start

### Menjalankan Aplikasi

Jalankan script berikut untuk memulai semua services:

```bash
docker-start.bat
```

Atau manual dengan docker-compose:

```bash
docker-compose --env-file .env.docker-compose up -d --build
```

### Menghentikan Aplikasi

```bash
docker-stop.bat
```

Atau manual:

```bash
docker-compose down
```

### Melihat Logs

Semua services:
```bash
docker-logs.bat
```

Service tertentu:
```bash
docker-logs.bat reverb
docker-logs.bat queue-worker
docker-logs.bat app-node-1
```

## 📦 Services yang Berjalan

| Service | Port | Deskripsi |
|---------|------|-----------|
| **nginx** | 8000 | Load balancer & web server |
| **reverb** | 8080 | WebSocket server (Laravel Reverb) |
| **mysql** | 3307 | Database MySQL |
| **redis** | 6379 | Cache & Queue backend |
| **queue-worker** | - | Laravel queue worker |
| **scheduler** | - | Laravel task scheduler (heartbeat) |
| **app-node-1** | - | Laravel app (Jakarta) |
| **app-node-2** | - | Laravel app (Bandung) |
| **app-node-3** | - | Laravel app (Surabaya) |
| **app-node-4** | - | Laravel app (Bali) |
| **subscriber-node-1/2/3** | - | Redis pub/sub subscribers |

## 🌐 Akses Aplikasi

- **Web Application**: http://localhost:8000
- **WebSocket**: ws://localhost:8080
- **MySQL**: localhost:3307
  - Database: `absensi`
  - Username: `absensi`
  - Password: `absensi_password_2024`
  - Root Password: `root_password_2024`

## 🔧 Konfigurasi

### Environment Variables

Konfigurasi utama ada di:
- `.env.docker-compose` - Build arguments untuk Docker
- `docker/.env.docker` - Environment variables untuk containers

### Reverb WebSocket

Reverb sudah dikonfigurasi dengan:
- Key: `reverb-key`
- Host: `0.0.0.0` (di container)
- Port: `8080`
- Scheme: `http`

Frontend akan connect ke `ws://localhost:8080`

### Queue Worker

Queue worker menggunakan Redis sebagai backend dan akan otomatis memproses jobs dengan:
- Max tries: 3
- Timeout: 90 detik
- Sleep: 3 detik

## 🛠️ Troubleshooting

### Cek Status Containers

```bash
docker-compose ps
```

### Cek Logs Container Tertentu

```bash
docker-compose logs -f reverb
docker-compose logs -f queue-worker
docker-compose logs -f app-node-1
```

### Restart Service Tertentu

```bash
docker-compose restart reverb
docker-compose restart queue-worker
```

### Rebuild Container

```bash
docker-compose up -d --build reverb
```

### Masuk ke Container

```bash
docker-compose exec app-node-1 sh
```

### Jalankan Artisan Command

```bash
docker-compose exec app-node-1 php artisan migrate
docker-compose exec app-node-1 php artisan cache:clear
```

### WebSocket Tidak Connect

1. Pastikan Reverb running:
   ```bash
   docker-compose logs reverb
   ```

2. Cek apakah port 8080 terbuka:
   ```bash
   netstat -ano | findstr :8080
   ```

3. Rebuild frontend assets jika perlu:
   ```bash
   npm run build
   ```

### Database Connection Error

1. Pastikan MySQL healthy:
   ```bash
   docker-compose ps mysql
   ```

2. Cek logs MySQL:
   ```bash
   docker-compose logs mysql
   ```

3. Test connection:
   ```bash
   docker-compose exec app-node-1 php artisan health:check
   ```

## 🔄 Update Aplikasi

Setelah melakukan perubahan code:

1. Rebuild containers:
   ```bash
   docker-compose up -d --build
   ```

2. Atau rebuild service tertentu:
   ```bash
   docker-compose up -d --build app-node-1
   ```

## 🧹 Cleanup

### Hapus Containers & Networks

```bash
docker-compose down
```

### Hapus Containers, Networks & Volumes

```bash
docker-compose down -v
```

### Hapus Images

```bash
docker-compose down --rmi all
```

## 📊 Monitoring

### Resource Usage

```bash
docker stats
```

### Container Health

```bash
docker-compose ps
```

Semua container seharusnya menunjukkan status `healthy` atau `running`.

## 🎯 Production Notes

1. **Security**: Ganti semua password di `docker/.env.docker` dan `.env.docker-compose`
2. **SSL/TLS**: Untuk production, gunakan HTTPS dan WSS (WebSocket Secure)
3. **Scaling**: Bisa menambah lebih banyak app nodes dengan menduplikasi service di docker-compose.yml
4. **Backup**: Backup volume `mysql_data` secara berkala
5. **Monitoring**: Pertimbangkan menggunakan tools seperti Prometheus + Grafana

## 📝 Architecture

```
┌─────────────┐
│   Browser   │
└──────┬──────┘
       │
       ├─── HTTP ──────► nginx:80 ──► Load Balance ──► app-node-1/2/3
       │
       └─── WS ────────► reverb:8080
                              │
                              ├─── Redis (pub/sub)
                              │
                              └─── MySQL
```

## 🤝 Support

Jika ada masalah, cek:
1. Logs dengan `docker-compose logs -f`
2. Status dengan `docker-compose ps`
3. Health check dengan `docker-compose exec app-node-1 php artisan health:check`
