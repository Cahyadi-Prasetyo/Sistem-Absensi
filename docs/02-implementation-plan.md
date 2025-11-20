# Master Implementation Plan: Sistem Absensi Distributed Production-Ready

**Project:** Laravel Reverb Absensi - Distributed Real-Time System  
**Author:** Implementation dari Brainstorming Session  
**Date:** 2025-11-20  
**Estimated Timeline:** 15-20 jam (development + testing)

---

## 📋 Executive Summary

Dari hasil brainstorming, sistem absensi memiliki **3 masalah utama** yang perlu diperbaiki:

1. **Queue Worker tidak berfungsi** → Broadcast ke Reverb gagal
2. **Multi-node deployment gagal** → Hanya 1 dari 4 nodes yang running
3. **Halaman riwayat tidak real-time** → Butuh refresh manual

**Solusi yang akan diimplementasikan:**

- ✅ Fix Docker Compose untuk development multi-node
- ✅ Implementasi hybrid tab system di halaman riwayat (real-time + pagination)
- ✅ Migration ke Docker Swarm untuk production dengan proper queue workers
- ✅ Reverb scaling configuration untuk high availability

---

## 🎯 Goals & Success Criteria

### Goals
1. Semua 4 app nodes berjalan stabil di Docker Compose
2. Queue worker processing broadcast jobs dengan benar
3. Halaman riwayat admin update real-time tanpa refresh
4. Sistem siap deploy ke Docker Swarm untuk production
5. WebSocket connection stabil via Nginx load balancer

### Success Criteria
- [ ] `docker-compose ps` menunjukkan 4 app nodes healthy
- [ ] Real-time update muncul di admin dashboard dari node berbeda
- [ ] Halaman riwayat tab "Hari Ini" update otomatis
- [ ] Queue jobs processed dengan success rate > 99%
- [ ] Docker Swarm deployment dengan zero downtime updates
- [ ] Load testing: 100 concurrent users tanpa lag

---

## 📊 Current State Analysis

### Arsitektur Saat Ini

```
Browser → Nginx → [app-node-1, 2, 3, 4] → MySQL
                         ↓
                    Redis Pub/Sub
                         ↓
                  [subscriber-1,2,3,4] → broadcast() → Queue → ???
                                                                ↓
                                                            Reverb (WebSocket)
```

### Identified Problems

| # | Problem | Impact | Root Cause |
|---|---------|--------|------------|
| 1 | Queue worker missing | Broadcast tidak sampai Reverb | Tidak ada service yang run `queue:work` |
| 2 | Hanya 1 app-node running | Tidak truly distributed | Volume conflicts, migration conflicts |
| 3 | Riwayat tidak real-time | Poor UX | Tidak ada WebSocket listener |
| 4 | Reverb tidak scalable | Single point of failure | Tidak enable Redis scaling |

---

## 🚀 Implementation Phases

## Phase 1: Problem Diagnosis & Quick Fixes (2 jam)

### 1.1 Diagnose Queue Worker Issue

**Current Situation:**
```yaml
# docker-compose.yml - TIDAK ADA queue worker service!
# Hanya ada: app-node-1...4, subscriber-1...4, reverb
```

**Investigation Steps:**
```bash
# 1. Check if broadcast jobs are queued
docker-compose exec app-node-1 php artisan queue:failed

# 2. Check Redis queue
docker-compose exec redis redis-cli
> KEYS queue*
> LLEN queues:default

# 3. Manual test queue worker
docker-compose exec app-node-1 php artisan queue:work --once --verbose
```

**Expected Findings:**
- Jobs stuck in Redis queue
- No active worker consuming jobs
- `AttendanceCreated` events not reaching Reverb

### 1.2 Test Multi-Node Health

```bash
# Check all nodes status
docker-compose ps

# Check logs for each node
docker-compose logs app-node-1 | grep ERROR
docker-compose logs app-node-2 | grep ERROR
docker-compose logs app-node-3 | grep ERROR
docker-compose logs app-node-4 | grep ERROR

# Common errors to look for:
# - "Migration failed"
# - "Permission denied on storage"
# - "Database connection timeout"
```

**Deliverables:**
- [ ] Document semua error messages
- [ ] Screenshot `docker-compose ps` output
- [ ] List failed queue jobs

---

## Phase 2: Fix Docker Compose Multi-Node (4 jam)

### 2.1 Fix docker-compose.yml

**Changes Required:**

#### A. Add Dedicated Migration Service

```yaml
# NEW SERVICE
migration:
  build:
    context: .
    dockerfile: Dockerfile
  command: sh -c "php artisan migrate --force && php artisan db:seed --class=ResetDatabaseSeeder --force"
  env_file:
    - docker/.env.docker
  environment:
    APP_NODE_ID: migration-runner
  depends_on:
    mysql:
      condition: service_healthy
    redis:
      condition: service_healthy
  networks:
    - absensi-network
  restart: "no"  # Run once only
```

#### B. Add Queue Worker Service

```yaml
# NEW SERVICE - CRITICAL untuk broadcast!
queue-worker:
  build:
    context: .
    dockerfile: Dockerfile
  command: php artisan queue:work redis --tries=3 --timeout=90 --sleep=3 --verbose
  env_file:
    - docker/.env.docker
  environment:
    APP_NODE_ID: queue-worker
    QUEUE_CONNECTION: redis
    BROADCAST_CONNECTION: reverb
  volumes:
    - ./storage/logs/queue:/var/www/html/storage/logs
  depends_on:
    migration:
      condition: service_completed_successfully
    redis:
      condition: service_healthy
    reverb:
      condition: service_started
  restart: unless-stopped
  networks:
    - absensi-network
```

#### C. Update App Nodes - Separate Volumes

```yaml
app-node-1:
  # ... existing config ...
  environment:
    APP_NODE_ID: app-node-1
    SKIP_MIGRATION: "true"  # NEW
    SKIP_CACHE: "true"      # NEW
  volumes:
    - ./storage/logs/node-1:/var/www/html/storage/logs  # CHANGED
    - public_data:/var/www/html/public
  depends_on:
    migration:
      condition: service_completed_successfully  # CHANGED

# Repeat for app-node-2, 3, 4 with different log paths
```

#### D. Update Redis Subscribers

```yaml
subscriber-node-1:
  # ... existing config ...
  environment:
    APP_NODE_ID: subscriber-node-1
    QUEUE_CONNECTION: sync  # CRITICAL: Prevent queue loop!
    SKIP_MIGRATION: "true"
  volumes:
    - ./storage/logs/sub-1:/var/www/html/storage/logs  # CHANGED
```

### 2.2 Update docker-entrypoint.sh

**File:** `docker-entrypoint.sh`

```bash
#!/bin/sh
set -e

echo "🚀 Starting Laravel application..."
echo "📍 Node ID: ${APP_NODE_ID:-unknown}"

# Wait for MySQL
if [ -n "$DB_HOST" ]; then
    echo "⏳ Waiting for MySQL..."
    max_attempts=60
    attempt=0
    
    until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT:-3306};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" > /dev/null 2>&1; do
        attempt=$((attempt + 1))
        if [ $attempt -ge $max_attempts ]; then
            echo "❌ Database timeout"
            exit 1
        fi
        echo "⏳ DB unavailable - ${attempt}/${max_attempts}"
        sleep 2
    done
    echo "✅ Database ready!"
fi

# Wait for Redis
if [ -n "$REDIS_HOST" ]; then
    echo "⏳ Waiting for Redis..."
    max_attempts=30
    attempt=0
    
    until php -r "try { \$r = new Redis(); \$r->connect('${REDIS_HOST}', ${REDIS_PORT:-6379}); echo 'OK'; } catch (Exception \$e) { exit(1); }" > /dev/null 2>&1; do
        attempt=$((attempt + 1))
        if [ $attempt -ge $max_attempts ]; then
            echo "⚠️ Redis timeout, continuing..."
            break
        fi
        echo "⏳ Redis unavailable - ${attempt}/${max_attempts}"
        sleep 1
    done
    echo "✅ Redis ready!"
fi

# SKIP migration if flagged
if [ "${SKIP_MIGRATION}" != "true" ]; then
    if [ "${APP_NODE_ID}" = "migration-runner" ]; then
        echo "🔄 Running migrations..."
        php artisan migrate --force || exit 1
        echo "🌱 Seeding database..."
        php artisan db:seed --class=ResetDatabaseSeeder --force || echo "⚠️ Seeding skipped"
    fi
fi

# SKIP caching if flagged (prevent file conflicts)
if [ "${SKIP_CACHE}" != "true" ]; then
    echo "🧹 Clearing caches..."
    php artisan config:clear || true
    php artisan cache:clear || true
fi

echo "✅ Application ready!"

# Execute command
if [ $# -gt 0 ]; then
    echo "▶️ Executing: $@"
    exec "$@"
else
    echo "▶️ Starting PHP-FPM..."
    exec php-fpm
fi
```

### 2.3 Create Separate Log Directories

```bash
# On host machine
mkdir -p storage/logs/{node-1,node-2,node-3,node-4,sub-1,sub-2,sub-3,sub-4,queue,reverb}
chmod -R 775 storage/logs
```

### 2.4 Testing Multi-Node

```bash
# 1. Clean slate
docker-compose down -v
rm -rf storage/logs/*

# 2. Rebuild
docker-compose build --no-cache

# 3. Start services
docker-compose up -d

# 4. Watch logs
docker-compose logs -f

# 5. Verify all healthy
docker-compose ps

# Expected: ALL services should be "Up (healthy)"
```

**Success Criteria:**
- [ ] All 4 app nodes show status "Up (healthy)"
- [ ] Queue worker consuming jobs
- [ ] No ERROR in any logs
- [ ] Each node has separate log files

**Deliverables:**
- [ ] Updated `docker-compose.yml`
- [ ] Updated `docker-entrypoint.sh`
- [ ] Test report with screenshots

---

## Phase 3: Real-Time Riwayat Implementation (5 jam)

### 3.1 Backend API (Optional Enhancement)

**File:** `app/Http/Controllers/Admin/RiwayatController.php`

Add method untuk fetch today's data as JSON:

```php
public function todayJson(Request $request)
{
    $attendances = Attendance::with('user')
        ->whereDate('date', today())
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'user' => [
                    'id' => $attendance->user->id,
                    'name' => $attendance->user->name,
                ],
                'date' => $attendance->date->format('Y-m-d'),
                'jam_masuk' => $attendance->jam_masuk?->format('H:i:s'),
                'jam_pulang' => $attendance->jam_pulang?->format('H:i:s'),
                'status' => $attendance->status,
                'duration_minutes' => $attendance->duration_minutes,
            ];
        });

    return response()->json([
        'success' => true,
        'data' => $attendances,
    ]);
}
```

**Route:** `routes/web.php`
```php
Route::get('/api/attendances/today', [RiwayatController::class, 'todayJson'])
    ->name('api.attendances.today')
    ->middleware('auth');
```

### 3.2 Frontend: Update Riwayat View

**File:** `resources/views/admin/riwayat.blade.php`

Complete rewrite dengan hybrid tab system.

**Structure:**
```html
<div x-data="riwayatPage()" x-init="init()">
    <!-- Tab Navigation -->
    <div class="tabs">
        <button @click="activeTab = 'today'">Hari Ini</button>
        <button @click="activeTab = 'history'">
            Riwayat 
            <span x-show="hasNewUpdates" class="badge">New</span>
        </button>
    </div>

    <!-- Tab Content: Hari Ini (Real-time) -->
    <div x-show="activeTab === 'today'">
        <!-- WebSocket status indicator -->
        <span :class="reverbConnected ? 'badge-green' : 'badge-red'">
            Live • <span x-text="reverbConnected ? 'Connected' : 'Disconnected'"></span>
        </span>
        
        <!-- Real-time table -->
        <template x-for="att in todayAttendances" :key="att.id">
            <!-- Attendance rows -->
        </template>
    </div>

    <!-- Tab Content: Riwayat (Static with pagination) -->
    <div x-show="activeTab === 'history'">
        <!-- Existing pagination table -->
        @include('admin.partials.riwayat-table')
    </div>
</div>

@push('scripts')
<script>
function riwayatPage() {
    return {
        activeTab: 'today',
        todayAttendances: [],
        hasNewUpdates: false,
        reverbConnected: false,
        
        init() {
            this.loadTodayData();
            setTimeout(() => {
                this.checkReverbConnection();
                this.listenToEvents();
            }, 500);
        },
        
        async loadTodayData() {
            const res = await fetch('/api/attendances/today');
            const data = await res.json();
            if (data.success) {
                this.todayAttendances = data.data;
            }
        },
        
        checkReverbConnection() {
            // Same as dashboard.blade.php
        },
        
        listenToEvents() {
            window.Echo.channel('attendances')
                .listen('AttendanceCreated', (event) => {
                    // Only for today's date
                    if (event.date === this.getCurrentDate()) {
                        this.todayAttendances.unshift(event);
                        this.showNotification('New attendance');
                    }
                    
                    // Set badge for history tab
                    if (this.activeTab !== 'history') {
                        this.hasNewUpdates = true;
                    }
                })
                .listen('AttendanceUpdated', (event) => {
                    // Update existing
                    const index = this.todayAttendances.findIndex(a => a.id === event.id);
                    if (index !== -1) {
                        this.todayAttendances[index] = event;
                    }
                });
        },
        
        getCurrentDate() {
            return new Date().toISOString().split('T')[0];
        }
    }
}
</script>
@endpush
```

**Testing:**
```bash
# 1. Run development
docker-compose up -d

# 2. Login as admin
# 3. Open halaman riwayat
# 4. Test absensi dari karyawan
# 5. Verify real-time update di tab "Hari Ini"
# 6. Verify badge notification di tab "Riwayat"
```

**Success Criteria:**
- [ ] Tab switching works smoothly
- [ ] Real-time Hari Ini menunjukkan WebSocket status
- [ ] Absensi baru muncul otomatis di tab Hari Ini
- [ ] Badge "New" muncul di tab Riwayat
- [ ] Pagination tetap berfungsi di tab Riwayat

**Deliverables:**
- [ ] Updated `riwayat.blade.php` dengan tab system
- [ ] API endpoint `/api/attendances/today`
- [ ] Demo video real-time update

---

## Phase 4: Docker Swarm Migration (6 jam)

### 4.1 Create Production Stack File

**File:** `docker-stack-production.yml`

(Full content already provided in `docker_swarm_architecture.md`)

Key highlights:
- Apache service: replicas 4-10, auto-scaling
- Queue worker service: replicas 2, dedicated for broadcast
- Reverb: replicas 1 with Redis scaling enabled
- Redis subscribers: replicas 2
- Nginx: host mode for performance

### 4.2 Enable Reverb Scaling

**File:** `config/reverb.php`

```php
'scaling' => [
    'enabled' => env('REVERB_SCALING_ENABLED', false),
    'channel' => env('REVERB_SCALING_CHANNEL', 'reverb'),
],
```

**Environment:**
```env
REVERB_SCALING_ENABLED=true
REVERB_SCALING_CHANNEL=reverb
```

### 4.3 Nginx Configuration for Swarm

**File:** `nginx/nginx-swarm.conf`

```nginx
upstream reverb_backend {
    # In Swarm, use service name
    server reverb:8080;
}

upstream apache_backend {
    # Swarm will load balance automatically
    server apache:80;
}

server {
    listen 80;
    
    # WebSocket  
    location /app {
        proxy_pass http://reverb_backend;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        
        # Long timeouts
        proxy_read_timeout 7d;
        proxy_send_timeout 7d;
    }
    
    # HTTP
    location / {
        proxy_pass http://apache_backend;
    }
}
```

### 4.4 Deployment to Swarm

```bash
# 1. Initialize Swarm (if needed)
docker swarm init

# 2. Create secrets
echo "password123" | docker secret create db_password -
echo "rootpass456" | docker secret create db_root_password -

# 3. Build and push image
docker build -t registry.local/absensi:v1.0 .
docker push registry.local/absensi:v1.0

# 4. Deploy stack
export REGISTRY=registry.local
export VERSION=v1.0
export REVERB_APP_ID=123456
export REVERB_APP_KEY=your-key
export REVERB_APP_SECRET=your-secret

docker stack deploy -c docker-stack-production.yml absensi

# 5. Monitor deployment
watch docker service ls

# 6. Check logs
docker service logs absensi_apache -f
docker service logs absensi_queue-worker -f
docker service logs absensi_reverb -f
```

**Success Criteria:**
- [ ] All services show replicas running (e.g., "4/4")
- [ ] WebSocket connects via http://localhost/app
- [ ] Load balancing distributes requests evenly
- [ ] Queue jobs processed successfully
- [ ] Zero downtime during `docker stack deploy` update

**Deliverables:**
- [ ] `docker-stack-production.yml`
- [ ] `nginx-swarm.conf`
- [ ] Deployment script `deploy-swarm.sh`
- [ ] Monitoring dashboard setup

---

## Phase 5: Production Testing & Validation (3 jam)

### 5.1 Functional Testing

**Test Cases:**

| # | Test | Expected Result |
|---|------|----------------|
| 1 | Karyawan absen di node-1 | Admin di node-3 lihat real-time |
| 2 | Admin buka riwayat "Hari Ini" | Data load, WebSocket connected |
| 3 | Karyawan checkout | Jam pulang update otomatis |
| 4 | Admin di tab "Riwayat" | Badge "New" muncul saat ada absensi |
| 5 | Restart 1 apache replica | No downtime, request tetap handled |
| 6 | Restart Reverb | Auto-reconnect clients |
| 7 | Fill queue dengan 100 jobs | All processed dalam < 1 menit |

### 5.2 Load Testing

```bash
# Using Apache Bench
ab -n 1000 -c 100 http://localhost/

# Using k6 for WebSocket
k6 run load-test-websocket.js
```

**Target Metrics:**
- Response time p95: < 500ms
- WebSocket connection success rate: > 99%
- Queue processing time per job: < 1s
- Zero errors during 1000 concurrent requests

### 5.3 Failover Testing

```bash
# Test 1: Kill apache replica
docker service scale absensi_apache=3
# Expected: Traffic redistributed, zero errors

# Test 2: Kill queue worker
docker kill <queue-worker-container>
# Expected: Auto-restart, pending jobs resumed

# Test 3: Kill Reverb
docker kill <reverb-container>
# Expected: Clients reconnect automatically
```

**Deliverables:**
- [ ] Test report dengan all test cases passed
- [ ] Load testing results
- [ ] Failover test screenshots

---

## 📦 Deliverables Summary

### Code Changes
- [ ] `docker-compose.yml` - Multi-node fixes
- [ ] `docker-entrypoint.sh` - Migration & cache handling
- [ ] `docker-stack-production.yml` - Swarm deployment
- [ ] `nginx/nginx-swarm.conf` - Load balancer config
- [ ] `resources/views/admin/riwayat.blade.php` - Real-time tabs
- [ ] `app/Http/Controllers/Admin/RiwayatController.php` - API endpoint
- [ ] `config/reverb.php` - Scaling configuration

### Documentation
- [ ] `README.md` - Updated dengan Swarm instructions
- [ ] `docs/TROUBLESHOOTING.md` - Common issues & fixes
- [ ] `docs/DEPLOYMENT.md` - Step-by-step deployment guide
- [ ] `docs/MONITORING.md` - How to monitor production

### Scripts
- [ ] `scripts/deploy-swarm.sh` - Automated deployment
- [ ] `scripts/test-multi-node.sh` - Multi-node health check
- [ ] `scripts/backup-db.sh` - Database backup

---

## ⚠️ Risks & Mitigation

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| WebSocket disconnect during deployment | Medium | High | Blue-green deployment, gradual rollout |
| Queue jobs lost during crash | Low | High | Redis persistence, job retry logic |
| Database migration conflict | Medium | Critical | Dedicated migration service, run once |
| Reverb not scalable beyond 1000 connections | Low | Medium | Enable Redis scaling, monitor connections |
| Multi-node log conflicts | High | Low | Separate log volumes per node |

---

## 📈 Timeline & Resources

| Phase | Duration | Dependencies | Resources Needed |
|-------|----------|--------------|------------------|
| Phase 1 | 2 jam | None | Developer + Docker |
| Phase 2 | 4 jam | Phase 1 | Developer |
| Phase 3 | 5 jam | Phase 2 | Developer + Tester |
| Phase 4 | 6 jam | Phase 2,3 | Developer + DevOps |
| Phase 5 | 3 jam | Phase 4 | QA + DevOps |

**Total:** 20 jam (~2.5 workdays)

---

## ✅ Final Checklist

### Before Going Live
- [ ] All automated tests passing
- [ ] Load testing completed
- [ ] Backup strategy implemented
- [ ] Monitoring dashboard configured
- [ ] Rollback plan documented
- [ ] Team trained on new deployment

### Post-Deployment
- [ ] Monitor error rates first 24 hours
- [ ] Check queue job success rate
- [ ] Verify WebSocket connection stability
- [ ] Collect user feedback
- [ ] Performance baseline recorded

---

**Status:** Ready for implementation - Awaiting approval to proceed

**Next Step:** Review plan → Approve → Start Phase 1
