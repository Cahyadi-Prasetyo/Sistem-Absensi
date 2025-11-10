# Laravel Reverb Absensi

> Sistem Absensi Real-Time Terdistribusi berbasis Laravel 12 dengan WebSocket Broadcasting

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?logo=vue.js)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-2-9553E9)](https://inertiajs.com)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker)](https://www.docker.com)
[![Status](https://img.shields.io/badge/Status-Optimized-success)](#)

---

## 🚀 Quick Start

⚠️ **PENTING:** Jangan commit file `.env` atau `.env.docker`! Lihat [Security Guidelines](docs/SECURITY.md)

### 💡 Ready for Development!

**Modern Stack with Inertia.js + Vue 3:**
- Real-time updates via Reverb WebSocket
- Type-safe with TypeScript
- Beautiful UI with Tailwind CSS 4
- Component library with Reka UI

**Want to develop new features?** See [docs/INERTIA-DEVELOPMENT.md](docs/INERTIA-DEVELOPMENT.md)

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

### 🌐 Distributed System
- ✅ **Multi-Node Architecture** - Berjalan di beberapa server secara bersamaan
- ✅ **Real-Time Sync** - Data otomatis sync antar semua node tanpa refresh
- ✅ **Shared Database** - Semua node mengakses database yang sama
- ✅ **Shared Redis** - Queue dan cache terdistribusi
- ✅ **Node Tracking** - Tracking server mana yang memproses request
- ✅ **Load Balancing** - Distribusi traffic ke multiple instances

### 📱 Core Features
- ✅ **Real-Time Updates** - Perubahan data langsung terlihat tanpa refresh
- ✅ **Check-In/Out** - Absensi masuk dan keluar dengan timestamp otomatis
- ✅ **Status Detection** - Deteksi otomatis status terlambat/tepat waktu
- ✅ **Live Dashboard** - Dashboard dengan statistik real-time
- ✅ **History** - Riwayat absensi dengan pagination dan filter
- ✅ **Browser Notifications** - Notifikasi desktop untuk attendance baru
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
- **Inertia.js 2.0** - Modern Monolith
- **Vue 3** - Progressive JavaScript Framework
- **TypeScript** - Type Safety
- **Tailwind CSS 4** - Utility-First CSS
- **Reka UI** - Component Library

### Infrastructure
- **Docker** - Containerization
- **Docker Compose** - Orchestration
- **Nginx** - Load Balancer & Reverse Proxy

---

## 📚 Documentation

### 📚 Documentation

| Document | Description |
|----------|-------------|
| [Getting Started](docs/GETTING-STARTED.md) | ⚡ Setup & installation guide |
| [Distributed System](docs/DISTRIBUTED_SYSTEM.md) | 🌐 Multi-node architecture & testing |
| [Troubleshooting](docs/TROUBLESHOOTING.md) | 🔧 Common issues & solutions |
| [Deployment](docs/DEPLOYMENT.md) | 🚀 Production deployment guide |
| [Security](docs/SECURITY.md) | 🔒 Security best practices |
| [Changelog](docs/CHANGELOG.md) | 📝 Version history |

**Start here:** [docs/README.md](docs/README.md) - Complete documentation index

> 📁 **Note:** All documentation is organized in `docs/` folder. Old/deprecated docs moved to `docs/archive/`

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

### Test Distributed System
1. Start Docker: `docker-compose up -d`
2. Open 3 browser tabs ke `http://localhost/admin/dashboard`
3. Login sebagai admin di semua tab
4. Di tab ke-4, check-in di `http://localhost/attendances`
5. ✨ **Semua dashboard update otomatis tanpa refresh!**

### Test Load Balancing
1. Refresh halaman beberapa kali
2. Cek logs: `docker-compose logs -f app_1 app_2 app_3`
3. ✨ **Request didistribusikan ke 3 node berbeda!**

### Test Node Failure
1. Stop satu node: `docker-compose stop app_2`
2. Akses `http://localhost` - masih bisa diakses
3. ✨ **Sistem tetap berjalan dengan 2 node aktif!**

**Panduan lengkap:** [docs/DISTRIBUTED_SYSTEM.md](docs/DISTRIBUTED_SYSTEM.md)

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

### 🚀 Ready for Development

**Current Stack:**
- ✅ Laravel 12 + Inertia.js + Vue 3
- ✅ Real-time WebSocket (Reverb)
- ✅ Docker infrastructure ready
- ✅ Authentication + 2FA
- ✅ Basic attendance features

**Next Steps:**
- 📋 Admin Panel - User & attendance management
- 🏖️ Leave Management - Cuti/izin system
- 📊 Analytics - Advanced reporting
- 📍 Geolocation - GPS validation

**See:** [docs/INERTIA-DEVELOPMENT.md](docs/INERTIA-DEVELOPMENT.md) - Panduan lengkap pengembangan fitur baru

### ✅ Implemented (v1.0.0)

**Infrastructure:**
- ✅ Docker Infrastructure (7 services)
- ✅ Real-time Broadcasting (Laravel Reverb)
- ✅ Load Balancing (3 instances)
- ✅ Redis Cache & Broadcasting
- ✅ MySQL Database

**Features:**
- ✅ Check-In/Out Functionality
- ✅ Live Dashboard
- ✅ Attendance History
- ✅ Node Tracking
- ✅ Authentication (Fortify + 2FA)

**Frontend:**
- ✅ Inertia.js + Vue 3
- ✅ TypeScript Support
- ✅ Tailwind CSS 4
- ✅ Reka UI Components

### 🔮 Roadmap

**High Priority:**
- [ ] Admin Panel (User & attendance management)
- [ ] Leave Management (Cuti/izin system)
- [ ] Analytics & Reports (Advanced reporting)

**Medium Priority:**
- [ ] Geolocation Validation (GPS check-in)
- [ ] Photo Capture (Selfie attendance)
- [ ] Push Notifications
- [ ] Email Notifications

**Future:**
- [ ] Mobile App (React Native)
- [ ] Multi-tenant Support
- [ ] Advanced Analytics
- [ ] Integration APIs

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
