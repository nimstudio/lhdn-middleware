# Deployment Guide

## 🚀 **Production Deployment Guide**

This guide covers the complete deployment process for the LHDN Middleware SaaS application to a production environment.

---

## 📋 **Prerequisites**

### **Server Requirements**
- **Operating System**: Ubuntu 20.04+ or CentOS 8+
- **PHP**: 8.2+ with required extensions
- **MySQL**: 8.0+ with InnoDB engine
- **Redis**: 6.0+ for caching and queues
- **Web Server**: Nginx 1.18+ or Apache 2.4+
- **SSL Certificate**: Let's Encrypt or commercial SSL
- **Node.js**: 18+ for frontend asset compilation
- **Composer**: 2.0+ for PHP dependencies

### **Required PHP Extensions**
```bash
php8.2-cli
php8.2-fpm
php8.2-mysql
php8.2-redis
php8.2-curl
php8.2-gd
php8.2-mbstring
php8.2-xml
php8.2-zip
php8.2-bcmath
php8.2-intl
php8.2-imagick
```

### **System Dependencies**
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install nginx mysql-server redis-server nodejs npm composer git

# CentOS/RHEL
sudo yum install nginx mysql-server redis nodejs npm composer git
```

---

## 🏗️ **Server Setup**

### **1. Create Application User**
```bash
# Create application user
sudo adduser --system --group --home /var/www/lhdn-middleware lhdn

# Add user to web server group
sudo usermod -a -G www-data lhdn
```

### **2. Directory Structure**
```bash
# Create application directory
sudo mkdir -p /var/www/lhdn-middleware
sudo chown lhdn:lhdn /var/www/lhdn-middleware

# Create storage directories
sudo mkdir -p /var/www/lhdn-middleware/storage/{app,framework,logs}
sudo mkdir -p /var/www/lhdn-middleware/storage/framework/{cache,sessions,views}
sudo mkdir -p /var/www/lhdn-middleware/storage/app/{public,private}
sudo chown -R lhdn:lhdn /var/www/lhdn-middleware/storage
sudo chmod -R 775 /var/www/lhdn-middleware/storage
```

### **3. Database Setup**
```bash
# Login to MySQL
sudo mysql -u root -p

# Create database and user
CREATE DATABASE lhdn_middleware CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'lhdn_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON lhdn_middleware.* TO 'lhdn_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### **4. Redis Configuration**
```bash
# Edit Redis configuration
sudo nano /etc/redis/redis.conf

# Set password (optional but recommended)
requirepass your_redis_password

# Restart Redis
sudo systemctl restart redis-server
sudo systemctl enable redis-server
```

---

## 📦 **Application Deployment**

### **1. Clone Repository**
```bash
# Switch to application user
sudo su - lhdn

# Clone repository
cd /var/www/lhdn-middleware
git clone https://github.com/your-repo/lhdn-middleware.git .

# Set proper permissions
chmod -R 755 /var/www/lhdn-middleware
chmod -R 775 /var/www/lhdn-middleware/storage
chmod -R 775 /var/www/lhdn-middleware/bootstrap/cache
```

### **2. Install Dependencies**
```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
npm install

# Build frontend assets
npm run build
```

### **3. Environment Configuration**
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit environment file
nano .env
```

### **4. Environment Variables**
```bash
# .env configuration
APP_NAME="LHDN Middleware SaaS"
APP_ENV=production
APP_KEY=base64:your_generated_key
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lhdn_middleware
DB_USERNAME=lhdn_user
DB_PASSWORD=secure_password

# Redis configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379

# Cache and Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Mail configuration
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# File storage
FILESYSTEM_DISK=local
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

# LHDN MyInvois configuration
MYINVOIS_SANDBOX_URL=https://api.myinvois.hasil.gov.my
MYINVOIS_PRODUCTION_URL=https://api.myinvois.hasil.gov.my
MYINVOIS_TIMEOUT=30
MYINVOIS_RETRY_ATTEMPTS=3

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=info
LOG_DAYS=14
```

### **5. Database Migration**
```bash
# Run database migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --force

# Create storage link
php artisan storage:link
```

### **6. Cache Optimization**
```bash
# Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

---

## 🌐 **Web Server Configuration**

### **Nginx Configuration**
```nginx
# /etc/nginx/sites-available/lhdn-middleware
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/lhdn-middleware/public;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/javascript;

    # Main location block
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Security - deny access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ /(storage|bootstrap/cache) {
        deny all;
    }

    # File upload size
    client_max_body_size 100M;
}
```

### **Enable Site**
```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/lhdn-middleware /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
sudo systemctl enable nginx
```

---

## 🔧 **PHP-FPM Configuration**

### **PHP-FPM Pool Configuration**
```ini
# /etc/php/8.2/fpm/pool.d/lhdn-middleware.conf
[lhdn-middleware]
user = lhdn
group = lhdn
listen = /var/run/php/php8.2-fpm-lhdn-middleware.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000

php_admin_value[error_log] = /var/log/php8.2-fpm-lhdn-middleware.log
php_admin_flag[log_errors] = on
php_admin_value[memory_limit] = 256M
php_admin_value[upload_max_filesize] = 100M
php_admin_value[post_max_size] = 100M
php_admin_value[max_execution_time] = 300
```

### **PHP Configuration**
```ini
# /etc/php/8.2/fpm/php.ini
memory_limit = 256M
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
max_input_vars = 3000
date.timezone = Asia/Kuala_Lumpur

# OPcache configuration
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1
```

### **Restart PHP-FPM**
```bash
sudo systemctl restart php8.2-fpm
sudo systemctl enable php8.2-fpm
```

---

## 🔐 **SSL Certificate Setup**

### **Let's Encrypt SSL**
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

---

## ⚙️ **Queue Worker Setup**

### **Supervisor Configuration**
```ini
# /etc/supervisor/conf.d/lhdn-middleware-worker.conf
[program:lhdn-middleware-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/lhdn-middleware/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=lhdn
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/lhdn-middleware/storage/logs/worker.log
stopwaitsecs=3600
```

### **Start Supervisor**
```bash
# Reload supervisor configuration
sudo supervisorctl reread
sudo supervisorctl update

# Start workers
sudo supervisorctl start lhdn-middleware-worker:*

# Check status
sudo supervisorctl status
```

---

## 📊 **Monitoring & Logging**

### **Log Rotation**
```bash
# /etc/logrotate.d/lhdn-middleware
/var/www/lhdn-middleware/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 644 lhdn lhdn
    postrotate
        sudo systemctl reload php8.2-fpm
    endscript
}
```

### **System Monitoring**
```bash
# Install monitoring tools
sudo apt install htop iotop nethogs

# Create monitoring script
sudo nano /usr/local/bin/lhdn-monitor.sh
```

```bash
#!/bin/bash
# lhdn-monitor.sh
echo "=== LHDN Middleware System Status ==="
echo "Date: $(date)"
echo "Uptime: $(uptime)"
echo "Disk Usage: $(df -h /var/www/lhdn-middleware)"
echo "Memory Usage: $(free -h)"
echo "PHP-FPM Status: $(systemctl is-active php8.2-fpm)"
echo "Nginx Status: $(systemctl is-active nginx)"
echo "MySQL Status: $(systemctl is-active mysql)"
echo "Redis Status: $(systemctl is-active redis-server)"
echo "Queue Workers: $(supervisorctl status | grep lhdn-middleware-worker)"
```

### **Cron Jobs**
```bash
# Add to crontab
sudo crontab -e

# Laravel scheduler
* * * * * cd /var/www/lhdn-middleware && php artisan schedule:run >> /dev/null 2>&1

# Log cleanup
0 2 * * * find /var/www/lhdn-middleware/storage/logs -name "*.log" -mtime +14 -delete

# Backup database
0 3 * * * mysqldump -u lhdn_user -p'secure_password' lhdn_middleware | gzip > /var/backups/lhdn-middleware-$(date +\%Y\%m\%d).sql.gz
```

---

## 🔄 **Deployment Automation**

### **Deployment Script**
```bash
#!/bin/bash
# deploy.sh
set -e

echo "Starting deployment..."

# Pull latest code
cd /var/www/lhdn-middleware
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx

# Restart queue workers
sudo supervisorctl restart lhdn-middleware-worker:*

echo "Deployment completed successfully!"
```

### **Make Script Executable**
```bash
chmod +x deploy.sh
```

---

## 🛡️ **Security Hardening**

### **Firewall Configuration**
```bash
# Install UFW
sudo apt install ufw

# Configure firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### **File Permissions**
```bash
# Set proper permissions
sudo chown -R lhdn:lhdn /var/www/lhdn-middleware
sudo chmod -R 755 /var/www/lhdn-middleware
sudo chmod -R 775 /var/www/lhdn-middleware/storage
sudo chmod -R 775 /var/www/lhdn-middleware/bootstrap/cache
```

### **Database Security**
```sql
-- Remove test database
DROP DATABASE IF EXISTS test;

-- Remove anonymous users
DELETE FROM mysql.user WHERE User='';

-- Remove root remote access
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');

-- Flush privileges
FLUSH PRIVILEGES;
```

---

## 🔍 **Troubleshooting**

### **Common Issues**

#### **Permission Issues**
```bash
# Fix storage permissions
sudo chown -R lhdn:www-data /var/www/lhdn-middleware/storage
sudo chmod -R 775 /var/www/lhdn-middleware/storage
```

#### **Queue Not Processing**
```bash
# Check queue status
sudo supervisorctl status lhdn-middleware-worker:*

# Restart workers
sudo supervisorctl restart lhdn-middleware-worker:*

# Check logs
tail -f /var/www/lhdn-middleware/storage/logs/worker.log
```

#### **Database Connection Issues**
```bash
# Test database connection
php artisan tinker
DB::connection()->getPdo();

# Check MySQL status
sudo systemctl status mysql
```

#### **SSL Certificate Issues**
```bash
# Check certificate status
sudo certbot certificates

# Renew certificate
sudo certbot renew --dry-run
```

### **Log Files**
- **Application Logs**: `/var/www/lhdn-middleware/storage/logs/laravel.log`
- **Nginx Logs**: `/var/log/nginx/access.log`, `/var/log/nginx/error.log`
- **PHP-FPM Logs**: `/var/log/php8.2-fpm.log`
- **MySQL Logs**: `/var/log/mysql/error.log`
- **System Logs**: `/var/log/syslog`

---

## 📈 **Performance Optimization**

### **Database Optimization**
```sql
-- Optimize MySQL configuration
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
query_cache_size = 64M
query_cache_type = 1
```

### **PHP OPcache**
```ini
# Optimize OPcache
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 0
opcache.validate_timestamps = 0
```

### **Redis Optimization**
```conf
# Redis configuration
maxmemory 512mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

---

## 🔄 **Backup Strategy**

### **Database Backup**
```bash
#!/bin/bash
# backup-db.sh
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/lhdn-middleware"
mkdir -p $BACKUP_DIR

mysqldump -u lhdn_user -p'secure_password' lhdn_middleware | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +7 -delete
```

### **File Backup**
```bash
#!/bin/bash
# backup-files.sh
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/lhdn-middleware"

tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz /var/www/lhdn-middleware/storage/app

# Keep only last 7 days
find $BACKUP_DIR -name "files_backup_*.tar.gz" -mtime +7 -delete
```

---

## 🚀 **Go Live Checklist**

### **Pre-Launch Checklist**
- [ ] SSL certificate installed and working
- [ ] Database migrations completed
- [ ] Environment variables configured
- [ ] Queue workers running
- [ ] Log rotation configured
- [ ] Backup strategy implemented
- [ ] Monitoring setup
- [ ] Security hardening completed
- [ ] Performance optimization applied
- [ ] Error handling tested
- [ ] LHDN integration tested
- [ ] PDF generation tested
- [ ] Email functionality tested

### **Post-Launch Monitoring**
- [ ] Monitor application logs
- [ ] Check queue processing
- [ ] Monitor database performance
- [ ] Check SSL certificate expiry
- [ ] Monitor disk space
- [ ] Check backup completion
- [ ] Monitor user registrations
- [ ] Check LHDN API responses
- [ ] Monitor error rates
- [ ] Check response times

---

*This deployment guide provides comprehensive instructions for deploying the LHDN Middleware SaaS application to a production environment with proper security, monitoring, and optimization.*



