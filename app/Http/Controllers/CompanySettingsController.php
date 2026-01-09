<?php

namespace App\Http\Controllers;

use App\Models\OrganizationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingsController extends Controller
{
    public function edit()
    {
        $settings = OrganizationSetting::getSettings();
        return view('admin.organization-settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'accent_color' => 'required|string|max:7',
            'text_color' => 'required|string|max:7',
            'queue_number_digits' => 'required|integer|min:3|max:6',
            'logo_path' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        $settings = OrganizationSetting::getSettings();

        // Handle logo upload
        if ($request->hasFile('logo_path')) {
            // Delete old logo
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }

            $logoPath = $request->file('logo_path')->store('logos', 'public');
            $validated['logo_path'] = $logoPath;
        }

        $settings->update($validated);

        return redirect()->back()->with('success', 'Organization settings updated successfully!');
    }

    public function removeLogo()
    {
        $settings = OrganizationSetting::getSettings();
        
        if ($settings->logo_path) {
            Storage::disk('public')->delete($settings->logo_path);
            $settings->update(['logo_path' => null]);
        }

        return redirect()->back()->with('success', 'Logo removed successfully!');
    }
}
