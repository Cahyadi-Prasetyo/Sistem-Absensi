# 🔒 Security Guidelines

## ⚠️ IMPORTANT: Environment Files

### 🔴 NEVER Push These Files to GitHub:

```
.env
.env.docker
.env.backup
.env.production
```

These files contain **SENSITIVE INFORMATION**:
- `APP_KEY` - Laravel encryption key
- `DB_PASSWORD` - Database password
- `REVERB_APP_SECRET` - WebSocket secret
- API keys and credentials

### ✅ Safe to Push:

```
.env.example
.env.docker.example
```

These are **TEMPLATE FILES** with empty/placeholder values.

---

## 🛡️ Security Checklist

### Before Pushing to GitHub

- [ ] Check `.gitignore` includes `.env*` files
- [ ] Verify no sensitive data in committed files
- [ ] Use `.env.example` for documentation
- [ ] Never commit real credentials

### For Production

- [ ] Generate new `APP_KEY`
  ```bash
  php artisan key:generate
  ```

- [ ] Generate new Reverb credentials
  ```bash
  php artisan reverb:install
  ```

- [ ] Use strong database password
  ```bash
  DB_PASSWORD=your-strong-password-here
  ```

- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Use HTTPS (`REVERB_SCHEME=https`)

---

## 🔐 Sensitive Data in Current Setup

### 1. APP_KEY
**Location:** `.env`, `.env.docker`  
**Risk:** HIGH - Used for encryption  
**Action:** Generate new key for each environment

```bash
php artisan key:generate
```

### 2. REVERB_APP_SECRET
**Location:** `.env`, `.env.docker`  
**Risk:** MEDIUM - Used for WebSocket auth  
**Action:** Generate new secret

```bash
php artisan reverb:install
```

### 3. DB_PASSWORD
**Location:** `.env`, `.env.docker`  
**Risk:** HIGH - Database access  
**Action:** Use strong password in production

```env
DB_PASSWORD=your-strong-password-here
```

### 4. REDIS_PASSWORD
**Location:** `.env`, `.env.docker`  
**Risk:** MEDIUM - Cache/Queue access  
**Action:** Set password in production

```env
REDIS_PASSWORD=your-redis-password
```

---

## 📋 Setup Instructions

### For Development

1. Copy example file:
   ```bash
   copy .env.docker.example .env.docker
   ```

2. Generate APP_KEY:
   ```bash
   php artisan key:generate
   ```

3. Generate Reverb credentials:
   ```bash
   php artisan reverb:install
   ```

4. Set database password:
   ```env
   DB_PASSWORD=secret
   ```

### For Production

1. Copy example file:
   ```bash
   cp .env.docker.example .env.production
   ```

2. Update all sensitive values:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=base64:your-generated-key
   DB_PASSWORD=your-strong-password
   REVERB_APP_SECRET=your-generated-secret
   REVERB_SCHEME=https
   ```

3. Set proper permissions:
   ```bash
   chmod 600 .env.production
   ```

---

## 🚨 What to Do if Credentials Leaked

### 1. Immediately Rotate All Credentials

```bash
# Generate new APP_KEY
php artisan key:generate --force

# Generate new Reverb credentials
php artisan reverb:install --force

# Change database password
# Update .env and restart MySQL
```

### 2. Check for Unauthorized Access

```bash
# Check database logs
docker logs laravel_absensi_mysql

# Check application logs
tail -f storage/logs/laravel.log
```

### 3. Notify Users (if applicable)

- Force logout all users
- Reset passwords if needed
- Monitor for suspicious activity

---

## 🔍 Security Best Practices

### 1. Environment Variables

- ✅ Use `.env` files for configuration
- ✅ Never commit `.env` files
- ✅ Use different credentials per environment
- ✅ Rotate credentials regularly

### 2. Docker Security

- ✅ Don't expose unnecessary ports
- ✅ Use Docker secrets for production
- ✅ Run containers as non-root user
- ✅ Keep images updated

### 3. Database Security

- ✅ Use strong passwords
- ✅ Limit database user permissions
- ✅ Enable SSL/TLS connections
- ✅ Regular backups

### 4. Application Security

- ✅ Keep Laravel updated
- ✅ Use HTTPS in production
- ✅ Enable CSRF protection
- ✅ Validate all inputs
- ✅ Use prepared statements (Eloquent does this)

---

## 📚 Additional Resources

- [Laravel Security](https://laravel.com/docs/security)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Docker Security](https://docs.docker.com/engine/security/)

---

## 📞 Report Security Issues

If you discover a security vulnerability, please email:
- **Email:** security@example.com
- **Do NOT** create public GitHub issues for security vulnerabilities

---

**Last Updated:** 9 November 2025  
**Version:** 1.0.0
