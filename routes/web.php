<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\MarqueeController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\MonitorController;
use App\Models\User;
use App\Models\Video;
use App\Models\MarqueeSetting;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect('/login');
});

// Login & Auth (no company code in URL)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// SuperAdmin routes (no company code in URL)
Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Company management
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit')->whereNumber('company');
    Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update')->whereNumber('company');
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy')->whereNumber('company');
    
    // User management
    Route::get('/users', [AdminController::class, 'manageUsers'])->name('users.index');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit')->whereNumber('user');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update')->whereNumber('user');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.destroy')->whereNumber('user');
});

// Company-based routes (with company_code prefix)
Route::prefix('{company_code}')->middleware('company.context')->group(function () {
    
    // Kiosk (public)
    Route::prefix('kiosk')->name('kiosk.')->group(function () {
        Route::get('/', [KioskController::class, 'index'])->name('index');
        Route::get('/counters', [KioskController::class, 'counters'])->name('counters');
        Route::post('/generate-queue', [KioskController::class, 'generateQueue'])->name('generate');
    });

    // Monitor Display (public, read-only)
    Route::prefix('monitor')->name('monitor.')->group(function () {
        Route::get('/', [MonitorController::class, 'index'])->name('index');
        Route::get('/data', [MonitorController::class, 'getData'])->name('data');
    });

    // Protected routes
    Route::middleware('auth')->group(function () {
        
        // Admin routes
        Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
            
            // Company Settings
            Route::get('/company-settings', [CompanySettingsController::class, 'edit'])->name('company-settings.edit');
            Route::put('/company-settings', [CompanySettingsController::class, 'update'])->name('company-settings.update');
            Route::delete('/company-settings/logo', [CompanySettingsController::class, 'removeLogo'])->name('company-settings.remove-logo');
            
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
            Route::post('/videos/order', [VideoController::class, 'updateOrder'])->name('videos.order');
            Route::post('/videos/{video}/toggle', [VideoController::class, 'toggleActive'])->name('videos.toggle');
            Route::delete('/videos/{video}', [VideoController::class, 'destroy'])->name('videos.destroy');
            Route::post('/videos/control', [VideoController::class, 'updateControl'])->name('videos.control');
            Route::post('/videos/upload-bell', [VideoController::class, 'uploadBellSound'])->name('videos.upload-bell');
            Route::post('/videos/reset-bell', [VideoController::class, 'resetBellSound'])->name('videos.reset-bell');
            
            // Marquee management
            Route::get('/marquee', [MarqueeController::class, 'index'])->name('marquee.index');
            Route::post('/marquee', [MarqueeController::class, 'store'])->name('marquee.store');
            Route::put('/marquee/{marquee}', [MarqueeController::class, 'update'])->name('marquee.update');
            Route::post('/marquee/{marquee}/toggle', [MarqueeController::class, 'toggleActive'])->name('marquee.toggle');
            Route::delete('/marquee/{marquee}', [MarqueeController::class, 'destroy'])->name('marquee.destroy');
        });
        
        // Counter routes
        Route::middleware('role:counter')->prefix('counter')->name('counter.')->group(function () {
            Route::get('/dashboard', [CounterController::class, 'dashboard'])->name('dashboard');
            // Counter single-frame calling view now at /counter/panel
            Route::get('/panel', [CounterController::class, 'callView'])->name('panel');
            // Backward-compatible redirect from /view to /panel
            Route::get('/view', function () {
                return redirect()->to(route('counter.panel', ['company_code' => request()->route('company_code')]));
            })->name('view');
            Route::get('/data', [CounterController::class, 'getData'])->name('data');
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
