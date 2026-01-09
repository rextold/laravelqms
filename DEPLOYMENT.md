# Laravel QMS - Deployment Guide

## Table of Contents
- [Server Requirements](#server-requirements)
- [Initial Server Setup](#initial-server-setup)
- [Application Deployment](#application-deployment)
- [Database Configuration](#database-configuration)
- [Nginx Configuration](#nginx-configuration)
- [Load Balancer Setup](#load-balancer-setup)
- [SSL Certificate Setup](#ssl-certificate-setup)
- [Queue Workers](#queue-workers)
- [Caching Setup](#caching-setup)
- [Post-Deployment](#post-deployment)
- [Maintenance](#maintenance)

---

## Server Requirements

### Minimum Specifications
- **OS**: Ubuntu 20.04 LTS or higher / CentOS 8+
- **CPU**: 2 cores minimum (4+ recommended for production)
- **RAM**: 4GB minimum (8GB+ recommended)
- **Storage**: 20GB SSD minimum
- **PHP**: 8.2 or higher
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Nginx 1.18+

### Required PHP Extensions
```bash
php8.2-cli
php8.2-fpm
php8.2-mysql
php8.2-mbstring
php8.2-xml
php8.2-bcmath
php8.2-curl
php8.2-zip
php8.2-gd
php8.2-redis (optional, for caching)
```

---

## Initial Server Setup

### 1. Update System Packages
```bash
sudo apt update
sudo apt upgrade -y
```

### 2. Install PHP 8.2
```bash
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip php8.2-gd -y
```

### 3. Install Composer
```bash
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
```

### 4. Install Nginx
```bash
sudo apt install nginx -y
sudo systemctl start nginx
sudo systemctl enable nginx
```

### 5. Install MySQL
```bash
sudo apt install mysql-server -y
sudo mysql_secure_installation
```

### 6. Install Redis (Optional - for better caching)
```bash
sudo apt install redis-server -y
sudo systemctl start redis-server
sudo systemctl enable redis-server
sudo apt install php8.2-redis -y
```

---

## Application Deployment

### 1. Create Application Directory
```bash
sudo mkdir -p /var/www/laravelqms
sudo chown -R $USER:www-data /var/www/laravelqms
```

### 2. Clone or Upload Application
```bash
cd /var/www/laravelqms
# Option A: Using Git
git clone <your-repo-url> .

# Option B: Upload files via SCP/SFTP
# scp -r /path/to/local/laravelqms user@server:/var/www/
```

### 3. Install Dependencies
```bash
cd /var/www/laravelqms
composer install --optimize-autoloader --no-dev
```

### 4. Set Permissions
```bash
sudo chown -R www-data:www-data /var/www/laravelqms/storage
sudo chown -R www-data:www-data /var/www/laravelqms/bootstrap/cache
sudo chmod -R 775 /var/www/laravelqms/storage
sudo chmod -R 775 /var/www/laravelqms/bootstrap/cache
```

### 5. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file:
```env
APP_NAME="Queue Management System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravelqms
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=database

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## Database Configuration

### 1. Create Database
```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE laravelqms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'laravelqms_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON laravelqms.* TO 'laravelqms_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. Run Migrations
```bash
cd /var/www/laravelqms
php artisan migrate --force
php artisan db:seed --force
```

### 3. Create Database Indexes (Performance Optimization)
```bash
sudo mysql -u root -p laravelqms
```

```sql
-- Add composite indexes for better query performance
ALTER TABLE queues ADD INDEX idx_status_updated (status, updated_at);
ALTER TABLE queues ADD INDEX idx_counter_status (counter_id, status);
ALTER TABLE queues ADD INDEX idx_company_status (company_id, status);

-- Show indexes to verify
SHOW INDEXES FROM queues;
EXIT;
```

---

## Nginx Configuration

### Single Server Setup

Create Nginx configuration:
```bash
sudo nano /etc/nginx/sites-available/laravelqms
```

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/laravelqms/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 7d;
        add_header Cache-Control "public, immutable";
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        
        # Performance tuning
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/javascript application/json;
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/laravelqms /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## Load Balancer Setup

For high-traffic environments with multiple application servers:

### 1. Setup Multiple Application Instances

On your load balancer server, use the provided `nginx.conf`:

```bash
sudo cp nginx.conf /etc/nginx/nginx.conf
```

### 2. Configure Backend Servers

Edit the upstream block in nginx.conf:
```nginx
upstream qms_backend {
    least_conn;
    server 192.168.1.101:8001 max_fails=3 fail_timeout=30s;
    server 192.168.1.102:8001 max_fails=3 fail_timeout=30s;
    server 192.168.1.103:8001 max_fails=3 fail_timeout=30s;
}
```

Replace IPs with your actual backend server IPs.

### 3. Start Application Servers

On each backend server:
```bash
# Server 1 (Port 8001)
cd /var/www/laravelqms
php artisan serve --host=0.0.0.0 --port=8001

# Server 2 (Port 8002)
cd /var/www/laravelqms
php artisan serve --host=0.0.0.0 --port=8002

# Server 3 (Port 8003)
cd /var/www/laravelqms
php artisan serve --host=0.0.0.0 --port=8003
```

**For Production**: Use PHP-FPM with separate pools or systemd services instead of `php artisan serve`.

### 4. Setup Systemd Services for Backend Servers

Create service file for each instance:
```bash
sudo nano /etc/systemd/system/laravelqms@.service
```

```ini
[Unit]
Description=Laravel QMS Instance %i
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/laravelqms
ExecStart=/usr/bin/php artisan serve --host=0.0.0.0 --port=%i
Restart=on-failure

[Install]
WantedBy=multi-user.target
```

Enable and start services:
```bash
sudo systemctl enable laravelqms@8001
sudo systemctl enable laravelqms@8002
sudo systemctl enable laravelqms@8003

sudo systemctl start laravelqms@8001
sudo systemctl start laravelqms@8002
sudo systemctl start laravelqms@8003
```

---

## SSL Certificate Setup

### Using Let's Encrypt (Free SSL)

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

Auto-renewal:
```bash
sudo certbot renew --dry-run
```

### Manual SSL Configuration

If you have your own certificate:
```bash
sudo nano /etc/nginx/sites-available/laravelqms
```

Add SSL configuration:
```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    # ... rest of configuration
}

server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

---

## Queue Workers

### 1. Create Supervisor Configuration
```bash
sudo apt install supervisor -y
sudo nano /etc/supervisor/conf.d/laravelqms-worker.conf
```

```ini
[program:laravelqms-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/laravelqms/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/laravelqms/storage/logs/worker.log
stopwaitsecs=3600
```

### 2. Start Queue Workers
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravelqms-worker:*
```

### 3. Monitor Workers
```bash
sudo supervisorctl status
```

---

## Caching Setup

### 1. Configure Redis Cache
Already configured in `.env`:
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### 2. Cache Configuration
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Clear Cache (when needed)
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Post-Deployment

### 1. Storage Link
```bash
php artisan storage:link
```

### 2. Optimize Application
```bash
php artisan optimize
```

### 3. Test Application
```bash
# Test database connection
php artisan db:show

# Test queue processing
php artisan queue:work --once

# Check application status
php artisan about
```

### 4. Setup Cron Jobs

Add to crontab:
```bash
crontab -e
```

```cron
* * * * * cd /var/www/laravelqms && php artisan schedule:run >> /dev/null 2>&1
```

### 5. Configure Firewall
```bash
sudo ufw allow 'Nginx Full'
sudo ufw allow OpenSSH
sudo ufw enable
```

### 6. Setup Monitoring

Create health check endpoint and monitor:
- Application uptime
- Database connectivity
- Redis connectivity
- Queue worker status
- Disk space usage

---

## Maintenance

### Daily Tasks
```bash
# Check logs
tail -f /var/www/laravelqms/storage/logs/laravel.log

# Monitor queue
php artisan queue:monitor

# Check worker status
sudo supervisorctl status
```

### Weekly Tasks
```bash
# Restart queue workers
sudo supervisorctl restart laravelqms-worker:*

# Clear old logs
find /var/www/laravelqms/storage/logs -name "*.log" -mtime +30 -delete

# Database backup
mysqldump -u laravelqms_user -p laravelqms > backup_$(date +%Y%m%d).sql
```

### Monthly Tasks
```bash
# Update dependencies
composer update
php artisan migrate --force

# Optimize database
php artisan db:optimize

# Review and optimize indexes
# Check slow query log
```

### Deployment Updates
```bash
# Pull latest code
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Restart services
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx
sudo supervisorctl restart laravelqms-worker:*
```

---

## Troubleshooting

### 500 Internal Server Error
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check Nginx error logs
sudo tail -f /var/log/nginx/error.log

# Check PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log

# Verify permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Database Connection Issues
```bash
# Test connection
php artisan db:show

# Check MySQL status
sudo systemctl status mysql

# Verify credentials in .env
```

### Cache Issues
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart Redis
sudo systemctl restart redis-server
```

### Queue Not Processing
```bash
# Check worker status
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart laravelqms-worker:*

# Manual test
php artisan queue:work --once

# Check failed jobs
php artisan queue:failed
```

### Performance Issues
```bash
# Enable query logging temporarily
# Add to AppServiceProvider boot():
# DB::listen(function($query) {
#     Log::info($query->sql, $query->bindings, $query->time);
# });

# Check slow queries
sudo mysql -u root -p
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;

# Monitor Redis
redis-cli monitor

# Check server resources
htop
df -h
free -m
```

---

## Security Checklist

- [ ] Change default database credentials
- [ ] Set `APP_DEBUG=false` in production
- [ ] Configure firewall (UFW/iptables)
- [ ] Install SSL certificate
- [ ] Set proper file permissions
- [ ] Disable directory listing
- [ ] Configure rate limiting
- [ ] Enable CSRF protection
- [ ] Regular security updates
- [ ] Backup database regularly
- [ ] Monitor access logs
- [ ] Setup fail2ban for SSH

---

## Backup Strategy

### Automated Database Backup Script
```bash
#!/bin/bash
BACKUP_DIR="/var/backups/laravelqms"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="laravelqms"
DB_USER="laravelqms_user"
DB_PASS="your_password"

mkdir -p $BACKUP_DIR
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +7 -delete
```

Save as `/usr/local/bin/backup-qms.sh` and add to cron:
```bash
0 2 * * * /usr/local/bin/backup-qms.sh
```

---

## Support & Resources

- Laravel Documentation: https://laravel.com/docs
- Nginx Documentation: https://nginx.org/en/docs/
- Performance Guide: See `PERFORMANCE_OPTIMIZATION.md`
- Database Optimization: See `DATABASE_OPTIMIZATION.php`

---

**Last Updated**: January 2026
**Version**: 1.0
