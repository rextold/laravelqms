<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\Organization;
use App\Models\User;

class AuthController extends Controller
{
    protected function putOrganizationContextInSession(Request $request, Organization $organization): void
    {
        $request->session()->put('organization', [
            'id' => $organization->id,
            'code' => strtolower($organization->organization_code ?? ''),
            'name' => $organization->organization_name,
        ]);
    }

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
            'username' => 'required|string|exists:users,username',
            'password' => 'required|string|min:6',
        ], [
            'username.exists' => 'No account found for this username.',
            'password.min' => 'Password must be at least 6 characters.',
        ]);

        // Simple rate limiting: max 5 attempts per minute per username+IP
        $throttleKey = Str::lower($request->input('username')).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors([
                'username' => 'Too many login attempts. Please try again in a minute.',
            ])->onlyInput('username');
        }

        // Try to authenticate with username and password only
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Verify account is active only if the column exists
            $attributes = method_exists($user, 'getAttributes') ? $user->getAttributes() : [];
            if (array_key_exists('is_active', $attributes)) {
                $isActive = (bool) $user->getAttribute('is_active');
                if (!$isActive) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'username' => 'This account is inactive. Please contact the administrator.',
                    ])->onlyInput('username');
                }
            }

            // Regenerate session AFTER auth check succeeds
            $request->session()->regenerate();
            
            // Ensure CSRF token is available
            if (!$request->session()->has('_token')) {
                $request->session()->put('_token', csrf_token());
            }

            RateLimiter::clear($throttleKey);

            return $this->redirectByRole($user, $request);
        }

        RateLimiter::hit($throttleKey, 60);
        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Set counter offline on logout (both for GET and POST)
        if ($user && $user->isCounter()) {
            try {
                $user->update(['is_online' => false]);
            } catch (\Exception $e) {
                Log::warning('Failed to set counter offline on logout: ' . $e->getMessage());
                // Continue with logout even if offline update fails
            }
        }

        Auth::logout();
        
        // Safely invalidate session
        try {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } catch (\Exception $e) {
            Log::warning('Session invalidation error: ' . $e->getMessage());
        }

        return redirect(route('login'))->with('message', 'You have been logged out successfully.');
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

            // Ensure organization_code is available before building route and normalize to lowercase
            $orgCode = strtolower($organization->organization_code ?? '');
            if (empty($orgCode)) {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'User organization code is not configured. Please contact administrator.',
                ])->onlyInput('username');
            }

            $this->putOrganizationContextInSession($request, $organization);

            // Always send admins to their dashboard to prevent cross-role redirect loops
            try {
                $url = route('admin.dashboard', ['organization_code' => $orgCode]);
            } catch (\Exception $e) {
                // Fallback: logout and show error if route generation fails
                Log::error('Failed to generate admin dashboard route', ['exception' => $e->getMessage(), 'user_id' => $user->id ?? null, 'organization_code' => $orgCode]);
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Unable to determine dashboard URL. Please contact administrator.',
                ])->onlyInput('username');
            }

            return redirect()->to($url);
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
            $this->putOrganizationContextInSession($request, $organization);

            // Counters go straight to their service panel; skip intended URLs to avoid role-mismatch 403s
            $orgCode = strtolower($organization->organization_code ?? '');
            return redirect()->to(route('counter.panel', ['organization_code' => $orgCode]));
        }

        // Fallback
        return redirect('/');
    }

    protected function throttleKey(Request $request): string
    {
        return Str::lower($request->input('username')).'|'.$request->ip();
    }
}