<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Log;

class EnsureOrganizationContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        if (!$route) {
            return response('Route not found', 404);
        }

        $organizationCode = $route->parameter('organization_code');

        if (!$organizationCode) {
            return response('Organization not found', 404);
        }

        $normalizedCode = strtolower($organizationCode);
        if ($normalizedCode !== $organizationCode) {
            $segments = $request->segments();
            $segments[0] = $normalizedCode;
            $redirectPath = '/' . implode('/', $segments);
            $queryString = $request->getQueryString();
            if ($queryString) {
                $redirectPath .= '?' . $queryString;
            }

            return redirect()->to($redirectPath, 301);
        }

        $organization = Organization::findByCode($organizationCode);

        Log::debug('Organization lookup in middleware', [
            'organization_code' => $organizationCode,
            'organization_found' => $organization ? true : false,
            'organization_id' => $organization?->id,
            'organization_is_active' => $organization?->is_active,
        ]);

        if (!$organization) {
            Log::warning('Organization not found for code: ' . $organizationCode);
            return response('Organization not found', 404);
        }

        // SuperAdmin can access any organization
        // Admin and Counter can only access their assigned organization
        $user = auth()->user();
        Log::debug('User authorization check', [
            'user_id' => $user?->id,
            'user_role' => $user?->role,
            'user_organization_id' => $user?->organization_id,
            'requested_organization_id' => $organization->id,
            'is_superadmin' => $user?->isSuperAdmin(),
        ]);

        if ($user && !$user->isSuperAdmin() && $user->organization_id && $user->organization_id !== $organization->id) {
            Log::warning('403 Unauthorized access attempt', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'user_organization_id' => $user->organization_id,
                'requested_organization_id' => $organization->id,
                'requested_organization_code' => $organizationCode,
            ]);
            abort(403, 'Unauthorized access to this organization.');
        }

        // Store organization in request and session
        $request->merge(['_organization' => $organization]);
        session(['organization' => $organization]);

        return $next($request);
    }
}
