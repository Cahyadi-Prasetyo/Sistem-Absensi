# Laravel Reverb Absensi

> Sistem Absensi Real-Time Terdistribusi berbasis Laravel 12 dengan WebSocket Broadcasting

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?logo=vue.js)](https://vuejs.org)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker)](https://www.docker.com)
[![Status](https://img.shields.io/badge/Status-Production%20Ready-success)](#)

---

## 🚀 Quick Start

⚠️ **PENTING:** Jangan commit file `.env` atau `.env.docker`! Lihat [Security Guidelines](docs/SECURITY.md)

```bash
# 1. Clone repository
git clone https://github.com/yourusername/laravel-reverb-absensi.git
cd laravel-reverb-absensi

# 2. Copy environment template
copy .env.docker.example .env.docker

# 3. Generate APP_KEY
php artisan key:generate

# 4. Generate Reverb credentials
php artisan reverb:install

# 5. Install dependencies
composer install
npm install
npm run build

# 6. Start Docker
docker-compose up -d

# 7. Setup database
docker exec laravel_absensi_app_1 php artisan migrate --seed

# 8. Access application
# http://localhost
# Login: user1@example.com / password
```

**Panduan lengkap:** [docs/GETTING-STARTED.md](docs/GETTING-STARTED.md)

---

## ✨ Features

- ✅ **Real-Time Updates** - Perubahan data langsung terlihat tanpa refresh
- ✅ **Distributed System** - 3 Laravel app instances dengan load balancing
- ✅ **Check-In/Out** - Absensi masuk dan keluar dengan timestamp otomatis
- ✅ **Status Detection** - Deteksi otomatis status terlambat/tepat waktu
- ✅ **Live Dashboard** - Dashboard dengan statistik real-time
- ✅ **History** - Riwayat absensi dengan pagination dan filter
- ✅ **Node Tracking** - Tracking server mana yang memproses request
- ✅ **Authentication** - Laravel Fortify dengan 2FA support

---

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                  Load Balancer (Nginx:80)                   │
└─────────────────────────────────────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
   ┌────▼────┐          ┌────▼────┐          ┌────▼────┐
   │  App 1  │          │  App 2  │          │  App 3  │
   └────┬────┘          └────┬────┘          └────┬────┘
        │                     │                     │
        └─────────────────────┼─────────────────────┘
                              │
                    ┌─────────┴─────────┐
                    │                   │
              ┌─────▼─────┐       ┌────▼────┐
              │   Redis   │       │  MySQL  │
              └─────┬─────┘       └─────────┘
                    │
              ┌─────▼─────┐
              │  Reverb   │
              │ WebSocket │
              └───────────┘
```

**Detail:** [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)

---

## 🛠️ Tech Stack

### Backend
- **Laravel 12** - PHP Framework
- **Laravel Reverb** - WebSocket Server
- **Laravel Fortify** - Authentication + 2FA
- **MySQL 8.0** - Database
- **Redis 7** - Cache & Broadcasting

### Frontend
- **Vue 3** - JavaScript Framework
- **TypeScript** - Type Safety
- **Inertia.js** - Modern Monolith
- **Tailwind CSS** - Utility-First CSS
- **Reka UI** - Component Library

### Infrastructure
- **Docker** - Containerization
- **Docker Compose** - Orchestration
- **Nginx** - Load Balancer & Reverse Proxy

---

## 📚 Documentation

### 📖 Complete Documentation

| Document | Description |
|----------|-------------|
| [Getting Started](docs/GETTING-STARTED.md) | Quick start guide (5 minutes) |
| [Architecture](docs/ARCHITECTURE.md) | System architecture & design decisions |
| [Development](docs/DEVELOPMENT.md) | Development workflow & commands |
| [Deployment](docs/DEPLOYMENT.md) | Production deployment guide |
| [Security](docs/SECURITY.md) | ⚠️ Security guidelines (IMPORTANT!) |
| [Changelog](docs/CHANGELOG.md) | Version history & roadmap |

**Start here:** [docs/README.md](docs/README.md)

---

## 🐳 Docker Services

| Service | Port | Status |
|---------|------|--------|
| Nginx | 80, 443 | ✅ Running |
| App 1-3 | 9000 | ✅ Running |
| Reverb | 8080 | ✅ Running |
| MySQL | 3306 | ✅ Healthy |
| Redis | 6379 | ✅ Healthy |
| Queue | - | ✅ Running |

---

## 🌐 Access

- **Application:** http://localhost
- **WebSocket:** ws://localhost:8080
- **Test Login:** user1@example.com / password

---

## 🧪 Test Real-Time

1. Open 2 browser tabs
2. **Tab 1:** Login as `user1@example.com`, click "Check In"
3. **Tab 2:** Login as `user2@example.com`, watch dashboard
4. ✨ **Tab 2 auto-updates without refresh!**

---

## 📊 Docker Commands

```bash
# View logs
docker-compose logs -f

# Check status
docker ps

# Stop services
docker-compose down

# Restart
docker-compose restart

# Run artisan
docker exec laravel_absensi_app_1 php artisan [command]
```

**More commands:** [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md)

---

## 🎯 Project Status

**Version:** 1.0.0 MVP  
**Status:** ✅ Production Ready  
**Last Updated:** 9 November 2025

### ✅ Implemented (v1.0.0)

- ✅ Docker Infrastructure (7 services)
- ✅ Real-time Broadcasting (Laravel Reverb)
- ✅ Check-In/Out Functionality
- ✅ Live Dashboard
- ✅ Attendance History
- ✅ Load Balancing (3 instances)
- ✅ Node Tracking
- ✅ Authentication (Fortify + 2FA)

### 🔮 Future Enhancements

- [ ] Geolocation Validation
- [ ] Photo Capture
- [ ] Leave Management UI
- [ ] Analytics & Reports
- [ ] Push Notifications
- [ ] Admin Panel

**Full roadmap:** [docs/CHANGELOG.md](docs/CHANGELOG.md)

---

## 📝 Project Structure

```
laravel-reverb-absensi/
├── docs/                       # 📚 Complete documentation
│   ├── README.md               # Documentation index
│   ├── GETTING-STARTED.md      # Quick start guide
│   ├── ARCHITECTURE.md         # System architecture
│   ├── DEVELOPMENT.md          # Development guide
│   ├── DEPLOYMENT.md           # Deployment guide
│   ├── SECURITY.md             # Security guidelines
│   ├── CHANGELOG.md            # Version history
│   └── PLANNING.txt            # Original brainstorming
├── app/
│   ├── Events/                 # Broadcasting events
│   ├── Http/Controllers/       # API controllers
│   └── Models/                 # Eloquent models
├── database/
│   ├── migrations/             # Database migrations
│   └── seeders/                # Data seeders
├── resources/js/
│   ├── components/             # Vue components
│   └── pages/                  # Inertia pages
├── docker/
│   └── nginx/                  # Nginx configs
├── docker-compose.yml          # Docker orchestration
├── Dockerfile                  # Laravel container
└── README.md                   # This file
```

---

## 🤝 Contributing

Contributions are welcome! Please read [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md) for guidelines.

1. Fork repository
2. Create feature branch
3. Make changes
4. Write tests
5. Submit pull request

---

## 🔒 Security

⚠️ **IMPORTANT:** Never commit `.env` files to repository!

- Read [Security Guidelines](docs/SECURITY.md)
- Use `.env.docker.example` as template
- Generate unique keys for each environment
- Report security issues to: security@example.com

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- [Laravel Team](https://laravel.com)
- [Laravel Reverb](https://reverb.laravel.com)
- [Vue.js Team](https://vuejs.org)
- [Tailwind CSS](https://tailwindcss.com)
- [shadcn-vue](https://www.shadcn-vue.com)

---

## 📞 Support

- **Documentation:** [docs/README.md](docs/README.md)
- **Issues:** [GitHub Issues](https://github.com/yourusername/laravel-reverb-absensi/issues)
- **Email:** support@example.com

---

## 🌟 Show Your Support

Give a ⭐️ if this project helped you!

---

**Made with ❤️ using Laravel & Vue.js**
