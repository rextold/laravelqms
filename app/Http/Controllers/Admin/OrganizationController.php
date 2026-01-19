<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrganizationController extends Controller
{
    public function index()
    {
        // Only SuperAdmin can access this
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $organizations = Organization::with('setting')->orderBy('created_at', 'desc')->get();
        return view('admin.organizations.index', compact('organizations'));
    }

    public function create()
    {
        // Only SuperAdmin can access this
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        return view('admin.organizations.create');
    }

    public function store(Request $request)
    {
        // Only SuperAdmin can access this
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        // Log incoming request for debugging validation issues
        Log::debug('OrganizationController@store - incoming', $request->all());

        $rules = [
            'organization_code' => 'required|string|unique:organizations,organization_code|max:50|alpha_dash',
            'organization_name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            Log::warning('OrganizationController@store - validation failed', ['errors' => $validator->errors()->toArray()]);
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $validated['organization_code'] = strtoupper($validated['organization_code']);
        $validated['is_active'] = $request->has('is_active');

        $organization = Organization::create($validated);

        // Create default organization settings
        OrganizationSetting::create([
            'organization_id' => $organization->id,
            'code' => $organization->organization_code,
            'primary_color' => '#4F46E5',
            'secondary_color' => '#10B981',
            'accent_color' => '#F59E0B',
            'text_color' => '#1F2937',
            'queue_number_digits' => 4,
            'is_active' => true,
        ]);

        return redirect()->route('superadmin.organizations.index')
            ->with('success', 'Organization created successfully.');
    }

    public function edit($organization)
    {
        // Only SuperAdmin can access this
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $organization = Organization::findOrFail($organization);
        return view('admin.organizations.edit', compact('organization'));
    }

    public function update(Request $request, $organization)
    {
        // Only SuperAdmin can access this
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $organization = Organization::findOrFail($organization);

        $validated = $request->validate([
            'organization_code' => 'required|string|max:50|alpha_dash|unique:organizations,organization_code,' . $organization->id,
            'organization_name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['organization_code'] = strtoupper($validated['organization_code']);
        $validated['is_active'] = $request->has('is_active');

        $organization->update($validated);

        // Update organization settings if exists
        if ($organization->setting) {
            $organization->setting->update([
                'code' => $validated['organization_code'],
            ]);
        }

        return redirect()->route('superadmin.organizations.index')
            ->with('success', 'Organization updated successfully.');
    }

    public function destroy($organization)
    {
        // Only SuperAdmin can access this
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $organization = Organization::findOrFail($organization);

        // Prevent deletion if organization has users
        if ($organization->users()->count() > 0) {
            return back()->with('error', 'Cannot delete organization with existing users. Please reassign or delete users first.');
        }

        $organization->delete();

        return redirect()->route('superadmin.organizations.index')
            ->with('success', 'Organization deleted successfully.');
    }

    /**
     * Reset sequences for all organizations (superadmin)
     */
    public function resetAllSequences()
    {
        // Only SuperAdmin can access this
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $organizations = Organization::with('setting')->get();
        $userId = auth()->id();
        $today = now()->toDateString();

        foreach ($organizations as $org) {
            $settings = $org->setting;
            if (!$settings) {
                $settings = OrganizationSetting::create([
                    'organization_id' => $org->id,
                    'code' => $org->organization_code,
                    'queue_number_digits' => 4,
                    'is_active' => true,
                ]);
            }

            $previous = $settings->last_queue_sequence ?? 0;
            $settings->last_queue_sequence = 0;
            $settings->last_queue_sequence_date = $today;
            $settings->save();

            try {
                \App\Models\QueueResetLog::create([
                    'organization_id' => $org->id,
                    'user_id' => $userId,
                    'previous_sequence' => $previous,
                    'reset_to' => 0,
                    'note' => 'Bulk reset via superadmin',
                ]);
            } catch (\Throwable $e) {
                \Log::warning('Failed to create queue reset audit log (bulk)', ['error' => $e->getMessage(), 'organization_id' => $org->id]);
            }
        }

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'All organization queue sequences have been reset.']);
        }

        return redirect()->route('superadmin.organizations.index')->with('success', 'All organization queue sequences have been reset.');
    }
}
