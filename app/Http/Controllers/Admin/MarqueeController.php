<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarqueeSetting;
use Illuminate\Http\Request;

class MarqueeController extends Controller
{
    public function index()
    {
        $orgCode = request()->route('organization_code');
        $organization = \App\Models\Organization::where('organization_code', $orgCode)->firstOrFail();
        
        $marquees = MarqueeSetting::where('organization_id', $organization->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.marquee.index', compact('marquees'));
    }

    public function list()
    {
        $orgCode = request()->route('organization_code');
        $organization = \App\Models\Organization::where('organization_code', $orgCode)->firstOrFail();
        
        $marquees = MarqueeSetting::where('organization_id', $organization->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['marquees' => $marquees]);
    }

    public function store(Request $request)
    {
        $orgCode = request()->route('organization_code');
        $organization = \App\Models\Organization::where('organization_code', $orgCode)->firstOrFail();
        
        $validated = $request->validate([
            'text' => 'required|string|max:1000',
            'speed' => 'nullable|integer|min:10|max:200',
        ]);

        $marquee = MarqueeSetting::create([
            'text' => $validated['text'],
            'speed' => $validated['speed'] ?? 50,
            'is_active' => true,
            'organization_id' => $organization->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Marquee created successfully',
                'marquee' => $marquee
            ]);
        }

        return redirect()->route('admin.marquee.index', ['organization_code' => request()->route('organization_code')])
            ->with('success', 'Marquee created successfully.');
    }

    public function update(Request $request, MarqueeSetting $marquee)
    {
        $orgCode = request()->route('organization_code');
        $organization = \App\Models\Organization::where('organization_code', $orgCode)->firstOrFail();
        
        // Verify marquee belongs to this organization
        if ($marquee->organization_id !== $organization->id) {
            abort(403, 'Unauthorized');
        }
        
        $validated = $request->validate([
            'text' => 'required|string|max:1000',
            'speed' => 'nullable|integer|min:10|max:200',
        ]);

        $marquee->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Marquee updated successfully',
                'marquee' => $marquee->fresh(),
            ]);
        }

        return redirect()->route('admin.marquee.index', ['organization_code' => request()->route('organization_code')])
            ->with('success', 'Marquee updated successfully.');
    }

    public function toggleActive(MarqueeSetting $marquee)
    {
        $orgCode = request()->route('organization_code');
        $organization = \App\Models\Organization::where('organization_code', $orgCode)->firstOrFail();
        
        // Verify marquee belongs to this organization
        if ($marquee->organization_id !== $organization->id) {
            abort(403, 'Unauthorized');
        }
        
        // Deactivate all others in this organization if activating this one
        if (!$marquee->is_active) {
            MarqueeSetting::where('organization_id', $organization->id)
                ->where('id', '!=', $marquee->id)
                ->update(['is_active' => false]);
        }

        $marquee->update(['is_active' => !$marquee->is_active]);

        return response()->json(['success' => true, 'is_active' => $marquee->is_active]);
    }

    public function destroy(MarqueeSetting $marquee)
    {
        $orgCode = request()->route('organization_code');
        $organization = \App\Models\Organization::where('organization_code', $orgCode)->firstOrFail();
        
        // Verify marquee belongs to this organization
        if ($marquee->organization_id !== $organization->id) {
            abort(403, 'Unauthorized');
        }
        
        $marquee->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Marquee deleted successfully',
            ]);
        }

        return redirect()->route('admin.marquee.index', ['organization_code' => request()->route('organization_code')])
            ->with('success', 'Marquee deleted successfully.');
    }
}
