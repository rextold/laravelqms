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
            'company_phone' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_address' => 'nullable|string|max:500',
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
            
            if ($settings->organization_logo) {
                \Storage::disk('public')->delete($settings->organization_logo);
            }
            
            // Store file synchronously for immediate availability
            $path = $file->storeAs('logos', uniqid() . '_' . $file->getClientOriginalName(), 'public');
            $validated['organization_logo'] = $path;
            
            \Log::info('Logo stored successfully', ['path' => $path]);
        }

        // Only save settings-specific fields to organization_settings table
        $settings->fill([
            'company_phone' => $validated['company_phone'] ?? null,
            'company_email' => $validated['company_email'] ?? null,
            'company_address' => $validated['company_address'] ?? null,
            'primary_color' => $validated['primary_color'],
            'secondary_color' => $validated['secondary_color'],
            'accent_color' => $validated['accent_color'],
            'text_color' => $validated['text_color'],
            'queue_number_digits' => $validated['queue_number_digits'],
            'organization_logo' => $validated['organization_logo'] ?? $settings->organization_logo,
        ]);
        $settings->save();

        // Return JSON for AJAX requests for real-time updates
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
                'settings' => [
                    'primary_color' => $settings->primary_color,
                    'secondary_color' => $settings->secondary_color,
                    'accent_color' => $settings->accent_color,
                    'text_color' => $settings->text_color,
                    'organization_logo' => $settings->organization_logo ? asset('storage/' . $settings->organization_logo) : null,
                ]
            ]);
        }

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
        
        if ($settings && $settings->organization_logo) {
            // Delete the file from storage
            \Storage::disk('public')->delete($settings->organization_logo);
            
            // Clear the logo path in database
            $settings->organization_logo = null;
            $settings->save();
            
            return redirect()->back()
                ->with('success', 'Logo removed successfully.');
        }

        return redirect()->back()
            ->with('error', 'No logo to remove.');
    }

    /**
     * Get organization settings as JSON for real-time updates
     */
    public function getSettings($organization_code)
    {
        $organization = Organization::where('organization_code', $organization_code)->firstOrFail();
        
        $settings = OrganizationSetting::where('organization_id', $organization->id)->first();
        
        if (!$settings) {
            return response()->json(['error' => 'Settings not found'], 404);
        }

        return response()->json([
            'organization_name' => $organization->organization_name,
            'primary_color' => $settings->primary_color,
            'secondary_color' => $settings->secondary_color,
            'accent_color' => $settings->accent_color,
            'text_color' => $settings->text_color,
            'organization_logo' => $settings->organization_logo ? asset('storage/' . $settings->organization_logo) : null,
            'company_phone' => $settings->company_phone,
            'company_email' => $settings->company_email,
            'company_address' => $settings->company_address,
        ]);
    }
}
