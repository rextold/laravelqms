<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to handle Monitor public access BEFORE any authentication or CSRF checks
 * This ensures Monitor routes NEVER get a 403 Forbidden error
 */
class HandleMonitorPublicAccess
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
        // Check if this is a Monitor or Kiosk route
        if ($this->isPublicRoute($request)) {
            // Handle OPTIONS preflight request
            if ($request->isMethod('OPTIONS')) {
                return response('', 200)
                    ->header('Access-Control-Allow-Origin', '*')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, X-Requested-With, X-CSRF-Token, Authorization')
                    ->header('Access-Control-Max-Age', '3600');
            }
            
            // Allow request to proceed and add CORS headers to response
            $response = $next($request);
            
            return $response
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, X-Requested-With, X-CSRF-Token, Authorization')
                ->header('Access-Control-Max-Age', '3600');
        }
        
        return $next($request);
    }
    
    /**
     * Determine if the request is for a public route (Monitor or Kiosk)
     */
    protected function isPublicRoute(Request $request): bool
    {
        $path = $request->path();
        
        // Match monitor routes: {org}/monitor or {org}/monitor/data
        if (preg_match('#^[^/]+/monitor(/.*)?$#', $path)) {
            return true;
        }
        
        // Match kiosk routes
        if (preg_match('#^[^/]+/kiosk(/.*)?$#', $path)) {
            return true;
        }
        
        return false;
    }
}