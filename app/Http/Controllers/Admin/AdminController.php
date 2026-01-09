<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        $counters = User::counters()->get();
        $onlineCounters = User::onlineCounters()->get();
        
        // Add companies count for SuperAdmin
        $companiesCount = 0;
        $usersCount = 0;
        if (auth()->user()->isSuperAdmin()) {
            $companiesCount = Company::count();
            $usersCount = User::where('role', '!=', 'superadmin')->count();
        }
        
        return view('admin.dashboard', compact('counters', 'onlineCounters', 'companiesCount', 'usersCount'));
    }

    public function manageUsers()
    {
        $query = User::query();
        
        // SuperAdmin sees only admins, Regular admin only sees their company's users
        if (!auth()->user()->isSuperAdmin()) {
            $query->where('company_id', auth()->user()->company_id)
                  ->where('role', '!=', 'superadmin');
        } else {
            // SuperAdmin can see only admins
            $query->where('role', 'admin');
        }
        
        $users = $query->orderBy('role')->orderBy('counter_number')->get();

        return view('admin.users.index', compact('users'));
    }

    public function createUser()
    {
        // SuperAdmin can only create admins, Regular admin can only create counters
        $roles = auth()->user()->isSuperAdmin() 
            ? ['admin'] 
            : ['counter'];

        // Get all companies for SuperAdmin
        $companies = auth()->user()->isSuperAdmin() 
            ? Company::where('is_active', true)->get() 
            : collect();

        return view('admin.users.create', compact('roles', 'companies'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|unique:users,username|max:255',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(auth()->user()->isSuperAdmin() ? ['admin'] : ['counter'])],
            'display_name' => 'nullable|string|max:255',
            'counter_number' => ['nullable', 'integer', Rule::unique('users')],
            'priority_code' => 'nullable|string|max:20|unique:users,priority_code',
            'short_description' => 'nullable|string|max:255',
            'company_id' => auth()->user()->isSuperAdmin() ? 'required|exists:companies,id' : 'nullable|exists:companies,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        // Automatically assign company based on who is creating
        $admin = auth()->user();
        if ($admin->isSuperAdmin()) {
            // SuperAdmin must select a company (already validated above)
            // company_id is required and comes from the form
        } else {
            // Regular admin - always assign new user to their company
            $validated['company_id'] = $admin->company_id;
        }

        User::create($validated);

        $routePrefix = auth()->user()->isSuperAdmin() ? 'superadmin' : 'admin';
        $routeParams = auth()->user()->isSuperAdmin() ? [] : ['company_code' => request()->route('company_code')];
        return redirect()->route("{$routePrefix}.users.index", $routeParams)
            ->with('success', 'User created successfully.');
    }

    public function editUser($user)
    {
        // Manually resolve the User model
        $user = User::findOrFail($user);
        
        // Prevent non-superadmin from editing superadmin
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        // Prevent SuperAdmin from editing counter accounts
        if (auth()->user()->isSuperAdmin() && $user->isCounter()) {
            abort(403);
        }

        // Prevent admin from editing users outside their company
        $admin = auth()->user();
        if (!$admin->isSuperAdmin() && $user->company_id !== $admin->company_id) {
            abort(403);
        }

        $roles = auth()->user()->isSuperAdmin() 
            ? ['admin'] 
            : ['counter'];

        // Get all companies for SuperAdmin
        $companies = auth()->user()->isSuperAdmin() 
            ? Company::where('is_active', true)->get() 
            : collect();

        return view('admin.users.edit', compact('user', 'roles', 'companies'));
    }

    public function updateUser(Request $request, $user)
    {
        // Manually resolve the User model
        $user = User::findOrFail($user);
        
        // Prevent non-superadmin from updating superadmin
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        // Prevent SuperAdmin from updating counter accounts
        if (auth()->user()->isSuperAdmin() && $user->isCounter()) {
            abort(403);
        }

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => ['required', Rule::in(auth()->user()->isSuperAdmin() ? ['admin'] : ['counter'])],
            'display_name' => 'nullable|string|max:255',
            'counter_number' => ['nullable', 'integer', Rule::unique('users')->ignore($user->id)],
            'priority_code' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'short_description' => 'nullable|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // If role is not counter, clear counter-specific fields
        if ($validated['role'] !== 'counter') {
            $validated['display_name'] = null;
            $validated['counter_number'] = null;
            $validated['priority_code'] = null;
        }

        // Handle company assignment
        $admin = auth()->user();
        if (!$admin->isSuperAdmin()) {
            // Regular admin cannot change company assignment
            $validated['company_id'] = $user->company_id;
        } else {
            // SuperAdmin can modify company assignment
            if (!isset($validated['company_id'])) {
                // If not provided, keep current assignment
                $validated['company_id'] = $user->company_id;
            }
        }

        $user->update($validated);

        $routePrefix = auth()->user()->isSuperAdmin() ? 'superadmin' : 'admin';
        $routeParams = auth()->user()->isSuperAdmin() ? [] : ['company_code' => request()->route('company_code')];
        return redirect()->route("{$routePrefix}.users.index", $routeParams)
            ->with('success', 'User updated successfully.');
    }

    public function deleteUser($user)
    {
        // Manually resolve the User model
        $user = User::findOrFail($user);
        
        // Prevent non-superadmin from deleting superadmin
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        // Prevent SuperAdmin from deleting counter accounts
        if (auth()->user()->isSuperAdmin() && $user->isCounter()) {
            abort(403);
        }

        // Prevent admin from deleting users outside their company
        $admin = auth()->user();
        if (!$admin->isSuperAdmin() && $user->company_id !== $admin->company_id) {
            abort(403);
        }

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        $routePrefix = auth()->user()->isSuperAdmin() ? 'superadmin' : 'admin';
        $routeParams = auth()->user()->isSuperAdmin() ? [] : ['company_code' => request()->route('company_code')];
        return redirect()->route("{$routePrefix}.users.index", $routeParams)
            ->with('success', 'User deleted successfully.');
    }
}
