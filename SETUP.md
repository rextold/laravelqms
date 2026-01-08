# Laravel Queue Management System - Setup Guide

## Quick Start (5 Minutes)

### Step 1: Install Dependencies
```powershell
composer install
```

### Step 2: Environment Setup
```powershell
# Copy environment file
Copy-Item .env.example .env

# Generate application key
php artisan key:generate
```

### Step 3: Configure Database
Edit `.env` file and set your database credentials:
```
DB_DATABASE=qms_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 4: Create Database
```sql
-- Run this in MySQL/phpMyAdmin
CREATE DATABASE qms_db;
```

### Step 5: Run Migrations & Seed Data
```powershell
php artisan migrate:fresh --seed
```

### Step 6: Create Storage Link
```powershell
php artisan storage:link
```

### Step 7: Start Server
```powershell
php artisan serve
```

### Step 8: Access the System
Open your browser and visit:
- **Login**: http://localhost:8000/login
- **Kiosk**: http://localhost:8000/kiosk
- **Monitor**: http://localhost:8000/monitor

## Default Login Credentials

### SuperAdmin
- Username: `superadmin`
- Password: `password`

### Admin
- Username: `admin`
- Password: `password`

### Counter Staff
- Username: `counter1`, `counter2`, `counter3`, `counter4`, `counter5`
- Password: `password` (for all)

## Testing the System

### 1. Test Admin Functions
1. Login as `admin` / `password`
2. Create a new counter user
3. Upload a video (MP4 format recommended)
4. Create a marquee announcement

### 2. Test Counter Operations
1. Login as `counter1` / `password`
2. Click "Go Online"
3. Open Kiosk in another tab
4. Generate a queue for Counter 1
5. Return to counter dashboard
6. Click "Call Next Queue"
7. Test "Complete & Next" button

### 3. Test Monitor Display
1. Open http://localhost:8000/monitor in a new window
2. Press F11 for fullscreen
3. Watch real-time updates as queues are called

## Optional: Enable Real-Time Updates

### Using Pusher (Easiest)
1. Sign up at https://pusher.com (free tier available)
2. Create a new app
3. Get your credentials
4. Update `.env`:
```
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster
```
5. Install Pusher PHP SDK:
```powershell
composer require pusher/pusher-php-server
```

### Using Laravel WebSockets (Self-hosted)
```powershell
composer require beyondcode/laravel-websockets
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider"
php artisan migrate
php artisan websockets:serve
```

## Troubleshooting

### Error: "Base table or view not found"
```powershell
php artisan migrate:fresh --seed
```

### Error: "No application encryption key"
```powershell
php artisan key:generate
```

### Error: "Storage link not found"
```powershell
php artisan storage:link
```

### Videos not playing
- Ensure videos are MP4 format
- Check `storage/app/public/videos` directory exists
- Verify storage link is created

### Queues not updating
- Current version uses AJAX polling (auto-refresh)
- For real-time, configure Pusher or WebSockets

## Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Use strong passwords
- [ ] Configure proper database backups
- [ ] Set up SSL certificate
- [ ] Configure queue workers
- [ ] Set up proper file permissions
- [ ] Enable caching: `php artisan config:cache`
- [ ] Enable route caching: `php artisan route:cache`
- [ ] Enable view caching: `php artisan view:cache`

## Support

For issues or questions:
1. Check the README.md file
2. Review Laravel documentation: https://laravel.com/docs
3. Check error logs in `storage/logs/laravel.log`

## Next Steps

1. **Customize**: Edit views to match your branding
2. **Configure**: Adjust counter numbers and descriptions
3. **Test**: Run through all workflows
4. **Deploy**: Follow production checklist
5. **Train**: Prepare staff training materials

---

**System Built With:**
- Laravel 11.x
- Tailwind CSS 3.x
- MySQL 8.x
- PHP 8.2+

**Happy Queueing! 🎫**
