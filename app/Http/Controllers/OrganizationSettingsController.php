<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationSetting;
use Illuminate\Http\Request;

class OrganizationSettingsController extends Controller
{
    public function edit($organization_code)
    {
        // Get organization by code from URL
        $organization = Organization::where('organization_code', $organization_code)->firstOrFail();

        // Only Admin and SuperAdmin can edit settings
        if (!auth()->user()->isSuperAdmin() && auth()->user()->organization_id !== $organization->id) {
            abort(403);
        }

        $settings = $organization->setting ?? new OrganizationSetting(['organization_id' => $organization->id]);
        return view('admin.organization-settings', compact('organization', 'settings'));
    }

    public function update(Request $request, $organization_code)
    {
        // Get organization by code from URL
        $organization = Organization::where('organization_code', $organization_code)->firstOrFail();

        // Only Admin and SuperAdmin can update settings
        if (!auth()->user()->isSuperAdmin() && auth()->user()->organization_id !== $organization->id) {
            abort(403);
        }

        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
            'organization_phone' => 'nullable|string|max:255',
            'organization_email' => 'nullable|email|max:255',
            'organization_address' => 'nullable|string|max:500',
            'primary_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
            'secondary_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
            'accent_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
            'text_color' => 'required|regex:/^#[0-9A-F]{6}$/i',
            'queue_number_digits' => 'required|integer|min:1|max:10',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Update organization data
        $organization->update([
            'organization_name' => $validated['organization_name'],
        ]);

        // Get or create settings
        $settings = OrganizationSetting::where('organization_id', $organization->id)->first();
        if (!$settings) {
            $settings = new OrganizationSetting(['organization_id' => $organization->id]);
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            
            \Log::info('Logo upload started', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ]);
            
            if ($settings->logo_path) {
                \Storage::disk('public')->delete($settings->logo_path);
            }
            
            // Store file synchronously for immediate availability
            $path = $file->storeAs('logos', uniqid() . '_' . $file->getClientOriginalName(), 'public');
            $validated['logo_path'] = $path;
            
            \Log::info('Logo stored successfully', ['path' => $path]);
        }

        // Only save settings-specific fields to organization_settings table
        $settings->fill([
            'organization_phone' => $validated['organization_phone'] ?? null,
            'organization_email' => $validated['organization_email'] ?? null,
            'organization_address' => $validated['organization_address'] ?? null,
            'primary_color' => $validated['primary_color'],
            'secondary_color' => $validated['secondary_color'],
            'accent_color' => $validated['accent_color'],
            'text_color' => $validated['text_color'],
            'queue_number_digits' => $validated['queue_number_digits'],
            'logo_path' => $validated['logo_path'] ?? $settings->logo_path,
        ]);
        $settings->save();

        return redirect()->back()
            ->with('success', 'Settings updated successfully.');
    }

    public function removeLogo($organization_code)
    {
        // Get organization by code from URL
        $organization = Organization::where('organization_code', $organization_code)->firstOrFail();

        // Only Admin and SuperAdmin can remove logo
        if (!auth()->user()->isSuperAdmin() && auth()->user()->organization_id !== $organization->id) {
            abort(403);
        }

        $settings = OrganizationSetting::where('organization_id', $organization->id)->first();
        
        if ($settings && $settings->logo_path) {
            // Delete the file from storage
            \Storage::disk('public')->delete($settings->logo_path);
            
            // Clear the logo path in database
            $settings->logo_path = null;
            $settings->save();
            
            return redirect()->back()
                ->with('success', 'Logo removed successfully.');
        }

        return redirect()->back()
            ->with('error', 'No logo to remove.');
    }
}
