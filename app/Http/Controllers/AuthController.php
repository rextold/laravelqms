<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            $user = Auth::user();

            // Redirect based on role
            if ($user->isSuperAdmin() || $user->isAdmin()) {
                return redirect()->intended('/admin/dashboard');
            } elseif ($user->isCounter()) {
                // Set counter online on login
                $user->update(['is_online' => true]);
                return redirect()->intended('/counter/dashboard');
            }
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

        return redirect('/login');
    }
}
