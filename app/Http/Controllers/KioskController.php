<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\QueueService;
use Illuminate\Http\Request;

class KioskController extends Controller
{
    protected $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    public function index()
    {
        $onlineCounters = User::onlineCounters()->get();
        return view('kiosk.index', compact('onlineCounters'));
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

        $queue = $this->queueService->createQueue($counter);

        return response()->json([
            'success' => true,
            'queue' => $queue->load('counter')
        ]);
    }
}
