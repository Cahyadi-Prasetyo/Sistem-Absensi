# Dokumentasi Sistem Absensi Real-Time

**Distributed Attendance System with Laravel Reverb & Docker**

---

## 📚 Daftar Dokumentasi

### 🚀 Getting Started
- **[GETTING-STARTED.md](./GETTING-STARTED.md)** - Panduan lengkap instalasi dan penggunaan aplikasi dengan Docker
  - Quick start
  - Fitur aplikasi
  - Docker commands
  - Troubleshooting

### 🧪 Testing & Verification
- **[TESTING-DISTRIBUTED.md](./TESTING-DISTRIBUTED.md)** - Panduan testing sistem distributed
  - Cara cek load balancing
  - Verifikasi distribusi data
  - Test real-time features
  - Monitoring commands

### 🐳 Deployment
- **[DOCKER-SWARM-DEPLOYMENT.md](./DOCKER-SWARM-DEPLOYMENT.md)** - Guide deployment Docker Swarm untuk production
  - Swarm setup
  - Stack deployment
  - Scaling strategies
  - Production checklist

### 📝 Implementation Details
- **[IMPLEMENTATION-PROGRESS.md](./IMPLEMENTATION-PROGRESS.md)** - Laporan progress implementasi
  - Phase 1-4 completion log
  - Bug fixes applied
  - Architecture decisions

### 🏗️ Architecture
- **[01-brainstorming-session.md](./01-brainstorming-session.md)** - Sesi brainstorming awal
- **[02-implementation-plan.md](./02-implementation-plan.md)** - Master implementation plan
- **[03-docker-swarm-architecture.md](./03-docker-swarm-architecture.md)** - Arsitektur Docker Swarm
- **[04-task-checklist.md](./04-task-checklist.md)** - Task checklist

---

## 🎯 Recommended Reading Path

### Untuk User/Developer Baru:
1. **[GETTING-STARTED.md](./GETTING-STARTED.md)** ← Mulai dari sini
2. **[TESTING-DISTRIBUTED.md](./TESTING-DISTRIBUTED.md)** ← Verifikasi sistem berjalan dengan benar

### Untuk DevOps/Deployment:
1. **[DOCKER-SWARM-DEPLOYMENT.md](./DOCKER-SWARM-DEPLOYMENT.md)**
2. **[03-docker-swarm-architecture.md](./03-docker-swarm-architecture.md)**

### Untuk Technical Deep Dive:
1. **[IMPLEMENTATION-PROGRESS.md](./IMPLEMENTATION-PROGRESS.md)**
2. **[01-brainstorming-session.md](./01-brainstorming-session.md)**
3. **[02-implementation-plan.md](./02-implementation-plan.md)**

---

## 🏗️ System Architecture Overview

```
┌─────────────────────────────────────────────┐
│         Browser Clients                      │
│   (Admin Dashboard + Employee Portal)        │
└────────────┬────────────────────────────────┘
             │
             ├─ HTTP (8000) ──────────────┐
             └─ WebSocket (8081) ─────┐   │
                                       │   │
┌──────────────────────────────────┐  │   │
│        Nginx Load Balancer        │◄─┘   │
└──────────┬───────────────────────┘       │
           │                                │
    ┌──────┴──────┬──────┬──────┐          │
    │             │      │      │          │
┌───▼──┐     ┌───▼──┐ ┌─▼───┐ ┌▼────┐    │
│Node-1│     │Node-2│ │Node3│ │Node4│    │
└───┬──┘     └───┬──┘ └─┬───┘ └┬────┘    │
    │            │      │      │          │
    └────────────┴──────┴──────┘          │
                 │                         │
    ┌────────────┼─────────────┐           │
    │            │             │           │
┌───▼────┐  ┌───▼──┐    ┌────▼────┐      │
│ MySQL  │  │Redis │    │ Reverb  │◄─────┘
└────────┘  └──────┘    │WebSocket│
                         └─────────┘
                              ▲
                              │
                         Queue Worker
                         (Broadcast)
```

---

## ⚙️ Tech Stack

### Backend:
- **Laravel 12** - PHP Framework
- **MySQL 8.0** - Primary Database
- **Redis 7** - Cache, Queue, Pub/Sub
- **PHP 8.3-FPM** - Application Server

### Frontend:
- **Alpine.js 3** - Reactive UI
- **Tailwind CSS** - Styling
- **Laravel Echo** - WebSocket Client
- **Pusher JS** - Protocol library

### Infrastructure:
- **Docker & Docker Compose** - Containerization
- **Docker Swarm** - Orchestration (production)
- **Nginx** - Load Balancer & Reverse Proxy
- **Laravel Reverb** - WebSocket Server

---

## 🎯 Key Features

### Real-Time:
- ✅ Live attendance updates (no refresh needed)
- ✅ WebSocket-based communication
- ✅ Distributed event broadcasting
- ✅ Server status monitoring dengan heartbeat

### Distributed:
- ✅ Multi-node app servers (4 replicas)
- ✅ Load balancing via Nginx
- ✅ Horizontal scaling ready
- ✅ High availability architecture

### Developer-Friendly:
- ✅ Docker Compose for development
- ✅ Docker Swarm for production
- ✅ Comprehensive documentation
- ✅ Easy setup & troubleshooting

---

## 🚀 Quick Start

```bash
# Clone repository
git clone <repo-url>
cd Sistem-Absensi

# Build & Run
docker-compose build
docker-compose up -d

# Access
open http://localhost:8000
```

**Login Credentials**:
- Admin: `admin@test.com` / `password`
- Karyawan: `user@test.com` / `password`

---

## 📊 Monitoring & Health Checks

```bash
# Container status
docker-compose ps

# Logs
docker-compose logs -f

# Redis heartbeats (check all nodes alive)
docker exec -it sistemabsensi-redis-1 redis-cli KEYS "absensi_database_node:*:heartbeat"

# Database distribution
docker exec -it sistemabsensi-mysql-1 mysql -u absensi -pAbsensiSecure2025! -e "SELECT node_id, COUNT(*) FROM absensi.attendances WHERE date=CURDATE() GROUP BY node_id;"
```

Lihat **[TESTING-DISTRIBUTED.md](./TESTING-DISTRIBUTED.md)** untuk panduan lengkap.

---

## 🆘 Troubleshooting

Lihat **[GETTING-STARTED.md](./GETTING-STARTED.md)** bagian Troubleshooting untuk:
- Port conflicts
- WebSocket connection issues
- Service health problems
- Database migration errors

---

## 📞 Support

Untuk pertanyaan atau issue, silakan:
1. Check dokumentasi di folder `docs/`
2. Review logs: `docker-compose logs -f`
3. Test dengan panduan di `TESTING-DISTRIBUTED.md`

---

**Last Updated**: 2025-11-20  
**Version**: 1.0.0  
**Status**: Production Ready 🟢
