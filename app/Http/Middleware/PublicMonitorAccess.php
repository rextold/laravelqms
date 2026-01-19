<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure Monitor routes are completely public
 * - No authentication required
 * - No CSRF verification
 * - CORS headers enabled
 * - Works with AJAX calls from any origin
 */
class PublicMonitorAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow the request to proceed without authentication
        // The presence of this middleware marks the route as public
        
        $response = $next($request);
        
        // Add CORS headers to allow cross-origin requests
        return $response
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, X-Requested-With, Authorization')
            ->header('Access-Control-Max-Age', '3600');
    }
}