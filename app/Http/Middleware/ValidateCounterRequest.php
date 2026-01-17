<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Closure;

class ValidateCounterRequest
{
    /**
     * Handle an incoming request for counter operations.
     * Validates both HTTP method and authorization.
     */
    public function handle(Request $request, Closure $next, $method = 'POST')
    {
        // Validate HTTP method
        if (!$request->isMethod($method)) {
            return response()->json([
                'success' => false,
                'error' => 'Method Not Allowed',
                'message' => "This endpoint only accepts {$method} requests"
            ], 405);
        }

        // Validate authentication
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'Authentication required'
            ], 401);
        }

        // Validate counter role
        if (!$user->isCounter()) {
            return response()->json([
                'success' => false,
                'error' => 'Forbidden',
                'message' => 'Counter role required to access this resource'
            ], 403);
        }

        // Validate online status for action endpoints
        if (in_array($request->path(), [
            $request->route('organization_code') . '/counter/call-next',
            $request->route('organization_code') . '/counter/move-next',
            $request->route('organization_code') . '/counter/transfer',
            $request->route('organization_code') . '/counter/notify',
            $request->route('organization_code') . '/counter/skip',
            $request->route('organization_code') . '/counter/recall',
        ])) {
            if (!$user->is_online) {
                return response()->json([
                    'success' => false,
                    'error' => 'Forbidden',
                    'message' => 'Counter must be online to perform this action'
                ], 403);
            }
        }

        return $next($request);
    }
}