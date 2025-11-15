# Nginx Configuration Files

Konfigurasi Nginx untuk Laravel multi-node deployment.

## 📁 Files

| File | Purpose |
|------|---------|
| `laravel-absensi.conf` | Nginx config untuk server standalone |
| `start-multi-nodes.sh` | Script untuk start 3 Laravel nodes |
| `stop-multi-nodes.sh` | Script untuk stop semua nodes |
| `NGINX-SETUP.md` | Panduan lengkap setup Nginx |

## 🚀 Quick Start

### Docker (Automatic)

Nginx sudah ter-configure otomatis di Docker Compose. Cukup jalankan:

```bash
docker-compose up -d
```

### Server Standalone

1. **Copy configuration:**
```bash
sudo cp laravel-absensi.conf /etc/nginx/sites-available/
sudo ln -s /etc/nginx/sites-available/laravel-absensi /etc/nginx/sites-enabled/
```

2. **Edit configuration:**
```bash
sudo nano /etc/nginx/sites-available/laravel-absensi
```

Update:
- `server_name` → domain Anda
- `root` → path project Anda
- `upstream` ports → sesuaikan jika perlu

3. **Test & reload:**
```bash
sudo nginx -t
sudo systemctl reload nginx
```

4. **Start Laravel nodes:**
```bash
chmod +x start-multi-nodes.sh stop-multi-nodes.sh
./start-multi-nodes.sh
```

## 🏗️ Architecture

```
Client Request
      ↓
   Nginx :80
      ↓
Load Balancer (least_conn)
      ↓
   ┌──────┼──────┐
   ↓      ↓      ↓
Node-1  Node-2  Node-3
:8001   :8002   :8003
   └──────┴──────┘
         ↓
    Reverb :8080
```

## ⚙️ Configuration Features

### Load Balancing
- **Algorithm:** Least connections
- **Health checks:** Max 3 fails, 30s timeout
- **Nodes:** 3 Laravel instances

### WebSocket Support
- **Proxy:** Reverb WebSocket server
- **Path:** `/app`
- **Timeout:** 7 days (persistent connection)

### Performance
- **Gzip:** Enabled untuk text/json/js/css
- **Static caching:** 1 year untuk images/fonts
- **Keepalive:** 65 seconds
- **Client max body:** 20MB

### Security
- **Headers:** X-Frame-Options, X-XSS-Protection, etc.
- **Hidden files:** Denied (.env, .git, .htaccess)
- **Sensitive files:** Protected

## 🧪 Testing

### Test Load Balancing

```bash
# Multiple requests
for i in {1..10}; do
    curl -s http://localhost/api/health | grep node_id
done

# Should show different node IDs (node-1, node-2, node-3)
```

### Test WebSocket

```bash
# Check Reverb endpoint
curl http://localhost:8080

# Test via browser
# Open http://localhost
# Check browser console for WebSocket connection
```

### Test Health Check

```bash
# Nginx health
curl http://localhost/nginx-health

# Application health
curl http://localhost/api/health
```

## 📊 Monitoring

### View Logs

```bash
# Nginx access log
sudo tail -f /var/log/nginx/laravel-absensi-access.log

# Nginx error log
sudo tail -f /var/log/nginx/laravel-absensi-error.log

# Laravel node logs
tail -f /var/www/laravel-reverb-absensi/storage/logs/node-*.log
```

### Check Upstream Status

```bash
# Check which nodes are responding
for port in 8001 8002 8003; do
    echo "Testing port $port:"
    curl -s http://127.0.0.1:$port/api/health | grep node_id
done
```

## 🔧 Maintenance

### Reload Configuration

```bash
# Test config
sudo nginx -t

# Reload (no downtime)
sudo systemctl reload nginx

# Restart (brief downtime)
sudo systemctl restart nginx
```

### Restart Laravel Nodes

```bash
# Stop all
./stop-multi-nodes.sh

# Start all
./start-multi-nodes.sh

# Or restart individual node
kill $(cat storage/logs/node-1.pid)
APP_NODE_ID=node-1 php -S 127.0.0.1:8001 -t public &
```

## 🐛 Troubleshooting

### 502 Bad Gateway

**Cause:** Laravel nodes tidak running

**Solution:**
```bash
# Check nodes
ps aux | grep php

# Restart nodes
./start-multi-nodes.sh
```

### WebSocket Connection Failed

**Cause:** Reverb tidak running atau port blocked

**Solution:**
```bash
# Check Reverb
ps aux | grep reverb

# Restart Reverb
php artisan reverb:start
```

### Static Files 404

**Cause:** Path tidak sesuai di nginx config

**Solution:**
```bash
# Check root path di nginx config
grep "root" /etc/nginx/sites-available/laravel-absensi

# Update jika perlu
sudo nano /etc/nginx/sites-available/laravel-absensi
```

## 📚 Additional Resources

- [NGINX-SETUP.md](NGINX-SETUP.md) - Panduan lengkap setup
- [Nginx Documentation](https://nginx.org/en/docs/)
- [Laravel Deployment](https://laravel.com/docs/deployment)

## 🎯 Production Checklist

- [ ] Update `server_name` dengan domain production
- [ ] Setup SSL certificate (Let's Encrypt)
- [ ] Configure firewall (UFW/firewalld)
- [ ] Setup systemd services untuk auto-start
- [ ] Configure log rotation
- [ ] Setup monitoring (optional)
- [ ] Test failover scenario
- [ ] Backup configuration files

---

**Need help?** Check [NGINX-SETUP.md](NGINX-SETUP.md) untuk panduan lengkap.
