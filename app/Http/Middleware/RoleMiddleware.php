<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect('/login');
        }

        // Normalize roles to be case-insensitive and allow aliases (e.g., teller == counter)
        $userRole = strtolower(trim($request->user()->role));
        if ($userRole === 'teller') {
            $userRole = 'counter';
        }

        $allowedRoles = array_map(function ($role) {
            $normalized = strtolower(trim($role));
            return $normalized === 'teller' ? 'counter' : $normalized;
        }, $roles);

        if (!in_array($userRole, $allowedRoles, true)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
