# 🚀 Getting Started

Quick guide to get Laravel Reverb Absensi up and running in 5 minutes.

---

## 📋 Prerequisites

- Docker & Docker Compose
- Node.js & npm (v18+)
- Composer (optional, can use Docker)
- Git

---

## ⚡ Quick Start (5 Minutes)

### 1. Clone Repository

```bash
git clone https://github.com/yourusername/laravel-reverb-absensi.git
cd laravel-reverb-absensi
```

### 2. Setup Environment

⚠️ **IMPORTANT:** Never commit `.env` files! See [Security Guidelines](SECURITY.md)

```bash
# Copy environment template
copy .env.docker.example .env.docker

# Generate APP_KEY
php artisan key:generate

# Generate Reverb credentials
php artisan reverb:install
```

### 3. Install Dependencies

```bash
# PHP dependencies
composer install

# Node dependencies
npm install

# Build frontend
npm run build
```

### 4. Start Docker

```bash
# Build images
docker-compose build

# Start all services
docker-compose up -d

# Check status
docker-compose ps
```

### 5. Setup Database

```bash
# Run migrations
docker exec laravel_absensi_app_1 php artisan migrate

# Seed test data
docker exec laravel_absensi_app_1 php artisan db:seed
```

### 6. Access Application

Open your browser: **http://localhost**

**Test Credentials:**
- Email: `user1@example.com`
- Password: `password`

---

## 🧪 Test Real-Time Features

### Step 1: Open 2 Browser Tabs

**Tab 1:**
1. Go to http://localhost
2. Login as `user1@example.com` / `password`
3. Click "Check In" button

**Tab 2:**
1. Go to http://localhost (new tab)
2. Login as `user2@example.com` / `password`
3. Watch the dashboard

✨ **Tab 2 should auto-update without refresh!**

---

## 📊 Verify Services

### Check Docker Services

```bash
# View all services
docker-compose ps

# Should show:
# ✅ nginx (Port 80)
# ✅ app_1, app_2, app_3 (3 instances)
# ✅ reverb (Port 8080)
# ✅ mysql (Port 3306) - Healthy
# ✅ redis (Port 6379) - Healthy
# ✅ queue
```

### Check Application

```bash
# Test HTTP
curl http://localhost

# Should return: HTTP 200 OK
```

### Check WebSocket

```bash
# Test Reverb
curl http://localhost:8080

# Should return response (not connection refused)
```

---

## 🎯 What's Next?

### Learn More

- **Architecture:** [ARCHITECTURE.md](ARCHITECTURE.md)
- **Development:** [DEVELOPMENT.md](DEVELOPMENT.md)
- **Deployment:** [DEPLOYMENT.md](DEPLOYMENT.md)
- **Security:** [SECURITY.md](SECURITY.md)

### Common Tasks

```bash
# View logs
docker-compose logs -f

# Stop services
docker-compose down

# Restart services
docker-compose restart

# Run artisan commands
docker exec laravel_absensi_app_1 php artisan [command]
```

---

## 🐛 Troubleshooting

### Port Already in Use

```bash
# Check what's using port 80
netstat -ano | findstr :80

# Change port in docker-compose.yml or stop the process
```

### Database Connection Error

```bash
# Verify .env
DB_HOST=mysql  # NOT 127.0.0.1

# Restart services
docker-compose restart
```

### Echo Not Working

```bash
# Check Reverb logs
docker-compose logs reverb

# Rebuild frontend
npm run build

# Restart Reverb
docker-compose restart reverb
```

For more troubleshooting, see [Development Guide](DEVELOPMENT.md#troubleshooting).

---

## 📚 Additional Resources

- [Docker Documentation](https://docs.docker.com)
- [Laravel Documentation](https://laravel.com/docs)
- [Vue 3 Documentation](https://vuejs.org)

---

**Next:** [Architecture Guide](ARCHITECTURE.md) to understand the system
