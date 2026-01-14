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

        // Normalize organization code to lowercase
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

        // Retrieve organization from cache or database
        $cacheKey = 'org.active.by_code.' . $normalizedCode;
        $organization = Cache::remember($cacheKey, 300, function () use ($normalizedCode) {
            return Organization::findByCode($normalizedCode);
        });

        if (!$organization) {
            Log::warning('Organization not found for code: ' . $organizationCode);
            return response('Organization not found', 404);
        }

        // Set organization context for all routes
        $this->setOrganizationContext($request, $organization);

        // For protected routes with authenticated users, verify organization access
        $user = auth()->user();
        
        if ($user) {
            // Public routes like Kiosk and Monitor do not require this check
            $routeName = $request->route()->getName();
            if ($this->isPublicRoute($routeName, $request->path())) {
                return $next($request);
            }

            // SuperAdmin can access any organization; regular users must match their assigned organization
            if (!$user->isSuperAdmin() && $user->organization_id && $user->organization_id !== $organization->id) {
                Log::warning('403 Unauthorized organization access attempt', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'user_role' => $user->role,
                    'user_organization_id' => $user->organization_id,
                    'requested_organization_id' => $organization->id,
                    'requested_organization_code' => $organizationCode,
                ]);
                abort(403, 'Unauthorized access to this organization.');
            }
        }

        return $next($request);
    }

    private function isPublicRoute(string $routeName, string $path): bool
    {
        $publicRoutes = ['kiosk.', 'monitor.', 'api.settings'];
        
        foreach ($publicRoutes as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return true;
            }
        }

        if (
            preg_match('#/[a-z0-9_-]+/kiosk#i', $path)
            || preg_match('#/[a-z0-9_-]+/api/settings#i', $path)
            || preg_match('#/[a-z0-9_-]+/monitor($|/|/data)#i', $path)
        ) {
            return true;
        }

        return false;
    }

    private function setOrganizationContext(Request $request, Organization $organization): void
    {
        $normalizedOrgCode = strtolower($organization->organization_code ?? '');
        $organization->organization_code = $normalizedOrgCode;

        // Store organization on request
        $request->attributes->set('organization', $organization);

        // Store in session for access across requests
        $orgContext = [
            'id' => $organization->id,
            'code' => $normalizedOrgCode,
            'name' => $organization->organization_name,
        ];
        session(['organization' => $orgContext]);
    }
}