# Sistem Absensi Real-Time Terdistribusi

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white)
![Redis](https://img.shields.io/badge/Redis-7-DC382D?style=for-the-badge&logo=redis&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Latest-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Nginx](https://img.shields.io/badge/Nginx-Alpine-009639?style=for-the-badge&logo=nginx&logoColor=white)

**Sistem absensi karyawan modern dengan arsitektur distributed, real-time synchronization, dan high availability**

[Documentation](./docs) • [Getting Started](./docs/GETTING-STARTED.md) • [Testing Guide](./docs/TESTING-DISTRIBUTED.md)

</div>

---

## 📖 Tentang Proyek

Sistem Absensi Real-Time Terdistribusi adalah aplikasi web production-ready yang dibangun dengan **Laravel 12** dan **Laravel Reverb** untuk mengelola absensi karyawan dengan real-time updates dan high availability. Sistem ini mendemonstrasikan implementasi **distributed architecture** dengan multiple application nodes, load balancing, dan event-driven communication menggunakan WebSocket.

### 🎯 Key Features

- ✅ **Real-Time Updates** - WebSocket-based live notifications via Laravel Reverb
- ✅ **Distributed Architecture** - 4 application nodes with Nginx load balancing
- ✅ **High Availability** - Automatic failover and service recovery
- ✅ **Multi-Node Deployment** - Docker Compose & Docker Swarm support
- ✅ **Event Broadcasting** - Redis Pub/Sub for cross-node communication
- ✅ **Server Monitoring** - Real-time server status dashboard with heartbeat system
- ✅ **Mobile Responsive** - Tailwind CSS responsive design

---

## 🏗️ Tech Stack

### Backend
- **Framework**: Laravel 12
- **Language**: PHP 8.3-FPM
- **Database**: MySQL 8.0
- **Cache & Queue**: Redis 7
- **WebSocket**: Laravel Reverb

### Frontend
- **UI Framework**: Alpine.js 3
- **Styling**: Tailwind CSS
- **Real-Time**: Laravel Echo + Pusher Protocol
- **Build Tool**: Vite

### Infrastructure
- **Containerization**: Docker & Docker Compose
- **Orchestration**: Docker Swarm (Production)
- **Load Balancer**: Nginx (with `ip_hash`)
- **Reverse Proxy**: Nginx for WebSocket

---

## 🚀 Quick Start

### Prerequisites
- Docker Desktop (Windows/Mac) atau Docker Engine (Linux)
- Docker Compose v2.0+
- Minimum 4GB RAM (Recommended 8GB)

### Installation

1. **Clone Repository**
```bash
git clone <repository-url>
cd Sistem-Absensi
```

2. **Build & Start Services**
```bash
# Build all Docker images
docker-compose build

# Start all services
docker-compose up -d

# Wait for ~30 seconds for database migration
```

3. **Verify Services**
```bash
# Check all containers are running
docker-compose ps

# All containers should show "Up" or "Healthy" status
```

4. **Access Application**
- **URL**: http://localhost:8000
- **Login Page**: Credentials available in seeded database

For detailed instructions, see [docs/GETTING-STARTED.md](./docs/GETTING-STARTED.md)

---

## 📊 System Architecture

```
┌─────────────────────────────────────────────┐
│         Browser Clients                      │
│  (Admin Dashboard + Employee Portal)         │
└────────────┬────────────────────────────────┘
             │
             ├─ HTTP (8000) ──────────────┐
             └─ WebSocket (8081) ─────┐   │
                                       │   │
┌──────────────────────────────────┐  │   │
│    Nginx Load Balancer (8000)    │◄─┘   │
└──────────┬───────────────────────┘       │
           │ (ip_hash distribution)        │
    ┌──────┴──────┬──────┬──────┐          │
    │             │      │      │          │
┌───▼──┐     ┌───▼──┐ ┌─▼───┐ ┌▼────┐    │
│Node-1│     │Node-2│ │Node3│ │Node4│    │
│ PHP  │     │ PHP  │ │ PHP │ │ PHP │    │
│ FPM  │     │ FPM  │ │ FPM │ │ FPM │    │
└───┬──┘     └───┬──┘ └─┬───┘ └┬────┘    │
    │            │      │      │          │
    └────────────┴──────┴──────┘          │
                 │                         │
    ┌────────────┼─────────────┐           │
    │            │             │           │
┌───▼────┐  ┌───▼──┐    ┌────▼────┐      │
│ MySQL  │  │Redis │    │ Reverb  │◄─────┘
│  8.0   │  │  7   │    │WebSocket│
└────────┘  └──┬───┘    │ (8081)  │
               │        └─────────┘
               │              ▲
          ┌────┴────┐         │
          │         │         │
     ┌────▼───┐ ┌──▼────┐    │
     │Queue   │ │4x Sub │────┘
     │Worker  │ │Nodes  │
     └────────┘ └───────┘
```

---

## 🎯 Core Features

### For Admin
- **Real-Time Dashboard**
  - Live attendance updates via WebSocket
  - Today/Week/Month statistics
  - Server status monitoring (4 nodes)
  - Latest attendance feed (auto-refresh)

- **Attendance History**
  - "Hari Ini (Live)" tab - Real-time today's data
  - "Semua Riwayat" tab - Full history with pagination
  - Advanced search & filter
  - Date range selection
  - Export to CSV

- **User Management**
  - Employee CRUD operations
  - Role management (admin/user)
  - Bulk operations

### For Employee
- **Dashboard Portal**
  - Clock In / Clock Out buttons
  - Geolocation-based attendance
  - Real-time status display

- **Personal History**
  - View personal attendance records
  - Monthly summary
  - Export personal data

---

## 🐳 Deployment Options

### Development (Docker Compose)
```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

### Production (Docker Swarm)
```bash
# Initialize Swarm
docker swarm init

# Deploy stack
docker stack deploy -c docker-stack-production.yml sistemabsensi

# Check services
docker stack services sistemabsensi
```

For detailed deployment guide, see [docs/DOCKER-SWARM-DEPLOYMENT.md](./docs/DOCKER-SWARM-DEPLOYMENT.md)

---

## 🔧 Services Overview

| Service | Replicas | Port | Description |
|---------|----------|------|-------------|
| **Redis** | 1 | 6379 | Cache, Queue, Pub/Sub |
| **MySQL** | 1 | 3307 | Primary Database |
| **Nginx** | 1 | 8000 | Load Balancer & Reverse Proxy |
| **Reverb** | 1 | 8081 | WebSocket Server |
| **Queue Worker** | 1 | - | Background Job Processor |
| **App Nodes** | 4 | 9000 (internal) | PHP-FPM Application Servers |
| **Subscribers** | 4 | - | Redis Pub/Sub Listeners |
| **Migration** | 1 (one-time) | - | Database Migration Service |

**Total**: 14 services

---

## 🧪 Testing Distributed System (Verification Prompts)

Gunakan command berikut untuk memverifikasi bahwa sistem berjalan secara terdistribusi (baik di Docker Compose maupun Swarm).

### 1. Cek Distribusi Node (Database)
Lihat bagaimana data absensi tersebar di berbagai node yang menangani request.

```bash
# Masuk ke container MySQL dan jalankan query
---

## � Future Implementation Roadmap

Berikut adalah rencana pengembangan sistem untuk fase selanjutnya:

### Phase 1: Quick Wins (Security & Monitoring)
-  **Docker Secrets**: Mengganti `.env` file dengan Docker Secrets untuk keamanan credential yang lebih baik.
- **Prometheus + Grafana**: Implementasi monitoring dashboard untuk metrics CPU, RAM, dan Request per second.
- **Health Checks**: Menambahkan endpoint `/health` yang lebih komprehensif (DB, Redis, Disk space).

### Phase 2: Feature Enhancements
- **Face Recognition**: Integrasi `face-api.js` untuk validasi wajah saat clock-in.
- **Geofencing**: Membatasi absensi hanya dalam radius kantor menggunakan Google Maps API.
- **Shift Management**: Fitur pengaturan shift kerja (Pagi, Siang, Malam).
- **PWA Support**: Menjadikan web app bisa diinstall di HP (Progressive Web App).

### Phase 3: Infrastructure Upgrades
- **Traefik Load Balancer**: Mengganti Nginx dengan Traefik untuk fitur auto-discovery dan dashboard monitoring.
- **CI/CD Pipeline**: Otomatisasi testing dan deployment menggunakan GitHub Actions.
- **Kubernetes**: Migrasi dari Docker Swarm ke K8s jika skala user mencapai ribuan.

### Phase 4: User Experience
- **Dark Mode**: Dukungan tema gelap native.
- **Push Notifications**: Notifikasi pengingat absen via browser push API.
- **Multi-language**: Dukungan Bahasa Inggris dan Indonesia.

---

## �📚 Documentation

Comprehensive documentation available in [docs/](./docs) folder:

- **[00-INDEX.md](./docs/00-INDEX.md)** - Documentation index
- **[README.md](./docs/README.md)** - Documentation overview
- **[GETTING-STARTED.md](./docs/GETTING-STARTED.md)** - Installation & usage guide
- **[TESTING-DISTRIBUTED.md](./docs/TESTING-DISTRIBUTED.md)** - Testing procedures & verification
- **[DOCKER-SWARM-DEPLOYMENT.md](./docs/DOCKER-SWARM-DEPLOYMENT.md)** - Production deployment
- **[IMPLEMENTATION-PROGRESS.md](./docs/IMPLEMENTATION-PROGRESS.md)** - Development progress report
- **[PROJECT-COMPLETION.md](./docs/PROJECT-COMPLETION.md)** - Project completion summary

---

## 🔍 Monitoring & Troubleshooting

Untuk panduan lengkap monitoring logs, cek status container, dan troubleshooting masalah umum, silakan lihat:
👉 **[docs/CHEAT-SHEET.md](./docs/CHEAT-SHEET.md)**

---

---

## 🏗️ Project Structure

```
Sistem-Absensi/
├── app/                     # Laravel application
│   ├── Console/
│   ├── Events/              # Broadcast events
│   ├── Http/
│   │   └── Controllers/
│   ├── Models/
│   └── Services/
├── resources/
│   ├── views/               # Blade templates
│   ├── js/                  # Alpine.js components
│   └── css/                 # Tailwind CSS
├── database/
│   ├── migrations/
│   └── seeders/
├── docker/                  # Docker environment files
├── nginx/                   # Nginx configuration
├── docs/                    # 📚 Documentation
├── docker-compose.yml       # Development deployment
├── docker-stack-production.yml  # Production (Swarm)
├── Dockerfile               # Application image
└── README.md                # This file
```

---

## 📈 Performance

- **Load Distribution**: Even distribution across 4 nodes via Nginx `ip_hash`
- **WebSocket Latency**: < 100ms (local network)
- **Database Queries**: Optimized with eager loading & indexing
- **Concurrent Users**: Tested with multiple simultaneous connections
- **Failover**: System remains operational with 1 node failure

---

## 🔐 Security Notes

- Environment variables managed via `.env` files
- Database credentials secured in Docker secrets (production)
- CORS configured for WebSocket connections
- Session management via Redis
- HTTPS ready (requires SSL certificate setup)

---

## 🤝 Contributing

This is a demonstration project for learning distributed systems architecture. 

For production deployment:
1. Update environment variables with secure credentials
2. Setup SSL/TLS certificates for HTTPS
3. Configure firewall rules
4. Implement monitoring & alerting
5. Regular backup procedures

---

## 📄 License

This project is open-source and available for educational purposes.

---

## 🆘 Support

For issues or questions:
1. Check [Documentation](./docs)
2. Review [Troubleshooting Guide](./docs/GETTING-STARTED.md#troubleshooting)
3. View logs: `docker-compose logs -f`

---

## 🎯 Project Status

✅ **Production Ready**
- Multi-node deployment tested
- Real-time features operational
- Load balancing verified
- Comprehensive documentation

**Version**: 1.0.0  
**Last Updated**: 2025-11-20  
**Status**: 🟢 Fully Operational

---

<div align="center">

**Built with ❤️ using Laravel 12 & Laravel Reverb**

</div>
