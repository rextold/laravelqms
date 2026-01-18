<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Closure;

class AllowPublicCounterData
{
    /**
     * Allow public access to counter data endpoint without authentication.
     * Also allows authenticated users regardless of query parameters.
     */
    public function handle(Request $request, Closure $next)
    {
        // Allow authenticated users unconditionally
        if (Auth::check()) {
            return $next($request);
        }

        // Allow public access if counter_id is provided
        if ($request->query('counter_id')) {
            return $next($request);
        }

        // Fallback to default behavior (which may result in a 403 if no other auth middleware runs)
        return $next($request);
    }
}