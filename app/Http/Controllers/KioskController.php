<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Queue as QueueModel;
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
        $companyCode = $request->route('organization_code');
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
        
        return view('kiosk.index', compact('onlineCounters', 'settings', 'companyCode', 'organization'));
    }    
    public function counters(Request $request)
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

    public function generateQueue(Request $request)
    {
        $organization = Organization::where('organization_code', $request->route('organization_code'))->firstOrFail();
        
        // Get counter_id from query string (GET parameter)
        $counterId = $request->query('counter_id');
        
        if (!$counterId) {
            return response()->json([
                'success' => false,
                'message' => 'The counter id field is required.'
            ], 422);
        }

        // Verify counter exists
        $counter = User::where('id', $counterId)->first();
        if (!$counter) {
            return response()->json([
                'success' => false,
                'message' => 'Counter not found'
            ], 404);
        }

        // Verify counter belongs to organization
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

            // Create a short HMAC signature for tamper-detection on tickets.
            // Use queue_number, id and created_at to bind the signature to this record.
            $payload = sprintf('%s|%s|%s', $queue->queue_number, $queue->id, $queue->created_at->timestamp ?? time());
            $key = config('app.key') ?? env('APP_KEY');
            $signature = hash_hmac('sha256', $payload, $key);

            $queueData = $queue->load('counter')->toArray();
            $queueData['signature'] = $signature;

            return response()->json([
                'success' => true,
                'queue' => $queueData
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

    /**
     * Verify a ticket signature and return validation result.
     * Public endpoint used by scanners or verification tools.
     */
    public function verifyTicket(Request $request)
    {
        $organization = Organization::where('organization_code', $request->route('organization_code'))->firstOrFail();

        $queueNumber = $request->query('queue_number');
        $signature = $request->query('signature');

        if (!$queueNumber || !$signature) {
            return response()->json(['valid' => false, 'message' => 'Missing queue_number or signature'], 422);
        }

        $queue = QueueModel::where('queue_number', $queueNumber)
            ->where('organization_id', $organization->id)
            ->first();

        if (!$queue) {
            return response()->json(['valid' => false, 'message' => 'Ticket not found'], 404);
        }

        $payload = sprintf('%s|%s|%s', $queue->queue_number, $queue->id, $queue->created_at->timestamp ?? time());
        $key = config('app.key') ?? env('APP_KEY');
        $expected = hash_hmac('sha256', $payload, $key);

        if (hash_equals($expected, $signature)) {
            return response()->json(['valid' => true, 'message' => 'Signature valid', 'queue' => $queue]);
        }

        return response()->json(['valid' => false, 'message' => 'Invalid signature'], 200);
    }
}
