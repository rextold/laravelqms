<?php

/**
 * ============================================================================
 * QUEUE MANAGEMENT SYSTEM - WEB ROUTES
 * ============================================================================
 * 
 * Route Structure:
 * ----------------
 * 1. ROOT ROUTES - Home redirects
 * 2. AUTHENTICATION - Login/Logout
 * 3. PUBLIC ROUTES - Kiosk, Monitor (no auth required)
 * 4. SUPERADMIN ROUTES - System-wide management
 * 5. ORGANIZATION ROUTES - Admin & Counter operations
 * 
 * Middleware:
 * -----------
 * - guest: Redirect authenticated users
 * - auth: Require authentication
 * - role:X: Require specific role (superadmin, admin, counter)
 * - organization.context: Set organization from URL
 * - allow.public: Mark route as publicly accessible
 * 
 */

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
use App\Models\Organization;
use Illuminate\Support\Facades\Route;

// ============================================================================
// 1. ROOT & DEFAULT REDIRECTS
// ============================================================================

// Home page redirects to login
Route::get('/', fn() => redirect('/login'));

// Default kiosk (redirects to first organization's kiosk)
Route::get('/kiosk', function () {
    $org = Organization::first();
    return $org 
        ? redirect('/' . strtolower($org->organization_code) . '/kiosk')
        : response('No organization configured', 404);
});

// Default monitor (redirects to first organization's monitor)
Route::get('/monitor', function () {
    $org = Organization::first();
    return $org 
        ? redirect('/' . strtolower($org->organization_code) . '/monitor')
        : view('monitor.fallback');
});

// ============================================================================
// 2. AUTHENTICATION ROUTES
// ============================================================================

// Login (guests only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Logout (GET method for simplicity - works with simple links)
Route::get('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ============================================================================
// 3. SUPERADMIN ROUTES (No organization prefix)
// ============================================================================

Route::prefix('superadmin')
    ->name('superadmin.')
    ->middleware(['auth', 'role:superadmin'])
    ->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // Organizations CRUD
        Route::prefix('organizations')->name('organizations.')->group(function () {
            Route::get('/', [OrganizationController::class, 'index'])->name('index');
            Route::get('/create', [OrganizationController::class, 'create'])->name('create');
            Route::post('/', [OrganizationController::class, 'store'])->name('store');
            Route::get('/{organization}/edit', [OrganizationController::class, 'edit'])->name('edit');
            Route::put('/{organization}', [OrganizationController::class, 'update'])->name('update');
            Route::delete('/{organization}', [OrganizationController::class, 'destroy'])->name('destroy');
        });
        
        // Users CRUD
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminController::class, 'manageUsers'])->name('index');
            Route::get('/create', [AdminController::class, 'createUser'])->name('create');
            Route::post('/', [AdminController::class, 'storeUser'])->name('store');
            Route::get('/{user}/edit', [AdminController::class, 'editUser'])->name('edit');
            Route::put('/{user}', [AdminController::class, 'updateUser'])->name('update');
            Route::delete('/{user}', [AdminController::class, 'deleteUser'])->name('destroy');
        });
    });

// ============================================================================
// 4. ORGANIZATION-BASED ROUTES (with {organization_code} prefix)
// ============================================================================

Route::prefix('{organization_code}')->group(function () {
    
    // ========================================================================
    // PUBLIC ROUTES - No Authentication Required
    // ========================================================================
    
    // KIOSK - Customer queue ticket generation
    Route::prefix('kiosk')
        ->name('kiosk.')
        ->middleware(['organization.context', 'allow.public'])
        ->group(function () {
            Route::get('/', [KioskController::class, 'index'])->name('index');
            Route::get('/counters', [KioskController::class, 'counters'])->name('counters');
            Route::get('/generate-queue', [KioskController::class, 'generateQueue'])->name('generate');
            Route::get('/verify-ticket', [KioskController::class, 'verifyTicket'])->name('verify');
        });
    
    // MONITOR - Public display screen (completely public, no auth)
    Route::prefix('monitor')
        ->name('monitor.')
        ->middleware(['organization.context', 'allow.public'])
        ->group(function () {
            Route::get('/', [MonitorController::class, 'index'])->name('index');
            Route::get('/data', [MonitorController::class, 'getData'])->name('data');
        });
    
    // ========================================================================
    // PROTECTED ROUTES - Authentication Required
    // ========================================================================
    
    Route::middleware(['auth', 'organization.context'])->group(function () {
        
        // Account Settings (available to all authenticated users)
        Route::prefix('account')->name('account.')->group(function () {
            Route::get('/settings', [AccountController::class, 'settings'])->name('settings');
            Route::put('/password', [AccountController::class, 'updatePassword'])->name('update-password');
        });
        
        // ====================================================================
        // ADMIN ROUTES - Requires admin role
        // ====================================================================
        
        Route::prefix('admin')
            ->name('admin.')
            ->middleware('role:admin')
            ->group(function () {
                
                // Dashboard
                Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
                
                // Organization Settings
                Route::prefix('organization-settings')->name('organization-settings.')->group(function () {
                    Route::get('/', [OrganizationSettingsController::class, 'edit'])->name('edit');
                    Route::put('/', [OrganizationSettingsController::class, 'update'])->name('update');
                    Route::delete('/logo', [OrganizationSettingsController::class, 'removeLogo'])->name('remove-logo');
                    Route::get('/api/get', [OrganizationSettingsController::class, 'getSettingsApi'])->name('api.get');
                });
                
                // User Management
                Route::prefix('users')->name('users.')->group(function () {
                    Route::get('/', [AdminController::class, 'manageUsers'])->name('index');
                    Route::get('/create', [AdminController::class, 'createUser'])->name('create');
                    Route::post('/', [AdminController::class, 'storeUser'])->name('store');
                    Route::get('/{user}/edit', [AdminController::class, 'editUser'])->name('edit');
                    Route::put('/{user}', [AdminController::class, 'updateUser'])->name('update');
                    Route::delete('/{user}', [AdminController::class, 'deleteUser'])->name('destroy');
                });
                
                // Video Management
                Route::prefix('videos')->name('videos.')->group(function () {
                    Route::get('/', [VideoController::class, 'index'])->name('index');
                    Route::post('/', [VideoController::class, 'store'])->name('store');
                    Route::put('/{video}', [VideoController::class, 'update'])->name('update');
                    Route::delete('/{video}', [VideoController::class, 'destroy'])->name('destroy');
                    Route::post('/order', [VideoController::class, 'updateOrder'])->name('order');
                    Route::post('/{video}/toggle', [VideoController::class, 'toggleActive'])->name('toggle');
                    Route::post('/control', [VideoController::class, 'updateControl'])->name('control');
                    Route::post('/unmute', [VideoController::class, 'unmute'])->name('unmute');
                    Route::post('/upload-bell', [VideoController::class, 'uploadBellSound'])->name('upload-bell');
                    Route::post('/reset-bell', [VideoController::class, 'resetBellSound'])->name('reset-bell');
                    Route::post('/set-now-playing', [VideoController::class, 'setNowPlaying'])->name('set-now-playing');
                });
                
                // Playlist Management
                Route::prefix('playlist')->name('playlist.')->group(function () {
                    Route::get('/', [VideoController::class, 'getPlaylist'])->name('get');
                    Route::post('/add', [VideoController::class, 'addToPlaylist'])->name('add');
                    Route::post('/remove', [VideoController::class, 'removeFromPlaylist'])->name('remove');
                    Route::post('/reorder', [VideoController::class, 'reorderPlaylist'])->name('reorder');
                    Route::post('/control', [VideoController::class, 'updatePlaylistControl'])->name('control');
                    Route::post('/now-playing', [VideoController::class, 'setNowPlaying'])->name('now-playing');
                });
                
                // Marquee Management
                Route::prefix('marquee')->name('marquee.')->group(function () {
                    Route::get('/', [MarqueeController::class, 'index'])->name('index');
                    Route::get('/list', [MarqueeController::class, 'list'])->name('list');
                    Route::post('/', [MarqueeController::class, 'store'])->name('store');
                    Route::put('/{marquee}', [MarqueeController::class, 'update'])->name('update');
                    Route::post('/{marquee}/toggle', [MarqueeController::class, 'toggleActive'])->name('toggle');
                    Route::delete('/{marquee}', [MarqueeController::class, 'destroy'])->name('destroy');
                });
            });
        
        // ====================================================================
        // COUNTER ROUTES - Requires counter role
        // ====================================================================
        
        Route::prefix('counter')
            ->name('counter.')
            ->middleware('role:counter')
            ->group(function () {
                
                // Counter Panel (main service view)
                Route::get('/panel', [CounterController::class, 'callView'])->name('panel');
                
                // Legacy URL redirect
                Route::get('/view', fn() => redirect()->route('counter.panel', [
                    'organization_code' => request()->route('organization_code')
                ]))->name('view');
                
                // Real-time Data API
                Route::get('/data', [CounterController::class, 'getData'])->name('data');
                
                // Status Toggle (GET for easy toggling)
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