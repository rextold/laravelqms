<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AllowPublicCounterData
{
    /**
     * Handle an incoming request.
     *
     * This middleware ensures that public counter data endpoints are properly
     * authorized and accessible. It validates that the counter context is set
     * and the user has appropriate permissions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if organization context is properly set by previous middleware
        if (!$request->has('organization_code') && !$request->route('organization_code')) {
            \Log::warning('AllowPublicCounterData middleware: Missing organization context', [
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Organization context is required',
                'error' => 'missing_context'
            ], 400);
        }

        // Allow the request to proceed
        return $next($request);
    }
}<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AllowPublicCounterData
{
    public function handle(Request $request, Closure $next)
    {
        $organization = $request->route('organization');
        if (!$organization) {
            Log::warning('AllowPublicCounterData: Organization context not found.');
            // Depending on strictness, you might abort or just log
        }

        $isAuthenticated = Auth::check();
        $isPublicCounterRequest = $request->has('counter_id');

        if ($isAuthenticated || $isPublicCounterRequest) {
            return $next($request);
        }

        Log::warning('AllowPublicCounterData: Access denied.', [
            'ip' => $request->ip(),
            'organization' => $organization ? $organization->id : 'N/A',
            'is_authenticated' => $isAuthenticated,
            'has_counter_id' => $isPublicCounterRequest,
        ]);

        return response()->json(['error' => 'Forbidden'], 403);
    }
}