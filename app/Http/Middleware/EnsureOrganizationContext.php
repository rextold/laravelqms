<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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

        $cacheKey = 'org.active.by_code.' . $normalizedCode;
        $organization = Cache::remember($cacheKey, 300, function () use ($normalizedCode) {
            return Organization::findByCode($normalizedCode);
        });

        if (!$organization) {
            Log::warning('Organization not found for code: ' . $organizationCode);
            return response('Organization not found', 404);
        }

        // SuperAdmin can access any organization
        // Admin and Counter can only access their assigned organization
        // Kiosk and Monitor routes are public and don't require authorization
        $user = auth()->user();

        // Always allow kiosk and api.settings routes for public access
        $publicRoutes = ['kiosk.', 'monitor.', 'api.settings', 'counter.data'];
        $routeName = $request->route()->getName() ?? '';
        $isPublicRoute = false;

        foreach ($publicRoutes as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                $isPublicRoute = true;
                break;
            }
        }
        // Explicitly allow /counter/data by URI if route name is missing
        if (str_contains($path, '/counter/data')) {
            $isPublicRoute = true;
        }
        // Explicitly allow all /kiosk and /api/settings routes by URI if route name is missing
        $path = $request->getPathInfo();
        if (str_contains($path, '/kiosk') || str_contains($path, '/api/settings')) {
            $isPublicRoute = true;
        }

        // Normalize organization_code to lowercase for routes and session storage
        $normalizedOrgCode = strtolower($organization->organization_code ?? '');
        $organization->organization_code = $normalizedOrgCode;

        // Store organization on the request (not in input) and keep session payload lightweight.
        $request->attributes->set('organization', $organization);

        $orgContext = [
            'id' => $organization->id,
            'code' => $normalizedOrgCode,
            'name' => $organization->organization_name,
        ];

        $existing = session('organization');
        $needsWrite = !is_array($existing)
            || ($existing['id'] ?? null) !== $orgContext['id']
            || ($existing['code'] ?? null) !== $orgContext['code'];
        if ($needsWrite) {
            session(['organization' => $orgContext]);
        }

        // Public routes: allow regardless of logged-in user/org mismatch
        if ($isPublicRoute) {
            return $next($request);
        }

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

        return $next($request);
    }
}
