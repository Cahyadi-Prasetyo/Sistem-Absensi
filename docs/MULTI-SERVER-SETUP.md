# Multi-Server Setup Documentation

## Overview
Sistem absensi ini dikonfigurasi untuk berjalan dengan multiple application servers untuk load balancing dan high availability.

## Architecture

```
                    ┌─────────────┐
                    │   Nginx     │
                    │ Load Balancer│
                    │  Port: 8000 │
                    └──────┬──────┘
                           │
        ┌──────────────────┼──────────────────┐
        │                  │                  │
   ┌────▼────┐       ┌────▼────┐       ┌────▼────┐
   │App Node1│       │App Node2│       │App Node3│
   │Jakarta  │       │Bandung  │       │Surabaya │
   └────┬────┘       └────┬────┘       └────┬────┘
        │                  │                  │
        └──────────────────┼──────────────────┘
                           │
                    ┌──────▼──────┐
                    │   MySQL     │
                    │   Redis     │
                    │   Reverb    │
                    └─────────────┘
```

## Components

### 1. Application Nodes (4 nodes)
- **app-node-1** (Jakarta)
- **app-node-2** (Bandung)
- **app-node-3** (Surabaya)
- **app-node-4** (Bali)

Each node:
- Runs PHP-FPM
- Has unique `APP_NODE_ID`
- Shares same database and cache
- Records node_id in attendance records

### 2. Load Balancer (Nginx)
- Port: 8000
- Algorithm: Round-robin
- Health checks enabled
- Distributes traffic across all app nodes

### 3. Shared Services
- **MySQL**: Centralized database
- **Redis**: Shared cache and session storage
- **Reverb**: WebSocket server for real-time broadcasting

### 4. Background Workers
- **Queue Worker**: Processes background jobs
- **Scheduler**: Runs scheduled tasks
- **Subscriber Nodes** (4): Listen to Redis pub/sub for cross-node communication

## Configuration Files

### docker-compose.yml
Main orchestration file defining all services and their relationships.

Key sections:
- `app-node-1` to `app-node-4`: Application servers
- `nginx`: Load balancer
- `mysql`, `redis`, `reverb`: Shared services
- `queue-worker`, `scheduler`: Background workers
- `subscriber-node-1` to `subscriber-node-4`: Event subscribers

### docker/.env.docker
Environment variables for Docker containers:
```bash
APP_NODE_ID=app-node-1  # Unique per node
DB_HOST=mysql
REDIS_HOST=redis
REVERB_HOST=reverb
BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=redis
CACHE_STORE=redis
```

### nginx/nginx.conf
Load balancer configuration:
```nginx
upstream app_servers {
    server app-node-1:9000;
    server app-node-2:9000;
    server app-node-3:9000;
    server app-node-4:9000;
}
```

## Server Heartbeat System

Each application node sends heartbeat to Redis every 10 seconds:

```php
// app/Services/ServerStatusService.php
public function sendHeartbeat(): void
{
    $nodeId = config('app.node_id');
    $key = "server:heartbeat:{$nodeId}";
    
    Redis::setex($key, 30, json_encode([
        'node_id' => $nodeId,
        'timestamp' => now()->toIso8601String(),
        'status' => 'active',
    ]));
}
```

## Broadcasting Setup

### Reverb WebSocket Server
- Port: 8080
- Protocol: Pusher-compatible
- Handles real-time event broadcasting

### Event Flow
1. User action triggers event (e.g., attendance created)
2. Application node broadcasts event via Reverb
3. Reverb sends event to all connected WebSocket clients
4. Frontend receives event and updates UI

### Configuration
```php
// config/broadcasting.php
'reverb' => [
    'driver' => 'reverb',
    'key' => env('REVERB_APP_KEY'),
    'secret' => env('REVERB_APP_SECRET'),
    'app_id' => env('REVERB_APP_ID'),
    'options' => [
        'host' => env('REVERB_HOST'),
        'port' => env('REVERB_PORT', 8080),
        'scheme' => env('REVERB_SCHEME', 'http'),
    ],
],
```

## Deployment

### Build and Start
```bash
# Build all services
docker-compose build

# Start all services
docker-compose up -d

# Check status
docker-compose ps
```

### Scale Application Nodes
To add more nodes, duplicate app-node configuration in docker-compose.yml:
```yaml
app-node-5:
  build:
    context: .
    dockerfile: Dockerfile
  environment:
    APP_NODE_ID: app-node-5
    APP_NAME: "Absensi Node 5"
  # ... other config
```

Then add to nginx upstream:
```nginx
upstream app_servers {
    server app-node-1:9000;
    server app-node-2:9000;
    server app-node-3:9000;
    server app-node-4:9000;
    server app-node-5:9000;  # New node
}
```

### Monitoring

Check server status:
```bash
# View logs
docker-compose logs -f app-node-1

# Check health
docker-compose exec app-node-1 php artisan health:check

# View active servers
docker-compose exec app-node-1 php artisan tinker
>>> app(App\Services\ServerStatusService::class)->getActiveServers()
```

## Troubleshooting

### Node Not Receiving Traffic
1. Check nginx logs: `docker-compose logs nginx`
2. Verify node is healthy: `docker-compose ps`
3. Check nginx upstream: `docker-compose exec nginx cat /etc/nginx/nginx.conf`

### Broadcasting Not Working
1. Check Reverb is running: `docker-compose ps reverb`
2. Verify Pusher PHP SDK installed: `composer show pusher/pusher-php-server`
3. Check WebSocket connection in browser Network tab
4. Test manual broadcast: `php test-pusher-broadcast.php`

### Database Connection Issues
1. Verify MySQL is running: `docker-compose ps mysql`
2. Check connection from app node: `docker-compose exec app-node-1 php artisan tinker`
3. Test query: `DB::select('SELECT 1')`

## Performance Optimization

### Caching
All nodes share Redis cache:
```php
Cache::remember('key', 3600, function() {
    return expensive_operation();
});
```

### Session Management
Sessions stored in database for cross-node compatibility:
```php
// config/session.php
'driver' => 'database',
```

### Queue Processing
Dedicated queue worker processes background jobs:
```bash
docker-compose logs -f queue-worker
```

## Security Considerations

1. **Network Isolation**: All services in private Docker network
2. **Port Exposure**: Only nginx (8000) and reverb (8080) exposed
3. **Environment Variables**: Sensitive data in .env files (not committed)
4. **Database Access**: Only accessible from Docker network

## Backup and Recovery

### Database Backup
```bash
docker-compose exec mysql mysqldump -u absensi -p absensi > backup.sql
```

### Restore
```bash
docker-compose exec -T mysql mysql -u absensi -p absensi < backup.sql
```

## Maintenance

### Update Application
```bash
# Pull latest code
git pull

# Rebuild containers
docker-compose build

# Restart services (zero-downtime)
docker-compose up -d --no-deps --build app-node-1
docker-compose up -d --no-deps --build app-node-2
# ... etc
```

### Clear Cache
```bash
docker-compose exec app-node-1 php artisan cache:clear
docker-compose exec app-node-1 php artisan config:clear
docker-compose exec app-node-1 php artisan route:clear
```

## References

- [Laravel Broadcasting](https://laravel.com/docs/broadcasting)
- [Laravel Reverb](https://laravel.com/docs/reverb)
- [Docker Compose](https://docs.docker.com/compose/)
- [Nginx Load Balancing](https://nginx.org/en/docs/http/load_balancing.html)
