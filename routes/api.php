<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QueueApiController;
use App\Http\Controllers\Api\CounterApiController;
use App\Http\Controllers\Api\MonitorApiController;

// Default user route (keep for compatibility)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// CSRF Token endpoint
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->middleware('web');

// Organization-based API routes
Route::prefix('v1/{organization_code}')->middleware(['organization.context'])->group(function () {
    
    // Queue Management API
    Route::prefix('queues')->group(function () {
        Route::post('/generate', [QueueApiController::class, 'generateQueue']);
        Route::get('/status', [QueueApiController::class, 'getQueueStatus']);
        Route::get('/waiting', [QueueApiController::class, 'getWaitingQueues']);
    });
    
    // Counter Management API
    Route::prefix('counters')->group(function () {
        Route::get('/online', [CounterApiController::class, 'getOnlineCounters']);
        Route::get('/{counter_id}/data', [CounterApiController::class, 'getCounterData']);
        
        // Protected counter operations (require authentication)
        Route::middleware(['auth'])->group(function () {
            Route::post('/{counter_id}/toggle-online', [CounterApiController::class, 'toggleOnlineStatus']);
            Route::post('/{counter_id}/call-next', [CounterApiController::class, 'callNext']);
            Route::post('/{counter_id}/move-next', [CounterApiController::class, 'moveToNext']);
            Route::post('/transfer-queue', [CounterApiController::class, 'transferQueue']);
        });
    });
    
    // Monitor Display API
    Route::prefix('monitor')->group(function () {
        Route::get('/data', [MonitorApiController::class, 'getMonitorData']);
        Route::get('/videos', [MonitorApiController::class, 'getVideoPlaylist']);
        Route::get('/settings', [MonitorApiController::class, 'getOrganizationSettings']);
    });
});

// Global API routes (without organization context)
Route::prefix('v1')->group(function () {
    
    // Health check endpoint
    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'message' => 'API is healthy',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0'
        ]);
    });
    
    // System status endpoint
    Route::get('/status', function () {
        return response()->json([
            'success' => true,
            'message' => 'Laravel QMS API',
            'version' => '1.0.0',
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'timestamp' => now()->toISOString(),
        ]);
    });
});
