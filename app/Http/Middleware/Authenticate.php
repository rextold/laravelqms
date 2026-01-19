<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Don't redirect for Monitor or Kiosk routes - they're public
        if ($this->isPublicRoute($request)) {
            return null;
        }
        
        return $request->expectsJson() ? null : route('login');
    }
    
    /**
     * Determine if the request is for a public route
     */
    protected function isPublicRoute(Request $request): bool
    {
        $path = $request->path();
        
        // Allow monitor routes
        if (preg_match('#^[^/]+/monitor(/.*)?$#', $path)) {
            return true;
        }
        
        // Allow kiosk routes
        if (preg_match('#^[^/]+/kiosk(/.*)?$#', $path)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle an unauthenticated user.
     */
    protected function unauthenticated($request, array $guards)
    {
        // Allow public routes to pass through without authentication
        if ($this->isPublicRoute($request)) {
            return;
        }
        
        parent::unauthenticated($request, $guards);
    }
}