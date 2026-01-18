<?php

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