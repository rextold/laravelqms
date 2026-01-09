# Performance Optimization Configuration for Laravel QMS

## 1. CACHING SETUP
# Set cache driver to file or Redis (file is default)
# For production, use Redis:
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

## 2. QUEUE CONFIGURATION
# Process queue jobs asynchronously instead of sync
QUEUE_CONNECTION=database

## 3. SESSION CONFIGURATION
# Use cookie or file for sessions (avoid database)
SESSION_DRIVER=file
SESSION_LIFETIME=120

## 4. DATABASE OPTIMIZATION
# Connection pooling
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qms
DB_USERNAME=root
DB_PASSWORD=

## 5. APP OPTIMIZATION
APP_DEBUG=false
APP_ENV=production

## 6. LOGGING
LOG_CHANNEL=stack
LOG_LEVEL=error

## 7. MEMORY LIMITS (adjust based on server)
PHP_MEMORY_LIMIT=256M
OPCACHE_MEMORY_CONSUMPTION=128

## HOW TO SETUP REDIS (Ubuntu/Debian):
# 1. Install Redis:
#    sudo apt-get install redis-server
#
# 2. Start Redis:
#    sudo systemctl start redis-server
#    sudo systemctl enable redis-server
#
# 3. Test Redis:
#    redis-cli ping
#    (should return PONG)
#
# 4. Install Laravel Redis client:
#    composer require predis/predis

## HOW TO RUN QUEUE WORKERS:
# 1. Start a queue worker:
#    php artisan queue:work --queue=default,high --timeout=60
#
# 2. For production, use supervisor to keep it running:
#    sudo apt-get install supervisor
#    Create /etc/supervisor/conf.d/qms-worker.conf

## HOW TO SETUP LOAD BALANCER:
# 1. Copy nginx.conf to /etc/nginx/sites-available/qms-loadbalancer
# 2. Enable it: sudo ln -s /etc/nginx/sites-available/qms-loadbalancer /etc/nginx/sites-enabled/
# 3. Test: sudo nginx -t
# 4. Restart: sudo systemctl restart nginx
#
# 5. Run multiple PHP processes:
#    php artisan serve --port=8001 &
#    php artisan serve --port=8002 &
#    php artisan serve --port=8003 &

## PERFORMANCE MONITORING:
# 1. Check cache hit rate:
#    redis-cli INFO stats
#
# 2. Monitor server resources:
#    top
#    free -h
#    df -h
#
# 3. Check database queries:
#    Enable query logging in config/database.php
#    Check storage/logs/laravel.log
