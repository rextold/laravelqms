# Company Code URI Implementation Guide

## Overview
The Laravel QMS application now supports multi-company functionality with company codes in URIs to identify which company is being accessed.

## New URI Structure

### Public Routes (No Company Code)
```
GET  /login                     - Login page (company code selected on form)
POST /login                     - Submit login credentials with company code
POST /logout                    - Logout and return to login
```

### Company-based Routes (Admin, Counter, Kiosk, Monitor)
```
GET  /{company_code}/kiosk                  - Kiosk queue generation interface
POST /{company_code}/kiosk/generate-queue   - Generate new queue ticket

GET  /{company_code}/monitor                - Display monitor (real-time queue info)
GET  /{company_code}/monitor/data           - Monitor data JSON endpoint
```

### Protected Routes (Admin)
```
GET  /{company_code}/admin/dashboard                      - Admin dashboard
GET  /{company_code}/admin/users                          - User management list
GET  /{company_code}/admin/users/create                   - Create new user
POST /{company_code}/admin/users                          - Store user
GET  /{company_code}/admin/users/{user}/edit              - Edit user
PUT  /{company_code}/admin/users/{user}                   - Update user
DELETE /{company_code}/admin/users/{user}                 - Delete user

GET  /{company_code}/admin/company-settings               - Company settings
PUT  /{company_code}/admin/company-settings               - Update settings
DELETE /{company_code}/admin/company-settings/logo        - Remove logo

GET  /{company_code}/admin/videos                         - Video management
POST /{company_code}/admin/videos                         - Add video
POST /{company_code}/admin/videos/{video}/toggle          - Toggle video active
DELETE /{company_code}/admin/videos/{video}               - Delete video
POST /{company_code}/admin/videos/upload-bell             - Upload bell sound
POST /{company_code}/admin/videos/reset-bell              - Reset bell sound

GET  /{company_code}/admin/marquee                        - Marquee management
POST /{company_code}/admin/marquee                        - Add marquee
PUT  /{company_code}/admin/marquee/{marquee}              - Update marquee
DELETE /{company_code}/admin/marquee/{marquee}            - Delete marquee
```

### Protected Routes (Counter)
```
GET  /{company_code}/counter/dashboard       - Counter overview with stats & reports
GET  /{company_code}/counter/panel           - Counter service station (single frame)
GET  /{company_code}/counter/data            - Counter data JSON endpoint
POST /{company_code}/counter/toggle-online   - Set counter online/offline
POST /{company_code}/counter/call-next       - Call next customer
POST /{company_code}/counter/move-next       - Move to next customer
POST /{company_code}/counter/notify          - Notify customer (blink on monitor)
POST /{company_code}/counter/skip            - Skip current customer
POST /{company_code}/counter/recall          - Recall skipped customer
POST /{company_code}/counter/transfer        - Transfer to another counter
```

## Example URLs

### Login (Same for All Companies)
- Login Page: `http://localhost:8000/login`
- Logout: `http://localhost:8000/logout`

### Company "DEFAULT"
- Admin Dashboard: `http://localhost:8000/DEFAULT/admin/dashboard`
- Counter Dashboard: `http://localhost:8000/DEFAULT/counter/dashboard`
- Kiosk: `http://localhost:8000/DEFAULT/kiosk`
- Monitor: `http://localhost:8000/DEFAULT/monitor`

### Company "COMPANY_A"
- Admin Dashboard: `http://localhost:8000/COMPANY_A/admin/dashboard`
- Counter Dashboard: `http://localhost:8000/COMPANY_A/counter/dashboard`
- Kiosk: `http://localhost:8000/COMPANY_A/kiosk`
- Monitor: `http://localhost:8000/COMPANY_A/monitor`

### Company "COMPANY_B"
- Admin Dashboard: `http://localhost:8000/COMPANY_B/admin/dashboard`
- Counter Dashboard: `http://localhost:8000/COMPANY_B/counter/dashboard`
- Kiosk: `http://localhost:8000/COMPANY_B/kiosk`
- Monitor: `http://localhost:8000/COMPANY_B/monitor`

## Database Changes

### New Tables
- `companies` - Store company information
  - `company_code` (unique) - URL identifier (e.g., 'DEFAULT', 'COMPANY_A')
  - `company_name` - Display name
  - `company_logo` - Logo path
  - `primary_color`, `secondary_color`, `accent_color`, `text_color` - Theme colors
  - `company_address`, `company_phone`, `company_email` - Contact info
  - `queue_number_digits` - Configuration
  - `is_active` - Enable/disable company

### Modified Tables
- `users` - Added `company_id` (foreign key to companies)
- `queues` - Added `company_id` (foreign key to companies)
- `company_settings` - Added `company_id` (foreign key to companies)

## Model Relationships

### Company
```php
- hasMany(User)
- hasMany(Queue)
- hasMany(VideoControl)
- hasMany(Video)
- hasMany(MarqueeSetting)
```

### User
```php
- belongsTo(Company)
- hasMany(Queue, 'counter_id')
- hasMany(Queue, 'transferred_to')
```

### Queue
```php
- belongsTo(Company)
- belongsTo(User, 'counter_id')
- belongsTo(User, 'transferred_to')
```

## Middleware

### `company.context` Middleware
- Located: `app/Http/Middleware/EnsureCompanyContext.php`
- Function: Validates company code in URI and loads company context
- Behavior:
  - Validates company code exists in `company_code` route parameter
  - Checks company is `is_active = true`
  - Stores company in session for access throughout request
  - Returns 404 if company not found

## Authentication Changes

### Login Form
- Single unified login page at `/login`
- Form includes three fields:
  - `username` - User's username
  - `password` - User's password
  - `company_code` - Company code selector (dropdown or input)
- Users select/enter their company code on the login form

### Login Validation
- Credentials (username + password + company_code) are validated together
- Users can only login with valid company_code
- Returns to login form with error if company not found or credentials invalid

### Redirect After Login
- Admin/SuperAdmin → `/{company_code}/admin/dashboard`
- Counter → `/{company_code}/counter/dashboard`

### Logout
- Returns to `/login` page (not company-specific)

## Sample Companies (from Seeder)

1. **DEFAULT** (Primary)
   - Code: `DEFAULT`
   - Name: `Default Company`
   - Primary Color: Blue (#3b82f6)

2. **COMPANY_A** (Branch 1)
   - Code: `COMPANY_A`
   - Name: `Company A - Branch 1`
   - Primary Color: Red (#ef4444)

3. **COMPANY_B** (Branch 2)
   - Code: `COMPANY_B`
   - Name: `Company B - Branch 2`
   - Primary Color: Cyan (#06b6d4)

## Migration Steps

To apply company code functionality:

1. Run migrations:
```bash
php artisan migrate
```

2. Seed companies and users:
```bash
php artisan db:seed
```

3. Update route calls in views to include company_code:
```blade
<!-- Old -->
<a href="{{ route('login') }}">Login</a>

<!-- New -->
<a href="{{ route('login', ['company_code' => 'DEFAULT']) }}">Login</a>
```

## Helper Functions

Access company in controller:
```php
$company = session('company');
$companyCode = request()->route('company_code');
```

Access in Blade template:
```blade
{{ session('company')->company_name }}
{{ request()->route('company_code') }}
```

## Validation & Security

- Company code is case-sensitive (validate in seeder/factory)
- Only active companies are accessible
- Users can only access their assigned company
- Admin/SuperAdmin can manage users within their company

## Future Enhancements

- Multi-company support for admin users
- Role-based company access
- Company transfer functionality
- Subdomain-based routing (optional alternative to URI-based)
- Company branding theme application per company
