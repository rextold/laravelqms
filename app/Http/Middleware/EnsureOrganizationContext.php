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
            return $this->handleMissingRoute($request);
        }

        $organizationCode = $route->parameter('organization_code');

        if (!$organizationCode) {
            return $this->handleMissingOrganizationCode($request);
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
            return $this->handleMissingOrganization($request, $organizationCode);
        }

        // Set organization context for all routes
        $this->setOrganizationContext($request, $organization);

        // For protected routes with authenticated users, verify organization access
        $user = auth()->user();
        
        if ($user) {
            // Check if this is a public route that should be accessible regardless of organization
            if ($this->isPublicRoute($request->route()->getName() ?? '', $request->path())) {
                // Public routes (kiosk, monitor) are accessible to all users
                return $next($request);
            }

            // SuperAdmin can access any organization; regular users must match their assigned organization
            if (!$user->isSuperAdmin() && $user->organization_id && $user->organization_id !== $organization->id) {
                return $this->handleUnauthorizedOrganizationAccess($request, $user, $organization, $organizationCode);
            }
        }

        return $next($request);
    }

    /**
     * Handle missing route scenario
     */
    private function handleMissingRoute(Request $request)
    {
        Log::warning('Route not found', ['url' => $request->url()]);
        
        // Try to redirect to login if it looks like a web request
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Route not found'], 404);
        }
        
        return redirect('/login')->with('error', 'Page not found. Please try again.');
    }

    /**
     * Handle missing organization code in route
     */
    private function handleMissingOrganizationCode(Request $request)
    {
        Log::warning('Organization code missing from route', ['url' => $request->url()]);
        
        // Try to redirect to default organization
        $defaultOrg = Organization::where('is_active', true)->first();
        if ($defaultOrg) {
            $path = $request->path();
            $redirectPath = '/' . strtolower($defaultOrg->organization_code) . '/' . ltrim($path, '/');
            return redirect()->to($redirectPath);
        }
        
        if ($request->expectsJson()) {
            return response()->json(['error' => 'No organization found'], 404);
        }
        
        return redirect('/login')->with('error', 'No organization available. Please contact administrator.');
    }

    /**
     * Handle missing organization scenario with better fallbacks
     */
    private function handleMissingOrganization(Request $request, string $organizationCode)
    {
        Log::warning('Organization not found for code: ' . $organizationCode);
        
        // For API requests, return JSON error
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Organization not found'], 404);
        }
        
        // For web requests, try to redirect to a valid organization
        $defaultOrg = Organization::where('is_active', true)->first();
        if ($defaultOrg && strtolower($defaultOrg->organization_code) !== strtolower($organizationCode)) {
            // Replace the organization code in the URL with the default one
            $segments = $request->segments();
            $segments[0] = strtolower($defaultOrg->organization_code);
            $redirectPath = '/' . implode('/', $segments);
            $queryString = $request->getQueryString();
            if ($queryString) {
                $redirectPath .= '?' . $queryString;
            }
            
            return redirect()->to($redirectPath)->with('warning', 'Organization not found. Redirected to default organization.');
        }
        
        // If no organizations exist at all, redirect to login with error
        return redirect('/login')->with('error', 'Organization not found. Please contact administrator.');
    }

    /**
     * Handle unauthorized organization access with better user experience
     */
    private function handleUnauthorizedOrganizationAccess(Request $request, $user, Organization $organization, string $organizationCode)
    {
        Log::warning('403 Unauthorized organization access attempt', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'user_organization_id' => $user->organization_id,
            'requested_organization_id' => $organization->id,
            'requested_organization_code' => $organizationCode,
        ]);

        // For API requests, return JSON error
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthorized access to this organization'], 403);
        }

        // For web requests, redirect to user's own organization if they have one
        if ($user->organization_id) {
            $userOrg = Organization::find($user->organization_id);
            if ($userOrg && $userOrg->is_active) {
                // Replace the organization code in the URL with user's organization
                $segments = $request->segments();
                $segments[0] = strtolower($userOrg->organization_code);
                $redirectPath = '/' . implode('/', $segments);
                $queryString = $request->getQueryString();
                if ($queryString) {
                    $redirectPath .= '?' . $queryString;
                }
                
                return redirect()->to($redirectPath)->with('warning', 'You can only access your assigned organization.');
            }
        }

        // Fallback: redirect to login with error message
        return redirect('/login')->with('error', 'You do not have access to this organization.');
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
