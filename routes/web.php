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
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Queue Management System Routes
| 
| Route Groups:
| 1. Public Routes (no auth) - Home, Kiosk, Monitor
| 2. Auth Routes - Login, Logout
| 3. SuperAdmin Routes - Organization & User Management
| 4. Organization Routes - Admin, Counter operations
|
*/

// ============================================================================
// ROOT & DEFAULT REDIRECTS
// ============================================================================

Route::get('/', function () {
    return redirect('/login');
});

// Default kiosk redirect (no organization specified)
Route::get('/kiosk', function () {
    $defaultOrg = \App\Models\Organization::first();
    if ($defaultOrg) {
        return redirect('/' . strtolower($defaultOrg->organization_code) . '/kiosk');
    }
    return response('No organization found', 404);
});

// Default monitor redirect (no organization specified)
Route::get('/monitor', function () {
    $defaultOrg = \App\Models\Organization::first();
    if ($defaultOrg) {
        return redirect('/' . strtolower($defaultOrg->organization_code) . '/monitor');
    }
    return view('monitor.fallback');
});

// ============================================================================
// AUTHENTICATION ROUTES
// ============================================================================

// Guest routes (login page)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Logout route (GET method for simplicity)
Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ============================================================================
// SUPERADMIN ROUTES (No organization prefix)
// ============================================================================

Route::middleware(['auth', 'role:superadmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // Organization CRUD
        Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
        Route::get('/organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
        Route::post('/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
        Route::get('/organizations/{organization}/edit', [OrganizationController::class, 'edit'])->name('organizations.edit');
        Route::put('/organizations/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');
        Route::delete('/organizations/{organization}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');
        
        // User CRUD
        Route::get('/users', [AdminController::class, 'manageUsers'])->name('users.index');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.destroy');
    });

// ============================================================================
// ORGANIZATION-BASED ROUTES (with {organization_code} prefix)
// ============================================================================

Route::prefix('{organization_code}')->group(function () {
    
    // ------------------------------------------------------------------------
    // PUBLIC ROUTES - No Authentication Required
    // These routes are accessible without login
    // ------------------------------------------------------------------------
    
    // KIOSK - Public queue ticket generation
    Route::prefix('kiosk')
        ->name('kiosk.')
        ->middleware(['organization.context', 'allow.public'])
        ->group(function () {
            Route::get('/', [KioskController::class, 'index'])->name('index');
            Route::get('/counters', [KioskController::class, 'counters'])->name('counters');
            Route::get('/generate-queue', [KioskController::class, 'generateQueue'])->name('generate');
        });

    // MONITOR - Public display screen (NO AUTH - accessible by anyone)
    Route::prefix('monitor')
        ->name('monitor.')
        ->middleware(['organization.context', 'allow.public'])
        ->withoutMiddleware(['auth', 'web'])
        ->group(function () {
            Route::get('/', [MonitorController::class, 'index'])->name('index');
            Route::get('/data', [MonitorController::class, 'getData'])->name('data');
        });
    
    // ------------------------------------------------------------------------
    // PROTECTED ROUTES - Authentication Required
    // ------------------------------------------------------------------------
    
    Route::middleware(['auth', 'organization.context'])->group(function () {
        
        // Account Settings (for all authenticated users)
        Route::get('/account/settings', [AccountController::class, 'settings'])->name('account.settings');
        Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.update-password');
        
        // --------------------------------------------------------------------
        // ADMIN ROUTES - Requires admin role
        // --------------------------------------------------------------------
        
        Route::prefix('admin')
            ->name('admin.')
            ->middleware('role:admin')
            ->group(function () {
                
                Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
                
                // Organization Settings
                Route::get('/organization-settings', [OrganizationSettingsController::class, 'edit'])->name('organization-settings.edit');
                Route::put('/organization-settings', [OrganizationSettingsController::class, 'update'])->name('organization-settings.update');
                Route::delete('/organization-settings/logo', [OrganizationSettingsController::class, 'removeLogo'])->name('organization-settings.remove-logo');
                Route::get('/organization-settings/api/get', [OrganizationSettingsController::class, 'getSettingsApi'])->name('organization-settings.api.get');
                
                // User Management
                Route::get('/users', [AdminController::class, 'manageUsers'])->name('users.index');
                Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
                Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
                Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
                Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
                Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.destroy');
                
                // Video Management
                Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
                Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
                Route::put('/videos/{video}', [VideoController::class, 'update'])->name('videos.update');
                Route::post('/videos/order', [VideoController::class, 'updateOrder'])->name('videos.order');
                Route::post('/videos/{video}/toggle', [VideoController::class, 'toggleActive'])->name('videos.toggle');
                Route::delete('/videos/{video}', [VideoController::class, 'destroy'])->name('videos.destroy');
                Route::post('/videos/control', [VideoController::class, 'updateControl'])->name('videos.control');
                Route::post('/videos/upload-bell', [VideoController::class, 'uploadBellSound'])->name('videos.upload-bell');
                Route::post('/videos/reset-bell', [VideoController::class, 'resetBellSound'])->name('videos.reset-bell');
                Route::post('/videos/set-now-playing', [VideoController::class, 'setNowPlaying'])->name('videos.set-now-playing');
                
                // Playlist Management
                Route::get('/playlist', [VideoController::class, 'getPlaylist'])->name('playlist.get');
                Route::post('/playlist/add', [VideoController::class, 'addToPlaylist'])->name('playlist.add');
                Route::post('/playlist/remove', [VideoController::class, 'removeFromPlaylist'])->name('playlist.remove');
                Route::post('/playlist/reorder', [VideoController::class, 'reorderPlaylist'])->name('playlist.reorder');
                Route::post('/playlist/control', [VideoController::class, 'updatePlaylistControl'])->name('playlist.control');
                Route::post('/playlist/now-playing', [VideoController::class, 'setNowPlaying'])->name('playlist.now-playing');
                
                // Marquee Management
                Route::get('/marquee', [MarqueeController::class, 'index'])->name('marquee.index');
                Route::get('/marquee/list', [MarqueeController::class, 'list'])->name('marquee.list');
                Route::post('/marquee', [MarqueeController::class, 'store'])->name('marquee.store');
                Route::put('/marquee/{marquee}', [MarqueeController::class, 'update'])->name('marquee.update');
                Route::post('/marquee/{marquee}/toggle', [MarqueeController::class, 'toggleActive'])->name('marquee.toggle');
                Route::delete('/marquee/{marquee}', [MarqueeController::class, 'destroy'])->name('marquee.destroy');
            });
        
        // --------------------------------------------------------------------
        // COUNTER ROUTES - Requires counter role
        // --------------------------------------------------------------------
        
        Route::prefix('counter')
            ->name('counter.')
            ->middleware('role:counter')
            ->group(function () {
                
                // Counter Panel (main view)
                Route::get('/panel', [CounterController::class, 'callView'])->name('panel');
                
                // Legacy redirect
                Route::get('/view', function () {
                    return redirect()->route('counter.panel', ['organization_code' => request()->route('organization_code')]);
                })->name('view');
                
                // Counter Data API (for real-time updates)
                Route::get('/data', [CounterController::class, 'getData'])->name('data');
                
                // Counter Status Toggle (GET for simplicity)
                Route::get('/toggle-online', [CounterController::class, 'toggleOnline'])->name('toggle-online');
                
                // Queue Operations (POST for state changes)
                Route::post('/call-next', [CounterController::class, 'callNext'])->name('call-next');
                Route::post('/move-next', [CounterController::class, 'moveToNext'])->name('move-next');
                Route::post('/skip', [CounterController::class, 'skipQueue'])->name('skip');
                Route::post('/recall', [CounterController::class, 'recallQueue'])->name('recall');
                Route::post('/transfer', [CounterController::class, 'transferQueue'])->name('transfer');
                Route::post('/notify', [CounterController::class, 'notifyCustomer'])->name('notify');
            });
    });
});