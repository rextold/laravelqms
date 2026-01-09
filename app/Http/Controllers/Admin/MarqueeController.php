<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarqueeSetting;
use Illuminate\Http\Request;

class MarqueeController extends Controller
{
    public function index()
    {
        $marquees = MarqueeSetting::orderBy('created_at', 'desc')->get();
        return view('admin.marquee.index', compact('marquees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:1000',
            'speed' => 'nullable|integer|min:10|max:200',
        ]);

        MarqueeSetting::create([
            'text' => $validated['text'],
            'speed' => $validated['speed'] ?? 50,
            'is_active' => true,
        ]);

        return redirect()->route('admin.marquee.index', ['organization_code' => request()->route('organization_code')])
            ->with('success', 'Marquee created successfully.');
    }

    public function update(Request $request, MarqueeSetting $marquee)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:1000',
            'speed' => 'nullable|integer|min:10|max:200',
        ]);

        $marquee->update($validated);

        return redirect()->route('admin.marquee.index', ['organization_code' => request()->route('organization_code')])
            ->with('success', 'Marquee updated successfully.');
    }

    public function toggleActive(MarqueeSetting $marquee)
    {
        // Deactivate all others if activating this one
        if (!$marquee->is_active) {
            MarqueeSetting::where('id', '!=', $marquee->id)
                ->update(['is_active' => false]);
        }

        $marquee->update(['is_active' => !$marquee->is_active]);

        return response()->json(['success' => true, 'is_active' => $marquee->is_active]);
    }

    public function destroy(MarqueeSetting $marquee)
    {
        $marquee->delete();

        return redirect()->route('admin.marquee.index', ['organization_code' => request()->route('organization_code')])
            ->with('success', 'Marquee deleted successfully.');
    }
}
