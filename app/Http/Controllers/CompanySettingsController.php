<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingsController extends Controller
{
    public function edit()
    {
        $settings = CompanySetting::getSettings();
        return view('admin.company-settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'accent_color' => 'required|string|max:7',
            'text_color' => 'required|string|max:7',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'queue_number_digits' => 'required|integer|min:3|max:6',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        $settings = CompanySetting::getSettings();

        // Handle logo upload
        if ($request->hasFile('company_logo')) {
            // Delete old logo
            if ($settings->company_logo) {
                Storage::disk('public')->delete($settings->company_logo);
            }

            $logoPath = $request->file('company_logo')->store('logos', 'public');
            $validated['company_logo'] = $logoPath;
        }

        $settings->update($validated);

        return redirect()->back()->with('success', 'Company settings updated successfully!');
    }

    public function removeLogo()
    {
        $settings = CompanySetting::getSettings();
        
        if ($settings->company_logo) {
            Storage::disk('public')->delete($settings->company_logo);
            $settings->update(['company_logo' => null]);
        }

        return redirect()->back()->with('success', 'Logo removed successfully!');
    }
}
