<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        // Counter routes
        '*/counter/data',
        '*/counter/notify',
        
        // Monitor routes - Allow all AJAX calls without authentication
        '*/monitor',
        '*/monitor/*',
        '*/monitor/data',
        
        // Kiosk routes - Public ticket generation
        '*/kiosk',
        '*/kiosk/*',
        '*/kiosk/generate-queue',
        '*/kiosk/counters',
        '*/kiosk/verify-ticket',
    ];
}