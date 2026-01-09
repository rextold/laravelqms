<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $this->redirectByRole(Auth::guard($guard)->user());
            }
        }

        return $next($request);
    }

    protected function redirectByRole($user)
    {
        if (!$user) {
            return redirect('/');
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard');
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            $company = $user->company;
            if ($company) {
                return redirect()->route('admin.dashboard', ['company_code' => $company->company_code]);
            }
            return redirect('/');
        }

        if (method_exists($user, 'isCounter') && $user->isCounter()) {
            $company = $user->company;
            if ($company) {
                return redirect()->route('counter.dashboard', ['company_code' => $company->company_code]);
            }
            return redirect('/');
        }

        return redirect('/');
    }
}
