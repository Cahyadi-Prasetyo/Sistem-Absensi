# Design Document

## Overview

Sistem Absensi Real-Time Terdistribusi adalah aplikasi web berbasis Laravel 12 yang mengimplementasikan arsitektur terdistribusi dengan multiple application nodes, load balancing, dan real-time data synchronization. Sistem ini dirancang untuk mendemonstrasikan konsep distributed systems seperti eventual consistency, high availability, fault tolerance, dan real-time communication.

### Key Design Principles

1. **Stateless Application Nodes**: Semua Laravel nodes bersifat stateless, tidak menyimpan session atau state di memory
2. **Shared Database**: Single MySQL instance sebagai source of truth untuk data persistence
3. **Event-Driven Architecture**: Menggunakan event broadcasting untuk real-time synchronization
4. **Graceful Degradation**: Sistem tetap berfungsi meskipun beberapa komponen mengalami failure
5. **Clean Separation of Concerns**: Pemisahan jelas antara presentation, business logic, dan data layer

## Architecture

### System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                         Client Browser                           │
│                    (Blade + Alpine.js)                          │
└────────────┬────────────────────────────────────┬───────────────┘
             │                                    │
             │ HTTP/HTTPS                         │ WebSocket
             │                                    │
┌────────────▼────────────────────────────────────▼───────────────┐
│                      Nginx Load Balancer                         │
│                    (Round-robin distribution)                    │
└────┬──────────────┬──────────────┬─────────────────────────────┘
     │              │              │
     │              │              │
┌────▼─────┐  ┌────▼─────┐  ┌────▼─────┐      ┌──────────────┐
│ Laravel  │  │ Laravel  │  │ Laravel  │      │   Laravel    │
│ Node 1   │  │ Node 2   │  │ Node 3   │      │   Reverb     │
│ (App)    │  │ (App)    │  │ (App)    │      │ (WebSocket)  │
└────┬─────┘  └────┬─────┘  └────┬─────┘      └──────┬───────┘
     │              │              │                   │
     └──────────────┴──────────────┴───────────────────┘
                    │                                  │
     ┌──────────────▼──────────────┐    ┌─────────────▼────────┐
     │      Redis (Pub/Sub)        │    │   MySQL Database     │
     │   (Cache + Queue + Broker)  │    │  (Shared Storage)    │
     └─────────────────────────────┘    └──────────────────────┘
```

### Component Responsibilities

#### 1. Nginx Load Balancer
- Distribusi HTTP requests ke Laravel nodes menggunakan round-robin
- Health check untuk mendeteksi node yang down
- Proxy WebSocket connections ke Reverb server
- SSL termination (jika diperlukan)

#### 2. Laravel Application Nodes (3 instances)
- Handle HTTP requests (login, absensi, dashboard, API)
- Business logic processing
- Database operations (CRUD)
- Publish events ke Redis pub/sub
- Subscribe ke Redis pub/sub untuk menerima events dari node lain
- Stateless - tidak menyimpan session di memory (gunakan database/Redis session)

#### 3. Laravel Reverb (WebSocket Server)
- Maintain persistent WebSocket connections dengan clients
- Broadcast events ke connected clients
- Handle presence channels untuk tracking online users
- Separate dari application nodes untuk scalability

#### 4. Redis
- **Pub/Sub**: Message broker untuk komunikasi antar nodes
- **Cache**: Menyimpan data yang sering diakses (user sessions, config)
- **Queue**: Background job processing (email, notifications)

#### 5. MySQL Database
- Single source of truth untuk semua data
- Shared oleh semua Laravel nodes
- ACID compliance untuk data integrity

## Components and Interfaces

### 1. Authentication System

#### Components
- `LoginController`: Handle login/logout
- `AuthMiddleware`: Protect routes berdasarkan authentication
- `RoleMiddleware`: Protect routes berdasarkan role (admin/karyawan)

#### Flow
```
User → Login Form → LoginController@login
  → Validate credentials
  → Create session
  → Redirect to dashboard (admin/karyawan)
```

### 2. Absensi Module

#### Components
- `AbsensiController`: Handle absensi masuk/pulang
- `AbsensiService`: Business logic untuk absensi
- `AbsensiRepository`: Database operations
- `AbsensiEvent`: Event yang di-broadcast saat ada absensi baru
- `AbsensiListener`: Listen ke Redis pub/sub events

#### Interfaces

```php
// AbsensiService Interface
interface AbsensiServiceInterface
{
    public function clockIn(int $userId, float $latitude, float $longitude): Attendance;
    public function clockOut(int $userId, float $latitude, float $longitude): Attendance;
    public function hasClockInToday(int $userId): bool;
    public function getTodayAttendance(int $userId): ?Attendance;
    public function calculateDuration(Attendance $attendance): string;
    public function determineStatus(Attendance $attendance): string;
}

// AbsensiRepository Interface
interface AbsensiRepositoryInterface
{
    public function create(array $data): Attendance;
    public function update(int $id, array $data): Attendance;
    public function findByUserAndDate(int $userId, Carbon $date): ?Attendance;
    public function getLatestAttendances(int $limit = 10): Collection;
    public function getUserAttendances(int $userId, ?Carbon $startDate = null, ?Carbon $endDate = null): Collection;
    public function getAllAttendances(?Carbon $startDate = null, ?Carbon $endDate = null, ?string $search = null): Collection;
}
```

#### Absensi Flow

**Clock In Flow:**
```
1. User clicks "Absen Masuk"
2. Frontend captures geolocation
3. POST /absensi/masuk with {latitude, longitude}
4. AbsensiController validates request
5. AbsensiService checks if already clocked in today
6. If not, create attendance record with node_id
7. Dispatch AbsensiEvent to Redis pub/sub
8. All nodes receive event and broadcast via Reverb
9. Frontend receives WebSocket event and updates UI
10. Return success response
```

**Clock Out Flow:**
```
1. User clicks "Absen Pulang"
2. Frontend captures geolocation
3. POST /absensi/pulang with {latitude, longitude}
4. AbsensiController validates request
5. AbsensiService finds today's attendance
6. Update attendance with jam_pulang
7. Calculate duration
8. Dispatch AbsensiEvent to Redis pub/sub
9. All nodes receive event and broadcast via Reverb
10. Frontend receives WebSocket event and updates UI
11. Return success response
```

### 3. Real-time Broadcasting System

#### Components
- `AbsensiEvent`: Event class yang implements ShouldBroadcast
- `RedisEventSubscriber`: Subscribe ke Redis pub/sub channel
- `BroadcastServiceProvider`: Configure broadcasting driver

#### Broadcasting Flow
```
Node 1: Absensi created
  → Dispatch AbsensiEvent
  → Laravel broadcasts to Redis pub/sub channel
  → Redis distributes to all subscribed nodes
  
Node 2 & 3: Receive event from Redis
  → Trigger local broadcast to Reverb
  → Reverb pushes to connected WebSocket clients
  
Client Browser:
  → Receives WebSocket message
  → Alpine.js updates UI without refresh
```

#### Event Structure
```javascript
// AbsensiEvent payload
{
  "event": "AbsensiCreated",
  "data": {
    "id": 123,
    "user_id": 45,
    "user_name": "Ahmad Rizki",
    "type": "in",
    "timestamp": "2025-11-14 08:15:00",
    "latitude": -6.2088,
    "longitude": 106.8456,
    "node_id": "app-node-1",
    "status": "Hadir"
  }
}
```

### 4. Dashboard Module

#### Admin Dashboard Components
- `AdminDashboardController`: Render admin dashboard
- `DashboardService`: Aggregate statistics
- `ServerStatusService`: Check node health

#### Karyawan Dashboard Components
- `KaryawanDashboardController`: Render karyawan portal
- `AbsensiStatusService`: Get user's absensi status for today

#### Dashboard Data
```php
// Admin Dashboard Data
[
    'metrics' => [
        'today_count' => 45,
        'week_count' => 312,
        'attendance_rate' => 94,
        'servers_online' => 3,
        'servers_total' => 4
    ],
    'latest_attendances' => Collection, // 10 latest
    'server_status' => [
        ['name' => 'Jakarta', 'status' => 'online', 'last_sync' => '2 detik lalu'],
        ['name' => 'Bandung', 'status' => 'online', 'last_sync' => '5 detik lalu'],
        ['name' => 'Surabaya', 'status' => 'online', 'last_sync' => '3 detik lalu'],
        ['name' => 'Bali', 'status' => 'offline', 'last_sync' => '2 menit lalu']
    ]
]

// Karyawan Dashboard Data
[
    'user' => User,
    'today_attendance' => Attendance|null,
    'can_clock_in' => true|false,
    'can_clock_out' => true|false,
    'location' => 'Jakarta Pusat'
]
```

### 5. Riwayat Module

#### Components
- `RiwayatController`: Handle riwayat page for both roles
- `RiwayatService`: Filter and export logic
- `ExportService`: Generate CSV/Excel files

#### Authorization
```php
// Policy-based authorization
class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }
    
    public function view(User $user, Attendance $attendance): bool
    {
        return $user->isAdmin() || $user->id === $attendance->user_id;
    }
}
```

## Data Models

### Database Schema

#### users table
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'karyawan') DEFAULT 'karyawan',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_role (role)
);
```

#### attendances table
```sql
CREATE TABLE attendances (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    type ENUM('in', 'out') NOT NULL,
    date DATE NOT NULL,
    jam_masuk TIMESTAMP NULL,
    jam_pulang TIMESTAMP NULL,
    latitude_masuk DECIMAL(10, 8) NULL,
    longitude_masuk DECIMAL(11, 8) NULL,
    latitude_pulang DECIMAL(10, 8) NULL,
    longitude_pulang DECIMAL(11, 8) NULL,
    node_id VARCHAR(50) NULL,
    status ENUM('Hadir', 'Terlambat', 'Alpha') DEFAULT 'Hadir',
    duration_minutes INT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, date),
    INDEX idx_date (date),
    INDEX idx_status (status),
    UNIQUE KEY unique_user_date (user_id, date)
);
```

#### attendance_logs table (Event Sourcing)
```sql
CREATE TABLE attendance_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    attendance_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    node_id VARCHAR(50) NOT NULL,
    payload JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attendance_id) REFERENCES attendances(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_attendance (attendance_id),
    INDEX idx_event_type (event_type),
    INDEX idx_created_at (created_at)
);
```

### Eloquent Models

```php
// User Model
class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'role'];
    protected $hidden = ['password', 'remember_token'];
    
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    
    public function isKaryawan(): bool
    {
        return $this->role === 'karyawan';
    }
}

// Attendance Model
class Attendance extends Model
{
    protected $fillable = [
        'user_id', 'type', 'date', 'jam_masuk', 'jam_pulang',
        'latitude_masuk', 'longitude_masuk', 'latitude_pulang', 'longitude_pulang',
        'node_id', 'status', 'duration_minutes'
    ];
    
    protected $casts = [
        'date' => 'date',
        'jam_masuk' => 'datetime',
        'jam_pulang' => 'datetime',
        'latitude_masuk' => 'float',
        'longitude_masuk' => 'float',
        'latitude_pulang' => 'float',
        'longitude_pulang' => 'float',
        'duration_minutes' => 'integer'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function logs()
    {
        return $this->hasMany(AttendanceLog::class);
    }
    
    public function getDurationFormatted(): string
    {
        if (!$this->duration_minutes) return '-';
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        return "{$hours}j {$minutes}m";
    }
}

// AttendanceLog Model
class AttendanceLog extends Model
{
    protected $fillable = ['attendance_id', 'user_id', 'event_type', 'node_id', 'payload'];
    protected $casts = ['payload' => 'array'];
    public $timestamps = false;
    
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

## Error Handling

### Error Categories

#### 1. Validation Errors (400)
- Invalid input data
- Missing required fields
- Format errors

**Response:**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "latitude": ["The latitude field is required."]
    }
}
```

#### 2. Authorization Errors (403)
- Insufficient permissions
- Accessing other user's data

**Response:**
```json
{
    "success": false,
    "message": "Anda tidak memiliki akses ke data ini"
}
```

#### 3. Business Logic Errors (422)
- Already clocked in today
- Cannot clock out before clock in
- Duplicate absensi

**Response:**
```json
{
    "success": false,
    "message": "Anda sudah melakukan absensi masuk hari ini"
}
```

#### 4. System Errors (500)
- Database connection failure
- Redis connection failure
- Unexpected exceptions

**Response:**
```json
{
    "success": false,
    "message": "Terjadi kesalahan sistem. Silakan coba lagi."
}
```

### Error Handling Strategy

```php
// Global Exception Handler
class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $exception->errors()
            ], 422);
        }
        
        if ($exception instanceof AuthorizationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        if ($exception instanceof BusinessLogicException) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], 422);
        }
        
        // Log unexpected errors
        Log::error('Unexpected error', [
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'System error occurred'
        ], 500);
    }
}
```

### Graceful Degradation

```php
// Redis Failure Handling
try {
    Redis::publish('absensi-channel', json_encode($event));
} catch (RedisException $e) {
    Log::warning('Redis publish failed, continuing without real-time sync', [
        'error' => $e->getMessage()
    ]);
    // Continue execution - absensi still saved to database
}

// WebSocket Failure Handling (Frontend)
window.Echo.connector.socket.on('connect_error', () => {
    console.warn('WebSocket connection failed, falling back to polling');
    // Show notification to user
    showNotification('Real-time updates unavailable. Please refresh manually.');
});
```

## Testing Strategy

### 1. Unit Tests

**Target Coverage: Core business logic**

```php
// AbsensiServiceTest
class AbsensiServiceTest extends TestCase
{
    public function test_clock_in_creates_attendance_record()
    {
        $user = User::factory()->create();
        $service = app(AbsensiService::class);
        
        $attendance = $service->clockIn($user->id, -6.2088, 106.8456);
        
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => now()->toDateString()
        ]);
    }
    
    public function test_cannot_clock_in_twice_same_day()
    {
        $user = User::factory()->create();
        $service = app(AbsensiService::class);
        
        $service->clockIn($user->id, -6.2088, 106.8456);
        
        $this->expectException(BusinessLogicException::class);
        $service->clockIn($user->id, -6.2088, 106.8456);
    }
    
    public function test_status_is_terlambat_when_clock_in_after_830()
    {
        Carbon::setTestNow('2025-11-14 09:00:00');
        
        $user = User::factory()->create();
        $service = app(AbsensiService::class);
        
        $attendance = $service->clockIn($user->id, -6.2088, 106.8456);
        
        $this->assertEquals('Terlambat', $attendance->status);
    }
}
```

### 2. Feature Tests

**Target: End-to-end user flows**

```php
// AbsensiFeatureTest
class AbsensiFeatureTest extends TestCase
{
    public function test_karyawan_can_clock_in()
    {
        $user = User::factory()->karyawan()->create();
        
        $response = $this->actingAs($user)
            ->postJson('/absensi/masuk', [
                'latitude' => -6.2088,
                'longitude' => 106.8456
            ]);
        
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }
    
    public function test_karyawan_cannot_view_other_user_attendance()
    {
        $user1 = User::factory()->karyawan()->create();
        $user2 = User::factory()->karyawan()->create();
        
        $attendance = Attendance::factory()->create(['user_id' => $user2->id]);
        
        $response = $this->actingAs($user1)
            ->get("/riwayat/{$attendance->id}");
        
        $response->assertStatus(403);
    }
}
```

### 3. Integration Tests

**Target: Component interactions**

```php
// BroadcastingIntegrationTest
class BroadcastingIntegrationTest extends TestCase
{
    public function test_absensi_event_is_broadcasted_to_redis()
    {
        Redis::shouldReceive('publish')
            ->once()
            ->with('absensi-channel', Mockery::type('string'));
        
        $user = User::factory()->create();
        $service = app(AbsensiService::class);
        
        $service->clockIn($user->id, -6.2088, 106.8456);
    }
}
```

### 4. Browser Tests (Optional)

**Target: Real browser interactions with Dusk**

```php
// DashboardBrowserTest
class DashboardBrowserTest extends DuskTestCase
{
    public function test_admin_sees_real_time_updates()
    {
        $admin = User::factory()->admin()->create();
        
        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/dashboard')
                ->waitForText('Absensi Terbaru')
                ->assertSee('Live');
            
            // Trigger absensi from another session
            $this->createAbsensi();
            
            // Wait for real-time update
            $browser->waitForText('Ahmad Rizki', 5);
        });
    }
}
```

### Testing Docker Environment

```bash
# Test individual node
docker exec app-node-1 php artisan test

# Test all nodes
docker-compose exec app-node-1 php artisan test
docker-compose exec app-node-2 php artisan test
docker-compose exec app-node-3 php artisan test

# Test load balancing
for i in {1..10}; do
  curl -I http://localhost | grep "X-Node-ID"
done
```

## Frontend Architecture

### Technology Stack
- **Blade Templates**: Server-side rendering
- **Alpine.js**: Reactive components and interactivity
- **Tailwind CSS**: Utility-first styling
- **Laravel Echo**: WebSocket client
- **Axios**: HTTP requests

### Component Structure

```javascript
// Dashboard Component (Alpine.js)
Alpine.data('dashboard', () => ({
    attendances: [],
    metrics: {},
    serverStatus: [],
    
    init() {
        this.loadData();
        this.listenToEvents();
    },
    
    loadData() {
        axios.get('/api/dashboard/data')
            .then(response => {
                this.attendances = response.data.latest_attendances;
                this.metrics = response.data.metrics;
                this.serverStatus = response.data.server_status;
            });
    },
    
    listenToEvents() {
        window.Echo.channel('absensi-channel')
            .listen('AbsensiCreated', (event) => {
                this.attendances.unshift(event.data);
                this.updateMetrics();
            });
    },
    
    updateMetrics() {
        // Refresh metrics
        axios.get('/api/dashboard/metrics')
            .then(response => {
                this.metrics = response.data;
            });
    }
}));

// Absensi Component (Alpine.js)
Alpine.data('absensi', () => ({
    canClockIn: true,
    canClockOut: false,
    loading: false,
    
    async clockIn() {
        this.loading = true;
        
        try {
            const position = await this.getGeolocation();
            
            const response = await axios.post('/absensi/masuk', {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            });
            
            if (response.data.success) {
                this.canClockIn = false;
                this.canClockOut = true;
                this.showSuccess('Absensi masuk berhasil!');
            }
        } catch (error) {
            this.showError(error.response.data.message);
        } finally {
            this.loading = false;
        }
    },
    
    getGeolocation() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Geolocation not supported'));
            }
            
            navigator.geolocation.getCurrentPosition(resolve, reject);
        });
    },
    
    showSuccess(message) {
        // Show toast notification
    },
    
    showError(message) {
        // Show error notification
    }
}));
```

### WebSocket Connection

```javascript
// bootstrap.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
    
    // Auto-reconnect configuration
    reconnectionDelay: 1000,
    reconnectionDelayMax: 5000,
    reconnectionAttempts: 5
});

// Connection event handlers
window.Echo.connector.socket.on('connect', () => {
    console.log('WebSocket connected');
});

window.Echo.connector.socket.on('disconnect', () => {
    console.warn('WebSocket disconnected');
});

window.Echo.connector.socket.on('reconnect_attempt', (attempt) => {
    console.log(`Reconnection attempt ${attempt}`);
});
```

## Deployment Configuration

### Docker Compose Structure

```yaml
version: '3.8'

services:
  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
    depends_on:
      - app-node-1
      - app-node-2
      - app-node-3
    networks:
      - absensi-network

  app-node-1:
    build: .
    environment:
      - APP_NAME="Absensi Node 1"
      - NODE_ID=app-node-1
      - DB_HOST=mysql
      - REDIS_HOST=redis
    depends_on:
      - mysql
      - redis
    networks:
      - absensi-network

  app-node-2:
    build: .
    environment:
      - APP_NAME="Absensi Node 2"
      - NODE_ID=app-node-2
      - DB_HOST=mysql
      - REDIS_HOST=redis
    depends_on:
      - mysql
      - redis
    networks:
      - absensi-network

  app-node-3:
    build: .
    environment:
      - APP_NAME="Absensi Node 3"
      - NODE_ID=app-node-3
      - DB_HOST=mysql
      - REDIS_HOST=redis
    depends_on:
      - mysql
      - redis
    networks:
      - absensi-network

  reverb:
    build: .
    command: php artisan reverb:start
    environment:
      - REDIS_HOST=redis
    ports:
      - "8080:8080"
    depends_on:
      - redis
    networks:
      - absensi-network

  mysql:
    image: mysql:8.0
    environment:
      - MYSQL_ROOT_PASSWORD=secret
      - MYSQL_DATABASE=absensi
    volumes:
      - mysql-data:/var/lib/mysql
    networks:
      - absensi-network

  redis:
    image: redis:alpine
    networks:
      - absensi-network

networks:
  absensi-network:
    driver: bridge

volumes:
  mysql-data:
```

### Nginx Configuration

```nginx
upstream laravel_nodes {
    server app-node-1:9000;
    server app-node-2:9000;
    server app-node-3:9000;
}

server {
    listen 80;
    server_name localhost;

    location / {
        proxy_pass http://laravel_nodes;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location /ws {
        proxy_pass http://reverb:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
    }
}
```

## Performance Considerations

### Caching Strategy

1. **Query Result Caching**: Cache frequently accessed data
2. **Session Storage**: Use Redis for session storage
3. **Config Caching**: Cache Laravel configuration in production
4. **Route Caching**: Cache routes for faster routing

### Database Optimization

1. **Indexes**: Add indexes on frequently queried columns
2. **Query Optimization**: Use eager loading to prevent N+1 queries
3. **Connection Pooling**: Reuse database connections

### Load Balancing

1. **Round-robin**: Equal distribution of requests
2. **Health Checks**: Remove unhealthy nodes from pool
3. **Session Affinity**: Not required (stateless design)

## Security Considerations

### Authentication
- Password hashing with bcrypt
- CSRF protection on all forms
- Session timeout after inactivity

### Authorization
- Role-based access control
- Policy-based authorization for resources
- Middleware protection on routes

### Data Protection
- Input validation and sanitization
- SQL injection prevention (Eloquent ORM)
- XSS prevention (Blade escaping)

### API Security
- Rate limiting on API endpoints
- CORS configuration
- API token authentication (if needed)
