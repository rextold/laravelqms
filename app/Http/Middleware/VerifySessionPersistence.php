<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifySessionPersistence
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Ensure session is started and cookie is set
        if (!session()->has('_token')) {
            session()->put('_token', csrf_token());
        }

        // If user is authenticated, ensure their ID is in the session
        if ($request->user()) {
            session()->put('user_id', $request->user()->id);
        }

        $response = $next($request);

        // Ensure session cookie is always sent with proper configuration
        $response->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', '0');

        return $response;
    }
}