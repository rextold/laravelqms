<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\MarqueeController;
use App\Http\Controllers\OrganizationSettingsController;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\MonitorController;
use App\Http\Controllers\AccountController;
use App\Models\User;
use App\Models\Video;
use App\Models\MarqueeSetting;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect('/login');
});

// Redirect /kiosk to default organization kiosk
Route::get('/kiosk', function () {
    // Redirect to default organization kiosk
    $defaultOrg = \App\Models\Organization::first();
    if ($defaultOrg) {
        return redirect('/' . strtolower($defaultOrg->organization_code) . '/kiosk');
    }
    return response('No organization found', 404);
});

// Redirect /monitor to default organization monitor, or show fallback view if none exists
Route::get('/monitor', function () {
    $defaultOrg = \App\Models\Organization::first();
    if ($defaultOrg) {
        return redirect('/' . strtolower($defaultOrg->organization_code) . '/monitor');
    }
    // Show a simple fallback monitor view if no organization exists
    return view('monitor.fallback');
});

// Login & Auth (no company code in URL)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});
// GET /logout route for GET method logout
Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Test routes
Route::get('/test-csrf', function () {
    return view('test.csrf-test');
});

Route::post('/test-csrf-endpoint', function (Illuminate\Http\Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'CSRF token validation successful',
        'data' => $request->all()
    ]);
});

Route::get('/test-forbidden', function () {
    return view('test.forbidden-test');
});

Route::post('/test-forbidden-endpoint', function (Illuminate\Http\Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Access granted - no forbidden issues',
        'data' => $request->all()
    ]);
});

// SuperAdmin routes (no company code in URL)
Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Organization management
    Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    Route::get('/organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::post('/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
    Route::get('/organizations/{organization}/edit', [OrganizationController::class, 'edit'])->name('organizations.edit')->whereNumber('organization');
    Route::put('/organizations/{organization}', [OrganizationController::class, 'update'])->name('organizations.update')->whereNumber('organization');
    Route::delete('/organizations/{organization}', [OrganizationController::class, 'destroy'])->name('organizations.destroy')->whereNumber('organization');
    
    // User management
    Route::get('/users', [AdminController::class, 'manageUsers'])->name('users.index');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit')->whereNumber('user');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update')->whereNumber('user');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.destroy')->whereNumber('user');
});

// Organization-based routes (with organization_code prefix)
Route::prefix('{organization_code}')->group(function () {
    
    // Kiosk (public) - no auth or org context validation needed
    Route::prefix('kiosk')->name('kiosk.')->middleware(['organization.context', 'allow.public'])->group(function () {
        Route::get('/', [KioskController::class, 'index'])->name('index');
        Route::get('/counters', [KioskController::class, 'counters'])->name('counters');
        Route::get('/generate-queue', [KioskController::class, 'generateQueue'])->name('generate');
    });

    // Monitor Display (public, read-only)
    Route::prefix('monitor')->name('monitor.')->middleware(['organization.context', 'allow.public'])->group(function () {
        Route::get('/', [MonitorController::class, 'index'])->name('index');
        Route::get('/data', [MonitorController::class, 'getData'])->name('data');
    });
    
    // Public counter data endpoint - accessible without authentication (supports ?counter_id query param)
    // Used by kiosk, monitor, and counter panel for real-time updates
    Route::prefix('counter')->name('counter.')->middleware(['auth', 'organization.context', 'allow.public'])->group(function () {
        Route::get('/data', [CounterController::class, 'getData'])->name('public-data');
    });

    // Direct public access route for counter data (without organization prefix, uses query parameter)
    // Fallback for direct API access without organization context
        
    // Protected routes - auth middleware runs FIRST, then organization context
    Route::middleware(['auth', 'organization.context'])->group(function () {

        // Account Settings (for admin and counter users)
        Route::get('/account/settings', [AccountController::class, 'settings'])->name('account.settings');
        Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.update-password');
        
        // Admin routes
        Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
            
            // Organization Settings
            Route::get('/organization-settings', [OrganizationSettingsController::class, 'edit'])->name('organization-settings.edit');
            Route::put('/organization-settings', [OrganizationSettingsController::class, 'update'])->name('organization-settings.update');
            Route::delete('/organization-settings/logo', [OrganizationSettingsController::class, 'removeLogo'])->name('organization-settings.remove-logo');
            // API endpoint for organization settings (used by monitor)
            Route::get('/organization-settings/api/get', [OrganizationSettingsController::class, 'getSettingsApi'])->name('organization-settings.api.get');
            
            // User management
            Route::get('/users', [AdminController::class, 'manageUsers'])->name('users.index');
            Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
            Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
            Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit')->whereNumber('user');
            Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update')->whereNumber('user');
            Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.destroy')->whereNumber('user');
            
            // Video management
            Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
            Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
            Route::put('/videos/{video}', [VideoController::class, 'update'])->name('videos.update')->whereNumber('video');
            Route::post('/videos/order', [VideoController::class, 'updateOrder'])->name('videos.order');
            Route::post('/videos/{video}/toggle', [VideoController::class, 'toggleActive'])->name('videos.toggle')->whereNumber('video');
            Route::delete('/videos/{video}', [VideoController::class, 'destroy'])->name('videos.destroy')->whereNumber('video');            Route::post('/videos/control', [VideoController::class, 'updateControl'])->name('videos.control');
            Route::post('/videos/upload-bell', [VideoController::class, 'uploadBellSound'])->name('videos.upload-bell');
            Route::post('/videos/reset-bell', [VideoController::class, 'resetBellSound'])->name('videos.reset-bell');
            Route::post('/videos/set-now-playing', [VideoController::class, 'setNowPlaying'])->name('videos.set-now-playing');
            
            // Playlist management
            Route::get('/playlist', [VideoController::class, 'getPlaylist'])->name('playlist.get');
            Route::post('/playlist/add', [VideoController::class, 'addToPlaylist'])->name('playlist.add');
            Route::post('/playlist/remove', [VideoController::class, 'removeFromPlaylist'])->name('playlist.remove');
            Route::post('/playlist/reorder', [VideoController::class, 'reorderPlaylist'])->name('playlist.reorder');
            Route::post('/playlist/control', [VideoController::class, 'updatePlaylistControl'])->name('playlist.control');
            Route::post('/playlist/now-playing', [VideoController::class, 'setNowPlaying'])->name('playlist.now-playing');
            
            // Marquee management
            Route::get('/marquee', [MarqueeController::class, 'index'])->name('marquee.index');
            Route::get('/marquee/list', [MarqueeController::class, 'list'])->name('marquee.list');
            Route::post('/marquee', [MarqueeController::class, 'store'])->name('marquee.store');
            Route::put('/marquee/{marquee}', [MarqueeController::class, 'update'])->name('marquee.update');
            Route::post('/marquee/{marquee}/toggle', [MarqueeController::class, 'toggleActive'])->name('marquee.toggle');
            Route::delete('/marquee/{marquee}', [MarqueeController::class, 'destroy'])->name('marquee.destroy');
        });        // All other counter routes require organization context and role:counter
        Route::middleware(['auth', 'organization.context', 'role:counter'])->prefix('counter')->name('counter.')->group(function () {
            // Counter single-frame calling view now at /counter/panel
            Route::get('/panel', [CounterController::class, 'callView'])->name('panel');
            // Backward-compatible redirect from /view to /panel
            Route::get('/view', function () {
                return redirect()->to(route('counter.panel', ['organization_code' => request()->route('organization_code')]));
            })->name('view');
            
            // Counter operations - use POST for state-changing operations
            // Middleware validates: 1) POST method, 2) Counter role, 3) Online status
            Route::post('/toggle-online', [CounterController::class, 'toggleOnline'])->name('toggle-online');
            Route::post('/call-next', [CounterController::class, 'callNext'])->name('call-next');
            Route::post('/move-next', [CounterController::class, 'moveToNext'])->name('move-next');
            Route::post('/transfer', [CounterController::class, 'transferQueue'])->name('transfer');
            Route::post('/notify', [CounterController::class, 'notifyCustomer'])->name('notify');
            Route::post('/skip', [CounterController::class, 'skipQueue'])->name('skip');
            Route::post('/recall', [CounterController::class, 'recallQueue'])->name('recall');
        });
    });
});