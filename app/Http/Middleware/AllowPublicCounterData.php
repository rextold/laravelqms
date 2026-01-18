<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AllowPublicCounterData
{
    /**
     * Handle an incoming request.
     *
     * This middleware allows public access to counter data endpoints.
     * It permits access if the user is authenticated OR if it's a public counter request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Get organization from route parameter
        $organization = $request->route('organization');
        
        if (!$organization) {
            Log::warning('AllowPublicCounterData: Organization context not found.', [
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);
        }

        // Allow access if user is authenticated
        $isAuthenticated = Auth::check();
        
        // Allow access for public counter requests (with counter_id parameter)
        $isPublicCounterRequest = $request->has('counter_id');

        if ($isAuthenticated || $isPublicCounterRequest) {
            return $next($request);
        }

        // Log access denial for debugging
        Log::warning('AllowPublicCounterData: Access denied.', [
            'ip' => $request->ip(),
            'organization' => $organization ? $organization->id : 'N/A',
            'is_authenticated' => $isAuthenticated,
            'has_counter_id' => $isPublicCounterRequest,
            'path' => $request->path(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Access denied. Authentication required or provide counter_id parameter.',
            'error' => 'forbidden'
        ], 403);
    }
}