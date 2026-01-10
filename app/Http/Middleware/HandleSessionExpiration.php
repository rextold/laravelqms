<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HandleSessionExpiration
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is not authenticated but was previously (session expired)
        if (!Auth::check() && $request->session()->has('_previous') && !$request->is('login', 'logout')) {
            // Redirect to login with message
            return redirect()->route('login')->withErrors([
                'message' => 'Your session has expired. Please login again.'
            ]);
        }

        // Also redirect unauthenticated users trying to access protected routes

        if (!Auth::check() && $request->route() && !in_array($request->route()->getName(), ['login', 'login.post', 'logout'])) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
