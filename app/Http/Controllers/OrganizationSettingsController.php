<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Intervention\Image\ImageManagerStatic as Image;

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

        try {
            $validated = $request->validate([
                'organization_name' => 'required|string|max:255',
                'organization_phone' => 'nullable|string|max:255',
                'organization_email' => 'nullable|email|max:255',
                'organization_address' => 'nullable|string|max:500',
                'primary_color' => 'nullable|regex:/^#[0-9A-F]{6}$/i',
                'secondary_color' => 'nullable|regex:/^#[0-9A-F]{6}$/i',
                'accent_color' => 'nullable|regex:/^#[0-9A-F]{6}$/i',
                'text_color' => 'nullable|regex:/^#[0-9A-F]{6}$/i',
                'queue_number_digits' => 'nullable|integer|min:1|max:10',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        // Update organization data
        $organization->update([
            'organization_name' => $validated['organization_name'],
        ]);

        // Get or create settings
        $settings = OrganizationSetting::where('organization_id', $organization->id)->first();
        if (!$settings) {
            $settings = new OrganizationSetting(['organization_id' => $organization->id]);
        }

        // Some schemas require a non-null/unique code column
        if (!$settings->exists && empty($settings->code) && Schema::hasColumn('organization_settings', 'code')) {
            $settings->code = $organization->organization_code;
        }

        // Handle logo upload with compression
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $ext = $file->getClientOriginalExtension();
            $newName = uniqid() . '_' . $originalName . '.' . $ext;
            
            \Log::info('Logo upload started', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
            ]);
            
            // Delete old logo if exists
            if ($settings->organization_logo) {
                \Storage::disk('public')->delete($settings->organization_logo);
            }
            
            // Read file and compress with Intervention Image
            $img = Image::make($file->getRealPath());
            
            // Resize if too large (max 400x400)
            if ($img->width() > 400 || $img->height() > 400) {
                $img->resize(400, 400, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            // Set quality based on format
            $quality = 85; // Default quality for compression
            if (in_array(strtolower($ext), ['jpg', 'jpeg'])) {
                $quality = 80; // Slightly lower for JPG
            }
            
            // Encode image
            $img->encode($ext, $quality);
            
            // Store compressed image
            $storagePath = 'logos/' . $newName;
            \Storage::disk('public')->put($storagePath, (string) $img);
            
            $logoPath = $storagePath;
            
            $compressedSize = \Storage::disk('public')->size($storagePath);
            \Log::info('Logo compressed and stored', [
                'path' => $storagePath,
                'original_size' => $file->getSize(),
                'compressed_size' => $compressedSize,
                'reduction' => round((1 - $compressedSize / $file->getSize()) * 100, 2) . '%'
            ]);
        }

        // Update organization_logo if new logo was uploaded
        if ($logoPath) {
            $settings->organization_logo = $logoPath;
        }
        
        // Update settings using actual database column names
        $settings->organization_phone = $validated['organization_phone'] ?? null;
        $settings->organization_email = $validated['organization_email'] ?? null;
        $settings->organization_address = $validated['organization_address'] ?? null;
        $settings->primary_color = $validated['primary_color'];
        $settings->secondary_color = $validated['secondary_color'];
        $settings->accent_color = $validated['accent_color'];
        $settings->text_color = $validated['text_color'];
        $settings->queue_number_digits = $validated['queue_number_digits'];
        
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
            'organization_phone' => $settings->organization_phone,
            'organization_email' => $settings->organization_email,
            'organization_address' => $settings->organization_address,
        ]);
    }

    // API endpoint for organization settings (used by monitor)
    public function getSettingsApi(Request $request)
    {
        $organization_code = $request->route('organization_code') ?? $request->query('organization_code');
        $organization = Organization::where('organization_code', $organization_code)->first();

        if (!$organization) {
            return response()->json(['error' => 'Organization not found'], 404);
        }

        $settings = $organization->setting ?? new OrganizationSetting(['organization_id' => $organization->id]);
        return response()->json([
            'organization' => $organization,
            'settings' => $settings,
            // Notification display settings
            'notify_title' => $settings->notify_title ?? 'Now Calling',
            'notify_message' => $settings->notify_message ?? 'Please proceed to the counter',
            'serve_title' => $settings->serve_title ?? 'Now Serving',
            'serve_message' => $settings->serve_message ?? 'Please proceed to Counter {counter}',
        ]);
    }}
