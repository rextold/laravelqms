<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        $admin = auth()->user();
        
        // Filter counters by organization for regular admins
        if ($admin->isSuperAdmin()) {
            $counters = User::counters()->get();
            $onlineCounters = User::onlineCounters()->get();
            $companiesCount = Organization::count();
            $usersCount = User::where('role', '!=', 'superadmin')->count();
            $adminsCount = User::where('role', 'admin')->count();
            $countersCount = User::where('role', 'counter')->count();
            
            // Get top organizations by counter count
            $topOrganizations = Organization::withCount(['users' => function($q) {
                $q->where('role', 'counter');
            }])
            ->orderBy('users_count', 'desc')
            ->limit(5)
            ->get();
            
            // Get all admins with their organizations
            $admins = User::where('role', 'admin')->with('organization')->get();
            
            // Queue statistics
            $todayQueues = \App\Models\Queue::whereDate('created_at', today())->count();
            $waitingQueues = \App\Models\Queue::where('status', 'waiting')->count();
            $completedToday = \App\Models\Queue::where('status', 'completed')->whereDate('updated_at', today())->count();
            $servingNow = \App\Models\Queue::where('status', 'serving')->count();
        } else {
            // Admin only sees counters in their organization
            $counters = User::counters()
                ->where('organization_id', $admin->organization_id)
                ->get();
            $onlineCounters = User::onlineCounters()
                ->where('organization_id', $admin->organization_id)
                ->get();
            $companiesCount = 0;
            $usersCount = 0;
            $adminsCount = 0;
            $countersCount = $counters->count();
            $topOrganizations = collect();
            $admins = collect();
            
            // Queue statistics for this organization
            $todayQueues = \App\Models\Queue::whereHas('counter', function($q) use ($admin) {
                $q->where('organization_id', $admin->organization_id);
            })->whereDate('created_at', today())->count();
            
            $waitingQueues = \App\Models\Queue::where('status', 'waiting')
                ->whereHas('counter', function($q) use ($admin) {
                    $q->where('organization_id', $admin->organization_id);
                })->count();
            
            $completedToday = \App\Models\Queue::where('status', 'completed')
                ->whereHas('counter', function($q) use ($admin) {
                    $q->where('organization_id', $admin->organization_id);
                })
                ->whereDate('updated_at', today())->count();
            
            $servingNow = \App\Models\Queue::where('status', 'serving')
                ->whereHas('counter', function($q) use ($admin) {
                    $q->where('organization_id', $admin->organization_id);
                })->count();
            
            // Get organization info
            $organization = $admin->organization;
        }
        
        return view('admin.dashboard', compact(
            'counters', 
            'onlineCounters', 
            'companiesCount', 
            'usersCount',
            'adminsCount',
            'countersCount',
            'topOrganizations',
            'admins',
            'todayQueues',
            'waitingQueues',
            'completedToday',
            'servingNow'
        ) + ($admin->isSuperAdmin() ? [] : ['organization' => $organization]));
    }

    public function manageUsers()
    {
        $query = User::query();
        
        // SuperAdmin sees only admins; Admin sees only counters in their organization
        if (!auth()->user()->isSuperAdmin()) {
            $query->where('organization_id', auth()->user()->organization_id)
                  ->where('role', 'counter');
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

        // Get all organizations for SuperAdmin
        $organizations = auth()->user()->isSuperAdmin() 
            ? Organization::where('is_active', true)->get() 
            : collect();

        return view('admin.users.create', compact('roles', 'organizations'));
    }

    public function storeUser(Request $request)
    {
        $admin = auth()->user();
        
        // Build validation rules
        $rules = [
            'username' => 'required|string|unique:users,username|max:255',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in($admin->isSuperAdmin() ? ['admin'] : ['counter'])],
            'organization_id' => $admin->isSuperAdmin() ? 'required|exists:organizations,id' : 'nullable|exists:organizations,id',
        ];

        // Add counter-specific validation when role is counter
        if ($request->role === 'counter') {
            $rules['display_name'] = 'required|string|max:255';
            // Counter number must be unique within the organization
            $organizationId = $admin->isSuperAdmin() ? $request->organization_id : $admin->organization_id;
            $rules['counter_number'] = [
                'required', 
                'integer', 
                Rule::unique('users', 'counter_number')->where('organization_id', $organizationId)
            ];
            $rules['priority_code'] = [
                'nullable', 
                'string', 
                'max:20', 
                Rule::unique('users', 'priority_code')->where('organization_id', $organizationId)
            ];
            $rules['short_description'] = 'nullable|string|max:255';
        } else {
            $rules['display_name'] = 'nullable|string|max:255';
            $rules['counter_number'] = 'nullable|integer';
            $rules['priority_code'] = 'nullable|string|max:20';
            $rules['short_description'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules, [
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'role.required' => 'Role is required.',
            'display_name.required' => 'Display name is required for counter users.',
            'counter_number.required' => 'Counter number is required for counter users.',
            'counter_number.unique' => 'This counter number is already in use.',
            'priority_code.unique' => 'This priority code is already in use.',
            'organization_id.required' => 'Organization is required.',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        // Automatically assign organization based on who is creating
        if ($admin->isSuperAdmin()) {
            // SuperAdmin must select an organization (already validated above)
            // organization_id is required and comes from the form
        } else {
            // Regular admin - always assign new user to their organization
            $validated['organization_id'] = $admin->organization_id;
        }
        
        // Final validation: Counter MUST have organization_id
        if ($validated['role'] === 'counter' && empty($validated['organization_id'])) {
            return back()->withErrors(['organization_id' => 'Counter must be assigned to an organization.'])->withInput();
        }

        User::create($validated);

        $routePrefix = auth()->user()->isSuperAdmin() ? 'superadmin' : 'admin';
        $routeParams = auth()->user()->isSuperAdmin() ? [] : ['organization_code' => request()->route('organization_code')];
        return redirect()->route("{$routePrefix}.users.index", $routeParams)
            ->with('success', 'User created successfully.');
    }

    public function editUser($organization_code = null, $user = null)
    {
        // Handle both superadmin (no org code) and admin (with org code) routes
        if ($user === null) {
            // SuperAdmin route: only one parameter (the user ID)
            $user = $organization_code;
            $organization_code = null;
        }
        
        // Manually resolve the User model
        $user = User::findOrFail($user);
        
        $admin = auth()->user();
        
        // Prevent non-superadmin from editing superadmin
        if ($user->isSuperAdmin() && !$admin->isSuperAdmin()) {
            abort(403, 'Unauthorized to edit superadmin.');
        }

        // Prevent SuperAdmin from editing counter accounts
        if ($admin->isSuperAdmin() && $user->isCounter()) {
            abort(403, 'SuperAdmin cannot edit counters.');
        }

        // Prevent admin from editing admin accounts (only counters)
        if (!$admin->isSuperAdmin() && $user->isAdmin()) {
            abort(403, 'Admin cannot edit other admins.');
        }

        // Prevent admin from editing users outside their organization
        if (!$admin->isSuperAdmin()) {
            if ($user->organization_id !== $admin->organization_id) {
                abort(403, 'Cannot edit users from other organizations.');
            }
        }

        $roles = $admin->isSuperAdmin() 
            ? ['admin'] 
            : ['counter'];

        // Get all organizations for SuperAdmin
        $organizations = $admin->isSuperAdmin() 
            ? Organization::where('is_active', true)->get() 
            : collect();

        return view('admin.users.edit', compact('user', 'roles', 'organizations'));
    }

    public function updateUser(Request $request, $organization_code = null, $user = null)
    {
        // Handle both superadmin (no org code) and admin (with org code) routes
        if ($user === null) {
            // SuperAdmin route: only one parameter (the user ID)
            $user = $organization_code;
            $organization_code = null;
        }
        
        // Manually resolve the User model
        $user = User::findOrFail($user);
        
        $admin = auth()->user();
        
        // Prevent non-superadmin from updating superadmin
        if ($user->isSuperAdmin() && !$admin->isSuperAdmin()) {
            abort(403, 'Unauthorized to update superadmin.');
        }

        // Prevent SuperAdmin from updating counter accounts
        if ($admin->isSuperAdmin() && $user->isCounter()) {
            abort(403, 'SuperAdmin cannot update counters.');
        }

        // Prevent admin from updating admin accounts (only counters)
        if (!$admin->isSuperAdmin() && $user->isAdmin()) {
            abort(403, 'Admin cannot update other admins.');
        }

        // Prevent admin from updating users outside their organization
        if (!$admin->isSuperAdmin()) {
            if ($user->organization_id !== $admin->organization_id) {
                abort(403, 'Cannot update users from other organizations.');
            }
        }

        // Build validation rules
        $rules = [
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => ['required', Rule::in(auth()->user()->isSuperAdmin() ? ['admin'] : ['counter'])],
            'organization_id' => 'nullable|exists:organizations,id',
        ];

        // Add counter-specific validation when role is counter
        if ($request->role === 'counter') {
            $rules['display_name'] = 'required|string|max:255';
            // Counter number must be unique within the organization
            $organizationId = $user->organization_id;
            $rules['counter_number'] = [
                'required', 
                'integer', 
                Rule::unique('users', 'counter_number')
                    ->where('organization_id', $organizationId)
                    ->ignore($user->id)
            ];
            $rules['priority_code'] = [
                'nullable', 
                'string', 
                'max:20', 
                Rule::unique('users', 'priority_code')
                    ->where('organization_id', $organizationId)
                    ->ignore($user->id)
            ];
            $rules['short_description'] = 'nullable|string|max:255';
        } else {
            $rules['display_name'] = 'nullable|string|max:255';
            $rules['counter_number'] = ['nullable', 'integer', Rule::unique('users', 'counter_number')->ignore($user->id)];
            $rules['priority_code'] = ['nullable', 'string', 'max:20', Rule::unique('users', 'priority_code')->ignore($user->id)];
            $rules['short_description'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules, [
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'password.min' => 'Password must be at least 6 characters.',
            'role.required' => 'Role is required.',
            'display_name.required' => 'Display name is required for counter users.',
            'counter_number.required' => 'Counter number is required for counter users.',
            'counter_number.unique' => 'This counter number is already in use.',
            'priority_code.unique' => 'This priority code is already in use.',
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

        // Handle organization assignment
        if (!$admin->isSuperAdmin()) {
            // Regular admin cannot change organization assignment
            $validated['organization_id'] = $user->organization_id;
        } else {
            // SuperAdmin can modify organization assignment
            if (!isset($validated['organization_id'])) {
                // If not provided, keep current assignment
                $validated['organization_id'] = $user->organization_id;
            }
        }
        
        // Final validation: Counter MUST have organization_id
        if ($validated['role'] === 'counter' && empty($validated['organization_id'])) {
            return back()->withErrors(['organization_id' => 'Counter must be assigned to an organization.'])->withInput();
        }

        $user->update($validated);

        $routePrefix = $admin->isSuperAdmin() ? 'superadmin' : 'admin';
        $routeParams = $admin->isSuperAdmin() ? [] : ['organization_code' => request()->route('organization_code')];
        return redirect()->route("{$routePrefix}.users.index", $routeParams)
            ->with('success', 'User updated successfully.');
    }

    public function deleteUser($organization_code = null, $user = null)
    {
        // Handle both superadmin (no org code) and admin (with org code) routes
        if ($user === null) {
            // SuperAdmin route: only one parameter (the user ID)
            $user = $organization_code;
            $organization_code = null;
        }
        
        // Manually resolve the User model
        $user = User::findOrFail($user);
        $admin = auth()->user();
        
        // Use policy for authorization
        if (!$admin->can('delete', $user)) {
            abort(403, 'You are not authorized to delete this user.');
        }
        
        try {
            $username = $user->username;
            $user->delete();
            
            $routePrefix = $admin->isSuperAdmin() ? 'superadmin' : 'admin';
            $routeParams = $admin->isSuperAdmin() ? [] : ['organization_code' => $organization_code ?? request()->route('organization_code')];
            
            return redirect()->route("{$routePrefix}.users.index", $routeParams)
                ->with('success', "Counter '{$username}' has been deleted successfully.");
                
        } catch (\Exception $e) {
            $routePrefix = $admin->isSuperAdmin() ? 'superadmin' : 'admin';
            $routeParams = $admin->isSuperAdmin() ? [] : ['organization_code' => $organization_code ?? request()->route('organization_code')];
            
            return redirect()->route("{$routePrefix}.users.index", $routeParams)
                ->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}
