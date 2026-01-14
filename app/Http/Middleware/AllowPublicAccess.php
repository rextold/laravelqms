<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowPublicAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // This middleware doesn't need to do anything.
        // Its presence is just a marker.
        return $next($request);
    }
}