<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;

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
    
    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        // First check parent implementation
        if (parent::inExceptArray($request)) {
            return true;
        }
        
        // Additional check using regex for Monitor and Kiosk routes
        $path = $request->path();
        
        // Exclude all monitor routes (public display)
        if (preg_match('#^[^/]+/monitor(/.*)?$#', $path)) {
            return true;
        }
        
        // Exclude all kiosk routes (public ticket generation)
        if (preg_match('#^[^/]+/kiosk(/.*)?$#', $path)) {
            return true;
        }
        
        return false;
    }
}