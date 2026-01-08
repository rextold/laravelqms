# Laravel Queue Management System (QMS)

A comprehensive web-based queuing system built with Laravel featuring real-time updates, role-based access control, and multi-counter management.

## Features

### Role-Based System
- **SUPERADMIN**: Manages all users including admins
- **ADMIN**: Manages counters, videos, and marquee announcements
- **COUNTER/TELLER**: Handles queue operations and customer service

### Key Capabilities
- ✅ Real-time queue updates
- ✅ Multi-counter support with online/offline status
- ✅ Queue transfer between counters
- ✅ Video playlist management for displays
- ✅ Scrolling marquee announcements
- ✅ Self-service kiosk interface
- ✅ Full-screen monitor display
- ✅ Auto-generated queue numbers with date and counter prefix

## System Requirements

- PHP 8.2 or higher
- MySQL 8.0 or higher
- Composer
- Node.js & NPM (optional, for asset compilation)

## Installation

### 1. Clone or Setup Project
```bash
cd laravelqms
composer install
```

### 2. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure Database
Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qms_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Run Migrations & Seeders
```bash
php artisan migrate:fresh --seed
```

This creates:
- SuperAdmin: `superadmin` / `password`
- Admin: `admin` / `password`
- 5 Sample Counters: `counter1` to `counter5` / `password`

### 5. Storage Link
```bash
php artisan storage:link
```

### 6. Start Development Server
```bash
php artisan serve
```

## Usage

### Access Points

| Interface | URL | Description |
|-----------|-----|-------------|
| Login | http://localhost:8000/login | Staff login page |
| Kiosk | http://localhost:8000/kiosk | Customer self-service |
| Monitor | http://localhost:8000/monitor | Public display screen |

### Default Credentials

**SuperAdmin:**
- Username: `superadmin`
- Password: `password`

**Admin:**
- Username: `admin`
- Password: `password`

**Counters:**
- Username: `counter1` to `counter5`
- Password: `password`

## System Workflow

### For Customers (Kiosk)
1. Access kiosk interface
2. Select an **online** counter
3. Receive queue number
4. Option to print or capture photo

### For Counter Staff
1. Login and **go online**
2. Call next queue from waiting list
3. Serve customer
4. Complete and move to next
5. Transfer queues to other online counters if needed
6. **Go offline** when done

### For Admins
1. Manage counter accounts
2. Upload and control videos for display
3. Create/edit marquee announcements
4. Monitor system activity
5. Access full-screen monitor display

### Monitor Display
- Shows only **online** counters
- Displays current queue per counter
- Plays admin-controlled videos
- Shows scrolling marquee text
- Auto-refreshes every 5 seconds

## Queue Number Format

```
YYYYMMDD-CC-NNNN
```

Example: `20260108-01-0001`
- Date: 2026-01-08
- Counter: 01
- Sequence: 0001

## Real-Time Updates

The system supports broadcasting for real-time updates. To enable:

### Option 1: Pusher (Recommended for Production)
```bash
composer require pusher/pusher-php-server
```

Edit `.env`:
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster
```

### Option 2: Laravel WebSockets (Self-hosted)
```bash
composer require beyondcode/laravel-websockets
php artisan websockets:serve
```

### Option 3: Polling (Fallback)
Current implementation uses AJAX polling every 5-10 seconds as a fallback.

## File Structure

```
app/
├── Events/              # Broadcasting events
├── Http/
│   ├── Controllers/     # Request handlers
│   └── Middleware/      # Role middleware
├── Models/              # Eloquent models
└── Services/            # Business logic

database/
├── migrations/          # Database schema
└── seeders/            # Initial data

resources/
└── views/
    ├── admin/          # Admin interface
    ├── counter/        # Counter dashboard
    ├── kiosk/          # Customer kiosk
    ├── monitor/        # Display screen
    └── auth/           # Authentication

routes/
├── web.php            # Web routes
├── api.php            # API routes
└── channels.php       # Broadcast channels
```

## API Endpoints

### Counter Operations
```
POST /counter/toggle-online    # Toggle online status
POST /counter/call-next        # Call next queue
POST /counter/move-next        # Complete & next
POST /counter/transfer         # Transfer queue
```

### Kiosk
```
POST /kiosk/generate-queue     # Generate new queue
```

### Monitor
```
GET /monitor/data              # Get current data (JSON)
```

## Security Features

- ✅ CSRF protection
- ✅ Role-based access control
- ✅ Password hashing
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ XSS protection (Blade templating)

## Customization

### Queue Number Format
Edit `QueueService::generateQueueNumber()` in:
```
app/Services/QueueService.php
```

### Auto-refresh Intervals
Adjust JavaScript intervals in views:
- Counter Dashboard: 10 seconds
- Monitor Display: 5 seconds
- Kiosk: 30 seconds

### Styling
The system uses Tailwind CSS CDN. For custom styling, install Tailwind locally:
```bash
npm install -D tailwindcss
npm run build
```

## Troubleshooting

### Queues not updating in real-time
- Check `.env` `BROADCAST_DRIVER` setting
- Ensure WebSocket/Pusher is configured correctly
- Fallback: System uses AJAX polling

### Video not playing on monitor
- Verify video file is uploaded to `storage/app/public/videos`
- Check browser console for errors
- Ensure `php artisan storage:link` was run

### Counter not showing as online
- Counter must explicitly click "Go Online" button
- Check database `users.is_online` field
- Auto-offline occurs on logout

## Production Deployment

### Important Steps
1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false`
3. Run `php artisan config:cache`
4. Run `php artisan route:cache`
5. Run `php artisan view:cache`
6. Configure proper database backups
7. Set up queue workers: `php artisan queue:work`
8. Configure supervisor for queue workers
9. Set up SSL certificate
10. Configure proper file permissions

### Recommended Server Setup
- Nginx or Apache
- PHP-FPM
- MySQL/MariaDB
- Redis (for queue and cache)
- Supervisor (for queue workers)

## Support & Credits

Built with Laravel 11.x framework following best practices for:
- RESTful architecture
- MVC pattern
- Service layer pattern
- Repository pattern
- SOLID principles

## License

Open source - customize as needed for your organization.
