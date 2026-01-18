<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class AllowPublicCounterData
{
    /**
     * Allow public access to counter data endpoint without authentication.
     * Used for kiosk and monitor displays.
     */
    public function handle(Request $request, Closure $next)
    {
        // If counter_id is provided, allow public access
        if ($request->query('counter_id')) {
            return $next($request);
        }

        // Otherwise, continue with normal request processing
        return $next($request);
    }
}