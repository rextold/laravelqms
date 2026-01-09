<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Organization;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        // If already authenticated, send user to their dashboard
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Try to authenticate with username and password only
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            return $this->redirectByRole($user, $request);
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Set counter offline on logout
        if ($user && $user->isCounter()) {
            $user->update(['is_online' => false]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('login'));
    }

    /**
     * Redirect user based on role and organization context.
     */
    protected function redirectByRole($user, Request $request = null)
    {
        $request = $request ?? request();

        if ($user->isSuperAdmin()) {
            // Force superadmins to their home; avoid stale intended URLs
            return redirect()->to(route('superadmin.dashboard'));
        }

        if ($user->isAdmin()) {
            $organization = $user->organization;
            if (!$organization) {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'User organization not assigned. Please contact administrator.',
                ])->onlyInput('username');
            }
            $request->session()->put('organization', $organization);
            // Always send admins to their dashboard to prevent cross-role redirect loops
            return redirect()->to(route('admin.dashboard', ['organization_code' => $organization->organization_code]));
        }

        if ($user->isCounter()) {
            $organization = $user->organization;
            if (!$organization) {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'User organization not assigned. Please contact administrator.',
                ])->onlyInput('username');
            }
            // Set counter online on login
            $user->update(['is_online' => true]);
            $request->session()->put('organization', $organization);
            // Counters go straight to their dashboard; skip intended URLs to avoid role-mismatch 403s
            return redirect()->to(route('counter.dashboard', ['organization_code' => $organization->organization_code]));
        }

        // Fallback
        return redirect('/');
    }
}