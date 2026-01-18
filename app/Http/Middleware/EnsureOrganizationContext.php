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

        // Public routes (kiosk, monitor) - allow access without any user checks
        if ($this->isPublicRoute($request)) {
            return $next($request);
        }

        // For authenticated users on non-public routes, verify organization access
        $user = auth()->user();

        if ($user) {
            // SuperAdmin can access any organization
            if ($user->isSuperAdmin()) {
                return $next($request);
            }
            
            // Regular users must belong to the organization they're accessing
            if ($user->organization_id && $user->organization_id !== $organization->id) {
                Log::warning('403 Unauthorized organization access attempt', [
                    'user_id' => $user->id,
                    'user_email' => $user->email ?? 'N/A',
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

    private function isPublicRoute(Request $request): bool
    {
        // Check for allow.public middleware
        if (in_array('allow.public', $request->route()->middleware())) {
            return true;
        }
        
        // Explicitly allow monitor and kiosk routes
        $routeName = $request->route()->getName() ?? '';
        $publicRoutePatterns = [
            'monitor.',   // All monitor routes
            'kiosk.',     // All kiosk routes
        ];
        
        foreach ($publicRoutePatterns as $pattern) {
            if (str_starts_with($routeName, $pattern)) {
                return true;
            }
        }
        
        // Also check path for public routes (fallback)
        $path = $request->getPathInfo();
        $publicPathPatterns = [
            '/monitor',
            '/kiosk',
        ];
        
        foreach ($publicPathPatterns as $pattern) {
            if (str_contains($path, $pattern)) {
                return true;
            }
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