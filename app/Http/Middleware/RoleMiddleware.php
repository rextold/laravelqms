<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect('/login');
        }

        // Normalize roles to be case-insensitive and allow aliases (e.g., teller == counter)
        $userRole = strtolower(trim($user->role));
        if ($userRole === 'teller') {
            $userRole = 'counter';
        }

        $allowedRoles = array_map(function ($role) {
            $normalized = strtolower(trim($role));
            return $normalized === 'teller' ? 'counter' : $normalized;
        }, $roles);

        $routeName = $request->route()->getName() ?? '';
        $pathInfo = $request->getPathInfo();

        // Allow public/monitored routes without role checking
        if (str_starts_with($routeName, 'kiosk.') || str_contains($pathInfo, '/kiosk')
            || str_starts_with($routeName, 'monitor.') || str_contains($pathInfo, '/monitor')) {
            return $next($request);
        }

        // Check if user has the required role
        if (!in_array($userRole, $allowedRoles, true)) {
            // For public displays (kiosk, monitor), don't abort - allow graceful degradation
            if (str_contains($pathInfo, '/kiosk') || str_contains($pathInfo, '/monitor')) {
                return $next($request);
            }
            abort(403, 'Unauthorized: Your role (' . $user->role . ') does not have access to this resource.');
        }

        // For organization-based routes, verify user organization matches
        $org = $request->attributes->get('organization');
        if ($org && $user->organization_id && $user->organization_id != $org->id) {
            // For public displays (kiosk, monitor), don't abort - allow graceful degradation
            if (str_contains($pathInfo, '/kiosk') || str_contains($pathInfo, '/monitor')) {
                return $next($request);
            }
            abort(403, 'Unauthorized: You do not have access to this organization.');
        }

        return $next($request);
    }
}
