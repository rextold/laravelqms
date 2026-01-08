<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\MarqueeController;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\MonitorController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Kiosk (public)
Route::prefix('kiosk')->name('kiosk.')->group(function () {
    Route::get('/', [KioskController::class, 'index'])->name('index');
    Route::post('/generate-queue', [KioskController::class, 'generateQueue'])->name('generate');
});

// Monitor Display (public, read-only)
Route::prefix('monitor')->name('monitor.')->group(function () {
    Route::get('/', [MonitorController::class, 'index'])->name('index');
    Route::get('/data', [MonitorController::class, 'getData'])->name('data');
});

// Protected routes
Route::middleware('auth')->group(function () {
    
    // Admin & SuperAdmin routes
    Route::middleware('role:admin,superadmin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // User management
        Route::get('/users', [AdminController::class, 'manageUsers'])->name('users.index');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.destroy');
        
        // Video management
        Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
        Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
        Route::post('/videos/order', [VideoController::class, 'updateOrder'])->name('videos.order');
        Route::post('/videos/{video}/toggle', [VideoController::class, 'toggleActive'])->name('videos.toggle');
        Route::delete('/videos/{video}', [VideoController::class, 'destroy'])->name('videos.destroy');
        Route::post('/videos/control', [VideoController::class, 'updateControl'])->name('videos.control');
        
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
        Route::post('/toggle-online', [CounterController::class, 'toggleOnline'])->name('toggle-online');
        Route::post('/call-next', [CounterController::class, 'callNext'])->name('call-next');
        Route::post('/move-next', [CounterController::class, 'moveToNext'])->name('move-next');
        Route::post('/transfer', [CounterController::class, 'transferQueue'])->name('transfer');
    });
});
