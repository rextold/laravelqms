<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationSetting;
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
        $organization = Organization::where('organization_code', $request->route('organization_code'))->firstOrFail();        
        $onlineCounters = User::onlineCounters()->where('organization_id', $organization->id)->get();
        $settings = OrganizationSetting::where('organization_id', $organization->id)->first();
        
        // Create default settings if none exist
        if (!$settings) {
            $settings = OrganizationSetting::create([
                'organization_id' => $organization->id,
                'code' => $organization->organization_code,
                'primary_color' => '#3b82f6',
                'secondary_color' => '#8b5cf6',
                'accent_color' => '#10b981',
                'text_color' => '#ffffff',
                'queue_number_digits' => 4,
                'is_active' => true,
            ]);
        }
        
        $companyCode = $organization->organization_code;
        return view('kiosk.index', compact('onlineCounters', 'settings', 'organization', 'companyCode'));
    }    public function counters(Request $request)
    {
        $organization = Organization::where('organization_code', $request->route('organization_code'))->firstOrFail();

        $counters = User::onlineCounters()
            ->where('organization_id', $organization->id)
            ->get(['id', 'display_name', 'counter_number', 'short_description']);
        return response()->json([
            'counters' => $counters,
            'timestamp' => now()->toISOString(),
            'status' => 'success'
        ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function getQueueStatus(Request $request)
    {
        $organization = Organization::where('organization_code', $request->route('organization_code'))->firstOrFail();
        $queueId = $request->query('queue_id');
        
        if (!$queueId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Queue ID is required'
            ], 400);
        }

        $queue = \App\Models\Queue::where('id', $queueId)
            ->where('organization_id', $organization->id)
            ->with('counter:id,counter_number,display_name')
            ->first();

        if (!$queue) {
            return response()->json([
                'status' => 'error',
                'message' => 'Queue not found'
            ], 404);
        }

        // Get position in queue for waiting status
        $position = null;
        if ($queue->status === 'waiting') {
            $position = \App\Models\Queue::where('counter_id', $queue->counter_id)
                ->where('status', 'waiting')
                ->where('created_at', '<', $queue->created_at)
                ->count() + 1;
        }

        // Get estimated wait time (rough calculation: 3 minutes per person ahead)
        $estimatedWaitMinutes = $position ? ($position - 1) * 3 : 0;

        return response()->json([
            'status' => 'success',
            'queue' => [
                'id' => $queue->id,
                'queue_number' => $queue->queue_number,
                'status' => $queue->status,
                'position' => $position,
                'estimated_wait_minutes' => $estimatedWaitMinutes,
                'counter' => $queue->counter,
                'created_at' => $queue->created_at->toISOString(),
                'called_at' => $queue->called_at?->toISOString(),
                'served_at' => $queue->served_at?->toISOString(),
            ],
            'timestamp' => now()->toISOString()
        ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function generateQueue(Request $request)
    {
        $organization = Organization::where('organization_code', $request->route('organization_code'))->firstOrFail();
        $validated = $request->validate([
            'counter_id' => 'required|exists:users,id',
        ]);

        $counter = User::findOrFail($validated['counter_id']);

        if ((int) $counter->organization_id !== (int) $organization->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid counter for this organization'
            ], 422);
        }

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
