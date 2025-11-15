# Nginx Server Setup Guide

Panduan lengkap untuk setup Nginx sebagai load balancer untuk Laravel multi-node.

## 📋 Prerequisites

- Ubuntu/Debian Server (atau distro Linux lainnya)
- Nginx installed
- PHP 8.2+ dengan PHP-FPM
- MySQL/MariaDB
- Redis (optional)
- Composer
- Node.js & NPM

## 🚀 Installation Steps

### 1. Install Nginx

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install nginx -y

# CentOS/RHEL
sudo yum install nginx -y

# Check status
sudo systemctl status nginx
```

### 2. Install PHP & Extensions

```bash
# Ubuntu/Debian
sudo apt install php8.2-fpm php8.2-cli php8.2-mysql php8.2-redis \
    php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd -y

# Check PHP-FPM status
sudo systemctl status php8.2-fpm
```

### 3. Clone Project

```bash
# Create directory
sudo mkdir -p /var/www
cd /var/www

# Clone repository
sudo git clone <repository-url> laravel-reverb-absensi
cd laravel-reverb-absensi

# Set permissions
sudo chown -R www-data:www-data /var/www/laravel-reverb-absensi
sudo chmod -R 755 /var/www/laravel-reverb-absensi
sudo chmod -R 775 storage bootstrap/cache
```

### 4. Install Dependencies

```bash
# Composer
composer install --no-dev --optimize-autoloader

# NPM
npm install
npm run build
```

### 5. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate key
php artisan key:generate

# Edit .env
nano .env
```

**Configure .env:**
```env
APP_NAME="Sistem Absensi"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://absensi.local

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi
DB_USERNAME=absensi_user
DB_PASSWORD=secure_password

CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1

BROADCAST_CONNECTION=reverb
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
```

### 6. Database Setup

```bash
# Login to MySQL
mysql -u root -p

# Create database and user
CREATE DATABASE absensi;
CREATE USER 'absensi_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON absensi.* TO 'absensi_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan migrate --force
php artisan db:seed --force
```

### 7. Configure Nginx

```bash
# Copy configuration
sudo cp nginx/laravel-absensi.conf /etc/nginx/sites-available/laravel-absensi

# Edit configuration
sudo nano /etc/nginx/sites-available/laravel-absensi

# Update paths dan domain sesuai kebutuhan:
# - server_name: domain Anda
# - root: path project
# - upstream ports: sesuaikan jika perlu

# Enable site
sudo ln -s /etc/nginx/sites-available/laravel-absensi /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

### 8. Setup Multi-Node Laravel

```bash
# Make scripts executable
chmod +x nginx/start-multi-nodes.sh
chmod +x nginx/stop-multi-nodes.sh

# Edit script paths jika perlu
nano nginx/start-multi-nodes.sh
# Update PROJECT_DIR sesuai lokasi project Anda

# Start multi-node
./nginx/start-multi-nodes.sh
```

### 9. Setup Systemd Services (Recommended)

Buat systemd service untuk auto-start saat boot.

**Laravel Node 1:**
```bash
sudo nano /etc/systemd/system/laravel-node-1.service
```

```ini
[Unit]
Description=Laravel Node 1
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/laravel-reverb-absensi
Environment="APP_NODE_ID=node-1"
ExecStart=/usr/bin/php -S 127.0.0.1:8001 -t public
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

**Ulangi untuk Node 2 dan 3** (ganti port dan node ID)

**Reverb Service:**
```bash
sudo nano /etc/systemd/system/laravel-reverb.service
```

```ini
[Unit]
Description=Laravel Reverb WebSocket Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/laravel-reverb-absensi
ExecStart=/usr/bin/php artisan reverb:start --host=0.0.0.0 --port=8080
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

**Queue Worker Service:**
```bash
sudo nano /etc/systemd/system/laravel-queue.service
```

```ini
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/laravel-reverb-absensi
ExecStart=/usr/bin/php artisan queue:work --tries=3 --timeout=90
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

**Enable dan Start Services:**
```bash
# Reload systemd
sudo systemctl daemon-reload

# Enable services
sudo systemctl enable laravel-node-1
sudo systemctl enable laravel-node-2
sudo systemctl enable laravel-node-3
sudo systemctl enable laravel-reverb
sudo systemctl enable laravel-queue

# Start services
sudo systemctl start laravel-node-1
sudo systemctl start laravel-node-2
sudo systemctl start laravel-node-3
sudo systemctl start laravel-reverb
sudo systemctl start laravel-queue

# Check status
sudo systemctl status laravel-node-1
sudo systemctl status laravel-reverb
```

### 10. Setup Hosts (Local Testing)

```bash
# Edit hosts file
sudo nano /etc/hosts

# Add entry
127.0.0.1   absensi.local
```

## 🧪 Testing

### Test Nginx Configuration

```bash
# Test config
sudo nginx -t

# Check Nginx status
sudo systemctl status nginx

# View logs
sudo tail -f /var/log/nginx/laravel-absensi-access.log
sudo tail -f /var/log/nginx/laravel-absensi-error.log
```

### Test Laravel Nodes

```bash
# Test each node directly
curl http://127.0.0.1:8001
curl http://127.0.0.1:8002
curl http://127.0.0.1:8003

# Test via Nginx (load balanced)
curl http://absensi.local

# Test health check
curl http://absensi.local/api/health
```

### Test Load Balancing

```bash
# Multiple requests to see different nodes
for i in {1..10}; do
    curl -s http://absensi.local/api/health | grep node_id
done

# Should show different node IDs
```

### Test WebSocket

```bash
# Check Reverb is running
curl http://127.0.0.1:8080

# Test via browser
# Open http://absensi.local
# Check browser console for WebSocket connection
```

## 🔧 Maintenance

### View Logs

```bash
# Nginx logs
sudo tail -f /var/log/nginx/laravel-absensi-access.log
sudo tail -f /var/log/nginx/laravel-absensi-error.log

# Laravel logs
tail -f /var/www/laravel-reverb-absensi/storage/logs/laravel.log

# Systemd service logs
sudo journalctl -u laravel-node-1 -f
sudo journalctl -u laravel-reverb -f
sudo journalctl -u laravel-queue -f
```

### Restart Services

```bash
# Restart Nginx
sudo systemctl restart nginx

# Restart Laravel nodes
sudo systemctl restart laravel-node-1
sudo systemctl restart laravel-node-2
sudo systemctl restart laravel-node-3

# Restart Reverb
sudo systemctl restart laravel-reverb

# Restart Queue
sudo systemctl restart laravel-queue
```

### Update Application

```bash
cd /var/www/laravel-reverb-absensi

# Pull latest code
git pull

# Update dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Run migrations
php artisan migrate --force

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Restart services
sudo systemctl restart laravel-node-{1,2,3}
sudo systemctl restart laravel-reverb
sudo systemctl restart laravel-queue
```

## 🔐 Security

### Firewall Setup

```bash
# UFW (Ubuntu)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# Firewalld (CentOS)
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

### SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Get certificate
sudo certbot --nginx -d absensi.yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

### Secure Permissions

```bash
# Set proper ownership
sudo chown -R www-data:www-data /var/www/laravel-reverb-absensi

# Set proper permissions
sudo find /var/www/laravel-reverb-absensi -type f -exec chmod 644 {} \;
sudo find /var/www/laravel-reverb-absensi -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/laravel-reverb-absensi/storage
sudo chmod -R 775 /var/www/laravel-reverb-absensi/bootstrap/cache
```

## 🐛 Troubleshooting

### Nginx 502 Bad Gateway

```bash
# Check PHP-FPM is running
sudo systemctl status php8.2-fpm

# Check Laravel nodes are running
sudo systemctl status laravel-node-1

# Check logs
sudo tail -f /var/log/nginx/error.log
```

### Permission Denied

```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/laravel-reverb-absensi

# Fix storage permissions
sudo chmod -R 775 storage bootstrap/cache
```

### WebSocket Not Connecting

```bash
# Check Reverb is running
sudo systemctl status laravel-reverb

# Check port is open
sudo netstat -tlnp | grep 8080

# Check firewall
sudo ufw status
```

### Database Connection Error

```bash
# Check MySQL is running
sudo systemctl status mysql

# Test connection
mysql -u absensi_user -p absensi

# Check .env configuration
cat .env | grep DB_
```

## 📊 Monitoring

### Setup Monitoring (Optional)

```bash
# Install monitoring tools
sudo apt install htop iotop nethogs -y

# Monitor processes
htop

# Monitor network
sudo nethogs

# Monitor disk I/O
sudo iotop
```

### Health Check Cron

```bash
# Add to crontab
crontab -e

# Add line:
*/5 * * * * curl -s http://absensi.local/api/health || echo "Health check failed" | mail -s "Alert" admin@example.com
```

## 📚 Additional Resources

- [Nginx Documentation](https://nginx.org/en/docs/)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [PHP-FPM Configuration](https://www.php.net/manual/en/install.fpm.php)

---

**Setup Complete!** 🎉

Access your application at: http://absensi.local
