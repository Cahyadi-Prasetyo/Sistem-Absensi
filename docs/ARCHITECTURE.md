# 🏗️ System Architecture

Understanding the Laravel Reverb Absensi system architecture.

---

## 📊 System Overview

Laravel Reverb Absensi adalah sistem absensi real-time terdistribusi yang menggunakan:
- **Laravel 12** sebagai backend framework
- **Laravel Reverb** untuk WebSocket real-time
- **Vue 3 + Inertia.js** untuk frontend
- **Docker** untuk containerization & distributed system
- **Redis** untuk broadcasting & caching
- **MySQL** untuk database

---

## 🏛️ Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                  Load Balancer (Nginx:80)                   │
│              WebSocket Proxy (/app, /apps)                  │
└─────────────────────────────────────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
   ┌────▼────┐          ┌────▼────┐          ┌────▼────┐
   │  App 1  │          │  App 2  │          │  App 3  │
   │ Node 1  │          │ Node 2  │          │ Node 3  │
   │ :9000   │          │ :9000   │          │ :9000   │
   └────┬────┘          └────┬────┘          └────┬────┘
        │                     │                     │
        └─────────────────────┼─────────────────────┘
                              │
                    ┌─────────┴─────────┐
                    │                   │
              ┌─────▼─────┐       ┌────▼────┐
              │   Redis   │       │  MySQL  │
              │   :6379   │       │  :3306  │
              └─────┬─────┘       └─────────┘
                    │
              ┌─────▼─────┐
              │  Reverb   │
              │   :8080   │
              └─────┬─────┘
                    │
              ┌─────▼─────┐
              │   Queue   │
              │  Worker   │
              └───────────┘
```

---

## 🔄 Real-Time Flow

### Check-In Flow

```
User A (Browser) → Check-in Request
    ↓
App Instance (Random: 1, 2, or 3) → Validate & Store to MySQL
    ↓
Dispatch AttendanceCreated Event
    ↓
Redis (Broadcasting Queue)
    ↓
Reverb Server (WebSocket)
    ↓
All Connected Clients (User A, B, C...)
    ↓
Vue Component Auto-Update UI ✨
```

### Data Flow

1. **User Action** - User clicks "Check In"
2. **HTTP Request** - Sent to Nginx load balancer
3. **Load Balancing** - Nginx routes to one of 3 app instances
4. **Processing** - Laravel validates and stores to MySQL
5. **Event Dispatch** - AttendanceCreated event dispatched
6. **Broadcasting** - Event pushed to Redis queue
7. **WebSocket** - Reverb broadcasts to all connected clients
8. **UI Update** - Vue components receive event and update UI

---

## 🛠️ Technology Stack

### Backend

| Technology | Version | Purpose |
|------------|---------|---------|
| Laravel | 12 | PHP Framework |
| Laravel Reverb | Latest | WebSocket Server |
| Laravel Fortify | Latest | Authentication + 2FA |
| PHP | 8.2+ | Programming Language |
| MySQL | 8.0 | Database |
| Redis | 7 | Cache & Broadcasting |

### Frontend

| Technology | Version | Purpose |
|------------|---------|---------|
| Vue.js | 3 | JavaScript Framework |
| TypeScript | Latest | Type Safety |
| Inertia.js | Latest | Modern Monolith |
| Tailwind CSS | Latest | Utility-First CSS |
| Reka UI | Latest | Component Library |
| Vite | Latest | Build Tool |

### Infrastructure

| Technology | Version | Purpose |
|------------|---------|---------|
| Docker | Latest | Containerization |
| Docker Compose | Latest | Orchestration |
| Nginx | Alpine | Load Balancer & Proxy |

---

## 🎯 Design Decisions

### Why Vue 3 + Inertia.js?

**Chosen over Livewire because:**

1. **Real-Time Performance**
   - WebSocket connection langsung dari browser
   - Tidak ada HTTP overhead untuk setiap update
   - Client-side rendering = instant UI updates

2. **Distributed System Friendly**
   - Stateless - tidak ada session state di server
   - Load balancer friendly (sticky session tidak diperlukan)
   - Horizontal scaling lebih mudah

3. **Developer Experience**
   - TypeScript support
   - Component reusability tinggi
   - Rich ecosystem (VueUse, Pinia, dll)
   - Better tooling (Vite, Vue DevTools)

4. **Scalability**
   - Horizontal scaling mudah
   - Tidak butuh sticky sessions
   - Client-side state management

### Why Docker?

1. **Consistency** - Same environment everywhere
2. **Scalability** - Easy to scale horizontally
3. **Isolation** - Services isolated from each other
4. **Portability** - Run anywhere

### Why 3 App Instances?

1. **High Availability** - If one fails, others continue
2. **Load Distribution** - Spread load across instances
3. **Zero Downtime** - Can update one at a time
4. **Testing** - Verify load balancing works

---

## 📦 Services Architecture

### 1. Nginx (Load Balancer)

**Purpose:** Distribute traffic across app instances

**Configuration:**
- Algorithm: `least_conn` (least connections)
- Health checks: Automatic
- WebSocket proxy: `/app` and `/apps` endpoints

**Ports:**
- 80 (HTTP)
- 443 (HTTPS - for production)

### 2. Laravel App Instances (3x)

**Purpose:** Handle HTTP requests and business logic

**Features:**
- Stateless design
- Shared codebase via volumes
- Individual NODE_ID for tracking

**Ports:**
- 9000 (PHP-FPM)

### 3. Laravel Reverb (WebSocket)

**Purpose:** Real-time broadcasting

**Features:**
- Native Laravel WebSocket server
- Handles all WebSocket connections
- Broadcasts events to connected clients

**Ports:**
- 8080 (WebSocket)

### 4. MySQL (Database)

**Purpose:** Persistent data storage

**Features:**
- InnoDB engine
- Health checks enabled
- Volume persistence

**Ports:**
- 3306

### 5. Redis (Cache & Broadcasting)

**Purpose:** Cache and message broker

**Features:**
- In-memory data store
- Broadcasting queue
- Session storage (optional)

**Ports:**
- 6379

### 6. Queue Worker

**Purpose:** Process background jobs

**Features:**
- Processes queued jobs
- Handles failed jobs
- Automatic restart on failure

---

## 🗄️ Database Schema

### Tables

1. **users**
   - id, name, email, password
   - role, employee_id, department
   - two_factor_secret, two_factor_recovery_codes

2. **attendances**
   - id, user_id
   - check_in, check_out
   - check_in_location, check_out_location (JSON)
   - check_in_photo, check_out_photo
   - status (present, late, absent, leave)
   - notes, node_id

3. **attendance_settings**
   - work_start_time, work_end_time
   - late_tolerance, location_radius
   - require_photo, require_location
   - office_locations (JSON)

4. **leaves**
   - id, user_id
   - start_date, end_date
   - reason, status
   - approved_by, rejection_reason

### Relationships

```
User
├── hasMany → Attendances
└── hasMany → Leaves

Attendance
└── belongsTo → User

Leave
├── belongsTo → User
└── belongsTo → User (approver)
```

---

## 🔌 API Endpoints

### Authentication
- POST `/login` - Login
- POST `/logout` - Logout
- POST `/register` - Register (if enabled)

### Attendance
- GET `/attendances` - List attendances
- POST `/attendances` - Check-in
- PUT `/attendances/{id}` - Check-out
- GET `/attendances/{id}` - Detail
- GET `/attendances/today/me` - Today's attendance

### Dashboard
- GET `/dashboard` - Dashboard page
- GET `/dashboard/live-stats` - Live statistics

---

## 📡 Broadcasting Channels

### Public Channels

**attendances**
- Purpose: Broadcast all attendance updates
- Events: `attendance.created`, `attendance.updated`
- Listeners: All authenticated users

### Private Channels

**user.{id}**
- Purpose: Personal notifications
- Events: `attendance.created`, `attendance.updated`
- Listeners: Specific user only

---

## 🔐 Security Architecture

### Authentication Flow

1. User submits credentials
2. Laravel Fortify validates
3. Session created
4. 2FA verification (if enabled)
5. Access granted

### Authorization

- Middleware: `auth`, `verified`
- Policies: User can only modify own attendance
- CSRF protection enabled
- XSS protection via Vue escaping

### Data Security

- Passwords: Bcrypt hashed
- Sessions: Encrypted
- Database: Prepared statements (Eloquent)
- WebSocket: Authenticated connections

---

## 📈 Scalability Considerations

### Horizontal Scaling

**Current:** 3 app instances  
**Can scale to:** 10+ instances

**How to scale:**
```yaml
# docker-compose.yml
services:
  app_4:
    # Same config as app_1
  app_5:
    # Same config as app_1
```

### Vertical Scaling

**Resources per service:**
- App: 512MB RAM, 0.5 CPU
- MySQL: 1GB RAM, 1 CPU
- Redis: 256MB RAM, 0.25 CPU
- Reverb: 512MB RAM, 0.5 CPU

### Performance Metrics

- **Concurrent Users:** 100+ tested
- **WebSocket Connections:** 1000+ supported
- **Response Time:** < 200ms average
- **Broadcast Latency:** < 500ms

---

## 🔮 Future Architecture Enhancements

1. **Microservices** - Split into smaller services
2. **Message Queue** - RabbitMQ for complex workflows
3. **CDN** - CloudFlare for static assets
4. **Monitoring** - Prometheus + Grafana
5. **Logging** - ELK Stack (Elasticsearch, Logstash, Kibana)
6. **Caching** - Varnish for HTTP caching

---

**Next:** [Development Guide](DEVELOPMENT.md) for development workflow
