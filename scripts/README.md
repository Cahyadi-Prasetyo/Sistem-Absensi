# Scripts

Helper scripts untuk development dan deployment.

## 📁 Files

| File | Platform | Purpose |
|------|----------|---------|
| `start-dev.bat` | Windows | Start local development environment |
| `docker-setup.bat` | Windows | Setup Docker environment |

## 🚀 Usage

### Local Development (Windows)

```bash
# Start Laravel server, Reverb, dan Queue worker
scripts\start-dev.bat
```

**What it does:**
1. Start Laravel development server (port 8000)
2. Start Reverb WebSocket server (port 8080)
3. Start Queue worker
4. Open 3 separate terminal windows

**To stop:** Close all terminal windows or press Ctrl+C in each.

### Docker Setup (Windows)

```bash
# Build dan start Docker containers
scripts\docker-setup.bat
```

**What it does:**
1. Build Docker images
2. Start all containers (nginx, nodes, mysql, redis, reverb)
3. Wait for services to be ready
4. Run database migrations and seeders

**Access:** http://localhost

## 🐧 Linux/Mac Users

Untuk Linux/Mac, gunakan command manual:

### Local Development
```bash
# Terminal 1
php artisan serve

# Terminal 2
php artisan reverb:start

# Terminal 3
php artisan queue:work
```

### Docker Setup
```bash
docker-compose up -d --build
docker-compose exec app-node-1 php artisan migrate:fresh --seed
```

## 📝 Notes

- Scripts ini dibuat untuk memudahkan quick start
- Untuk production, gunakan systemd services (Linux) atau proper process manager
- Lihat `nginx/NGINX-SETUP.md` untuk production deployment guide
