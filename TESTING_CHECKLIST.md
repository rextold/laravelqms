# Testing Checklist for Laravel QMS

## Pre-Testing Setup

- [ ] Database created and configured in `.env`
- [ ] Migrations run successfully: `php artisan migrate:fresh --seed`
- [ ] Storage link created: `php artisan storage:link`
- [ ] Server running: `php artisan serve`
- [ ] Can access http://localhost:8000

---

## Authentication Tests

### Login Functionality
- [ ] Access `/login` page loads correctly
- [ ] Login with superadmin credentials works
- [ ] Login with admin credentials works
- [ ] Login with counter1 credentials works
- [ ] Wrong credentials show error message
- [ ] Empty fields show validation error

### Logout Functionality
- [ ] Logout button visible when logged in
- [ ] Logout redirects to login page
- [ ] Counter automatically goes offline on logout
- [ ] Cannot access protected pages after logout

---

## SuperAdmin Tests

### User Management
- [ ] Can view all users including superadmin accounts
- [ ] Can create new admin account
- [ ] Can create new counter account with required fields
- [ ] Can edit existing users
- [ ] Can delete users (except self)
- [ ] Counter fields (display_name, counter_number) visible for counter role
- [ ] Counter fields hidden for admin role
- [ ] Validation prevents duplicate usernames
- [ ] Validation prevents duplicate counter numbers

### Access Control
- [ ] Can access all admin routes
- [ ] Can manage other admins
- [ ] Can reset any password

---

## Admin Tests

### Dashboard Access
- [ ] Dashboard shows system statistics
- [ ] Online counters count is accurate
- [ ] Total counters displayed correctly
- [ ] Today's queue count shows

### User Management
- [ ] Can view counter accounts
- [ ] Can create new counter accounts
- [ ] Can edit counter accounts
- [ ] Can delete counter accounts
- [ ] Cannot view/edit superadmin accounts
- [ ] Cannot create superadmin accounts

### Video Management
- [ ] Can upload MP4 video file
- [ ] Video appears in list after upload
- [ ] Can toggle video active/inactive
- [ ] Can delete video
- [ ] Video controls (play/pause) update correctly
- [ ] Volume slider works
- [ ] Videos stored in `storage/app/public/videos`

### Marquee Management
- [ ] Can create new marquee message
- [ ] Can set marquee speed
- [ ] Can toggle marquee active/inactive
- [ ] Only one marquee can be active at a time
- [ ] Can delete marquee message

### Monitor Access
- [ ] Can open monitor display in new window
- [ ] Monitor link works from admin panel

---

## Counter Tests

### Login & Status
- [ ] Counter login redirects to counter dashboard
- [ ] "Go Online" button visible and works
- [ ] Status changes from offline to online
- [ ] "Go Offline" button appears when online
- [ ] Dashboard shows counter number and name
- [ ] Auto-offline on logout works

### Queue Operations
- [ ] "Call Next Queue" button visible when no current queue
- [ ] Calling next queue updates display
- [ ] Current queue shows in large format
- [ ] "Complete & Next" button visible when serving
- [ ] Completing queue moves to next waiting
- [ ] No queues message shows when empty

### Statistics Display
- [ ] Waiting count shows correctly
- [ ] Completed today count updates
- [ ] Current queue number displays

### Transfer Function
- [ ] Transfer dropdown shows only online counters
- [ ] Can select target counter
- [ ] Transfer button works
- [ ] Queue moves to selected counter
- [ ] Cannot transfer to offline counter

### Auto-Refresh
- [ ] Page refreshes every 10 seconds
- [ ] Data updates without full reload

---

## Kiosk Tests

### Counter Selection
- [ ] All online counters displayed
- [ ] Offline counters NOT displayed
- [ ] Counter cards show number, name, description
- [ ] "Available" badge shows on online counters

### Queue Generation
- [ ] Clicking counter generates queue number
- [ ] Modal shows generated queue number
- [ ] Queue number format correct: YYYYMMDD-CC-NNNN
- [ ] Counter information displayed
- [ ] Print button visible
- [ ] Capture photo button visible
- [ ] Done button closes modal

### Print Function
- [ ] Print window opens
- [ ] Queue details visible in print preview
- [ ] Can print successfully

### User Experience
- [ ] Large, touch-friendly buttons
- [ ] Responsive on mobile devices
- [ ] No counters message when all offline
- [ ] Auto-refresh every 30 seconds

---

## Monitor Display Tests

### Layout & Design
- [ ] Full-screen friendly (F11 works)
- [ ] Header shows system title
- [ ] Video section displays if videos uploaded
- [ ] Counters grid layout responsive
- [ ] Marquee at bottom of screen

### Counter Display
- [ ] Only online counters shown
- [ ] Offline counters automatically hidden
- [ ] Counter number displayed
- [ ] Display name shown
- [ ] Short description visible
- [ ] Current queue number shows or "---"

### Video Playback
- [ ] Video auto-plays if uploaded
- [ ] Video loops continuously
- [ ] Volume control from admin panel works
- [ ] Play/pause from admin panel works

### Marquee Display
- [ ] Active marquee text scrolls
- [ ] Speed setting applied correctly
- [ ] Text readable and visible

### Real-Time Updates
- [ ] Data refreshes every 5 seconds
- [ ] Queue updates reflect immediately
- [ ] Counter status changes update display
- [ ] No page flicker on refresh

---

## Integration Tests

### Full Customer Flow
- [ ] Customer approaches kiosk
- [ ] Selects online counter
- [ ] Receives queue number
- [ ] Number appears in monitor display
- [ ] Counter calls queue
- [ ] Monitor updates with called queue
- [ ] Counter completes queue
- [ ] Next queue called automatically

### Counter Transfer Flow
- [ ] Counter 1 and 2 both online
- [ ] Queue generated for Counter 1
- [ ] Counter 1 transfers to Counter 2
- [ ] Queue appears in Counter 2's waiting list
- [ ] Monitor updates to show transfer

### Multi-Counter Scenario
- [ ] Multiple counters online
- [ ] Multiple queues generated
- [ ] Each counter calls own queues
- [ ] Monitor shows all counters correctly
- [ ] No queue conflicts

### Offline/Online Toggle
- [ ] Counter goes offline
- [ ] Kiosk hides counter immediately
- [ ] Monitor hides counter
- [ ] Counter goes online
- [ ] Kiosk shows counter
- [ ] Monitor shows counter

---

## Security Tests

### Unauthorized Access
- [ ] Cannot access `/admin/dashboard` without login
- [ ] Cannot access `/counter/dashboard` without login
- [ ] Counter cannot access admin routes
- [ ] Admin cannot access counter routes (without proper role)
- [ ] Non-superadmin cannot manage admins

### CSRF Protection
- [ ] All forms have CSRF token
- [ ] Form submissions fail without token
- [ ] API calls include CSRF token

### Data Validation
- [ ] Cannot create user with empty username
- [ ] Cannot create counter without display_name
- [ ] Cannot create counter without counter_number
- [ ] Cannot upload invalid file types
- [ ] SQL injection prevented (try: `' OR '1'='1`)

---

## Performance Tests

### Page Load Times
- [ ] Login page loads < 1 second
- [ ] Dashboard loads < 2 seconds
- [ ] Kiosk loads < 2 seconds
- [ ] Monitor loads < 2 seconds

### Database Queries
- [ ] No N+1 query problems
- [ ] Relationships eager-loaded where needed
- [ ] Indexes used on queue_number

### Auto-Refresh Impact
- [ ] Monitor refresh doesn't cause lag
- [ ] Counter refresh doesn't freeze UI
- [ ] Kiosk refresh smooth

---

## Browser Compatibility

### Desktop Browsers
- [ ] Chrome - All features work
- [ ] Firefox - All features work
- [ ] Edge - All features work
- [ ] Safari - All features work (Mac)

### Mobile Browsers
- [ ] Chrome Mobile - Kiosk works well
- [ ] Safari Mobile - Kiosk works well
- [ ] Responsive design on tablets

---

## Error Handling Tests

### Database Errors
- [ ] Graceful handling of connection loss
- [ ] Error logged in `storage/logs/laravel.log`

### File Upload Errors
- [ ] File too large shows error
- [ ] Invalid file type rejected
- [ ] Storage full handled

### Queue Errors
- [ ] No queues available shows message
- [ ] Invalid counter ID handled
- [ ] Transfer to offline counter rejected

---

## Production Readiness

### Configuration
- [ ] `.env.example` file complete
- [ ] Database settings documented
- [ ] APP_KEY generated
- [ ] Storage link created

### Security
- [ ] Default passwords documented
- [ ] Role middleware registered
- [ ] CSRF enabled
- [ ] Debug mode off for production

### Documentation
- [ ] README.md complete
- [ ] SETUP.md available
- [ ] Code comments present
- [ ] API endpoints documented

---

## Final Checks

- [ ] All migrations run without errors
- [ ] Seeder creates test data correctly
- [ ] No JavaScript console errors
- [ ] No PHP errors in logs
- [ ] All routes return 200 OK
- [ ] Forms submit successfully
- [ ] Redirects work correctly
- [ ] Flash messages display

---

## Test Scenarios Summary

### Scenario 1: Basic Operations
1. Admin creates 2 counters
2. Both counters login and go online
3. Customer generates queue for Counter 1
4. Counter 1 calls and completes queue
✅ Expected: Smooth operation, no errors

### Scenario 2: Transfer
1. 2 counters online
2. Generate 3 queues for Counter 1
3. Counter 1 transfers 1 queue to Counter 2
4. Both counters process their queues
✅ Expected: Queues processed correctly

### Scenario 3: Offline Handling
1. Counter online with waiting queues
2. Counter goes offline
3. Kiosk should hide counter
4. Monitor should hide counter
✅ Expected: System updates correctly

### Scenario 4: High Load
1. Generate 20 queues rapidly
2. Multiple counters call queues simultaneously
3. Monitor updates in real-time
✅ Expected: No conflicts, all queues tracked

---

## Bug Report Template

If you find issues, document:
- **Steps to reproduce**
- **Expected behavior**
- **Actual behavior**
- **Browser/device**
- **Error messages**
- **Screenshots**

---

## ✅ Sign-Off

Testing completed by: ________________
Date: ________________
All critical tests passed: [ ] Yes [ ] No
Ready for production: [ ] Yes [ ] No

---

**Note:** This checklist ensures the system works correctly across all roles and scenarios. Complete each section systematically before deployment.
