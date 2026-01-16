<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\QueueService;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    protected $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    public function dashboard()
    {
        $counter = auth()->user();
        $stats = $this->queueService->getCounterStats($counter);
        $waitingQueues = $counter->getWaitingQueues();
        $onlineCounters = User::onlineCounters()
            ->where('id', '!=', $counter->id)
            ->get();

        return view('counter.dashboard', compact('counter', 'stats', 'waitingQueues', 'onlineCounters'));
    }

    public function toggleOnline(Request $request)
    {
        $counter = auth()->user();
        $counter->update(['is_online' => !$counter->is_online]);

        return response()->json([
            'success' => true,
            'is_online' => $counter->is_online
        ]);
    }
}
    public function callNext()
    {
        $counter = auth()->user();
        $queue = $this->queueService->callNextQueue($counter);

        if (!$queue) {
            return response()->json([
                'success' => false,
                'message' => 'No queues waiting'
            ]);
        }

        return response()->json([
            'success' => true,
            'queue' => $queue
        ]);
    }

    public function moveToNext()
    {
        $counter = auth()->user();
        $queue = $this->queueService->moveToNext($counter);

        return response()->json([
            'success' => true,
            'queue' => $queue
        ]);
    }

    public function transferQueue(Request $request)
    {
        $validated = $request->validate([
            'queue_id' => 'required|exists:queues,id',
            'to_counter_id' => 'required|exists:users,id',
        ]);

        $queue = \App\Models\Queue::findOrFail($validated['queue_id']);
        $toCounter = User::findOrFail($validated['to_counter_id']);

        // Verify target counter is online
        if (!$toCounter->is_online) {
            return response()->json([
                'success' => false,
                'message' => 'Target counter is offline'
            ], 422);
        }

        $newQueue = $this->queueService->transferQueue($queue, $toCounter);

        return response()->json([
            'success' => true,
            'queue' => $newQueue
        ]);
    }
}