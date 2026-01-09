<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CompanySetting;
use App\Services\QueueService;
use Illuminate\Http\Request;

class KioskController extends Controller
{
    protected $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    public function index(Request $request)
    {
        $companyCode = $request->route('company_code');
        $onlineCounters = User::onlineCounters()->get();
        $settings = CompanySetting::getSettings();
        return view('kiosk.index', compact('onlineCounters', 'settings', 'companyCode'));
    }

    public function counters()
    {
        $counters = User::onlineCounters()->get(['id', 'display_name', 'counter_number', 'short_description']);
        return response()->json([
            'counters' => $counters,
        ]);
    }

    public function generateQueue(Request $request)
    {
        $validated = $request->validate([
            'counter_id' => 'required|exists:users,id',
        ]);

        $counter = User::findOrFail($validated['counter_id']);

        // Verify counter is online
        if (!$counter->is_online) {
            return response()->json([
                'success' => false,
                'message' => 'Counter is currently offline'
            ], 422);
        }
        try {
            $queue = $this->queueService->createQueue($counter);

            return response()->json([
                'success' => true,
                'queue' => $queue->load('counter')
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to generate queue number', [
                'counter_id' => $counter->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error generating priority number. Please try again.'
            ], 500);
        }
    }
}
