<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Organization;

class EnsureOrganizationContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $organizationCode = $request->route('organization_code');

        if (!$organizationCode) {
            return response('Organization not found', 404);
        }

        $organization = Organization::findByCode($organizationCode);

        if (!$organization) {
            return response('Organization not found', 404);
        }

        // SuperAdmin can access any organization
        // Admin and Counter can only access their assigned organization
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin() && $user->organization_id && $user->organization_id !== $organization->id) {
            abort(403, 'Unauthorized access to this organization.');
        }

        // Store organization in request and session
        $request->merge(['_organization' => $organization]);
        session(['organization' => $organization]);

        return $next($request);
    }
}
