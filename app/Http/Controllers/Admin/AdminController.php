<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        $counters = User::counters()->get();
        $onlineCounters = User::onlineCounters()->get();
        
        return view('admin.dashboard', compact('counters', 'onlineCounters'));
    }

    public function manageUsers()
    {
        $users = User::when(!auth()->user()->isSuperAdmin(), function($query) {
            return $query->where('role', '!=', 'superadmin');
        })->orderBy('role')->orderBy('counter_number')->get();

        return view('admin.users.index', compact('users'));
    }

    public function createUser()
    {
        $roles = auth()->user()->isSuperAdmin() 
            ? ['admin', 'counter'] 
            : ['counter'];

        return view('admin.users.create', compact('roles'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|unique:users,username|max:255',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(auth()->user()->isSuperAdmin() ? ['admin', 'counter'] : ['counter'])],
            'display_name' => 'required_if:role,counter|string|max:255',
            'counter_number' => 'required_if:role,counter|integer|unique:users,counter_number',
            'short_description' => 'nullable|string|max:255',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function editUser(User $user)
    {
        // Prevent non-superadmin from editing superadmin
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $roles = auth()->user()->isSuperAdmin() 
            ? ['admin', 'counter'] 
            : ['counter'];

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function updateUser(Request $request, User $user)
    {
        // Prevent non-superadmin from updating superadmin
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => ['required', Rule::in(auth()->user()->isSuperAdmin() ? ['admin', 'counter'] : ['counter'])],
            'display_name' => 'required_if:role,counter|string|max:255',
            'counter_number' => ['required_if:role,counter', 'integer', Rule::unique('users')->ignore($user->id)],
            'short_description' => 'nullable|string|max:255',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function deleteUser(User $user)
    {
        // Prevent non-superadmin from deleting superadmin
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
