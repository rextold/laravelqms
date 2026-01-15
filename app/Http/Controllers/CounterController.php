<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Queue;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Events\CounterStatusUpdated;

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
        $skippedQueues = $counter->getSkippedQueues();
        $onlineCounters = User::onlineCounters()
            ->where('id', '!=', $counter->id)
            ->when($counter->organization_id, fn ($q) => $q->where('organization_id', $counter->organization_id))
            ->get();

        // Get analytics data for reports
        $analyticsData = $this->getAnalyticsData($counter);

        return view('counter.dashboard', compact('counter', 'stats', 'waitingQueues', 'skippedQueues', 'onlineCounters', 'analyticsData'));
    }

    public function callView()
    {
        $counter = auth()->user();
        $stats = $this->queueService->getCounterStats($counter);
        $settings = \App\Models\OrganizationSetting::getSettings();
        $organization = $counter->organization;
        return view('counter.call', compact('counter', 'stats', 'settings', 'organization'));
    }

    public function getData()
    {
        $counter = auth()->user();

        $buildPayload = function () use ($counter) {
            $stats = $this->queueService->getCounterStats($counter);
            $current = $counter->getCurrentQueue();
            $waiting = $counter->getWaitingQueues();
            $skipped = $counter->getSkippedQueues();
            $onlineCounters = User::onlineCounters()
                ->where('id', '!=', $counter->id)
                ->when($counter->organization_id, fn ($q) => $q->where('organization_id', $counter->organization_id))
                ->get(['id', 'counter_number', 'display_name']);

            return [
                'success' => true,
                'is_online' => (bool) $counter->is_online,
                'stats' => [
                    'waiting' => $stats['waiting'],
                    'completed_today' => $stats['completed_today'],
                ],
                'current_queue' => $current ? [
                    'id' => $current->id,
                    'queue_number' => $current->queue_number,
                    'status' => $current->status,
                    'called_at' => optional($current->called_at)->toDateTimeString(),
                    'notified_at' => optional($current->notified_at)->toDateTimeString(),
                ] : null,
                'waiting_queues' => $waiting->map(fn($q) => [
                    'id' => $q->id,
                    'queue_number' => $q->queue_number,
                ])->values(),
                'skipped' => $skipped->map(fn($q) => [
                    'id' => $q->id,
                    'queue_number' => $q->queue_number,
                    'skipped_at' => optional($q->skipped_at)->toDateTimeString(),
                ])->values(),
                'online_counters' => $onlineCounters,
            ];
        };

        // Database-backed cache can be slower/stale for real-time polling; only cache on fast stores.
        $cacheStore = (string) config('cache.default');
        if ($cacheStore !== 'database') {
            $cacheKey = "counter.data.{$counter->id}";
            $payload = Cache::remember($cacheKey, 1, $buildPayload);
        } else {
            $payload = $buildPayload();
        }

        return response()->json($payload)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function toggleOnline(Request $request)
    {
        $counter = auth()->user();
        $counter->is_online = !$counter->is_online;
        $counter->save();
        // Broadcast status update
        event(new CounterStatusUpdated($counter->organization->organization_code, $counter->id, $counter->is_online ? 'online' : 'offline'));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_online' => $counter->is_online
            ]);
        }

        return redirect()->back()->with('status', $counter->is_online ? 'You are now Online' : 'You are now Offline');
    }

    public function callNext()
    {
        $counter = auth()->user();
        $queue = $this->queueService->callNextQueue($counter);
        
        // Invalidate cache after state change
        $this->invalidateCounterCache($counter);

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
        
        // Invalidate cache after state change
        $this->invalidateCounterCache($counter);

        return response()->json([
            'success' => true,
            'queue' => $queue,
            'message' => 'Completed. Press Call Next to serve the next customer.'
        ]);
    }

    private function invalidateCounterCache(User $counter)
    {
        \Illuminate\Support\Facades\Cache::forget("counter.data.{$counter->id}");
    }

    public function transferQueue(Request $request)
    {
        $counter = auth()->user();
        $validated = $request->validate([
            'queue_id' => 'required|exists:queues,id',
            'to_counter_id' => 'required|exists:users,id',
        ]);

        $queue = \App\Models\Queue::findOrFail($validated['queue_id']);
        $toCounter = User::findOrFail($validated['to_counter_id']);

        // Verify this queue belongs to the current counter
        if ($queue->counter_id !== $counter->id) {
            return response()->json([
                'success' => false,
                'message' => 'This queue does not belong to your counter'
            ], 422);
        }

        // Ensure same organization (allow legacy queues with null organization_id)
        if ($counter->organization_id && ($toCounter->organization_id !== $counter->organization_id || ($queue->organization_id !== null && $queue->organization_id !== $counter->organization_id))) {
            return response()->json([
                'success' => false,
                'message' => 'Transfer is only allowed within your organization'
            ], 422);
        }

        // Only transfer to other counters
        if (!$toCounter->isCounter()) {
            return response()->json([
                'success' => false,
                'message' => 'Target user is not a counter'
            ], 422);
        }

        // Verify target counter is online
        if (!$toCounter->is_online) {
            return response()->json([
                'success' => false,
                'message' => 'Target counter is offline'
            ], 422);
        }

        // Verify target counter is different from current counter
        if ($toCounter->id === $counter->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot transfer to the same counter'
            ], 422);
        }

        $newQueue = $this->queueService->transferQueue($queue, $toCounter);
        
        // Invalidate cache for both counters
        $this->invalidateCounterCache($counter);
        $this->invalidateCounterCache($toCounter);

        return response()->json([
            'success' => true,
            'queue' => $newQueue
        ]);
    }

    public function notifyCustomer(Request $request)
    {
        $counter = auth()->user();
        $queue = $counter->getCurrentQueue();

        if (!$queue) {
            return response()->json([
                'success' => false,
                'message' => 'No queue currently serving'
            ]);
        }

        $queue->update(['notified_at' => now()]);
        
        // Invalidate cache
        $this->invalidateCounterCache($counter);

        return response()->json([
            'success' => true,
            'queue' => $queue
        ]);
    }

    public function skipQueue(Request $request)
    {
        $counter = auth()->user();
        $queue = $counter->getCurrentQueue();

        if (!$queue) {
            return response()->json([
                'success' => false,
                'message' => 'No queue currently serving'
            ]);
        }

        $queue->update([
            'status' => 'skipped',
            'skipped_at' => now()
        ]);
        
        // Invalidate cache
        $this->invalidateCounterCache($counter);

        return response()->json([
            'success' => true,
            'queue' => $queue
        ]);
    }

    public function recallQueue(Request $request)
    {
        $validated = $request->validate([
            'queue_id' => 'required|exists:queues,id',
        ]);

        $queue = \App\Models\Queue::findOrFail($validated['queue_id']);
        $counter = auth()->user();

        // Verify this queue belongs to this counter
        if ($queue->counter_id !== $counter->id) {
            return response()->json([
                'success' => false,
                'message' => 'This queue does not belong to your counter'
            ], 422);
        }

        // Recall is intended for skipped queues only.
        if ($queue->status !== 'skipped') {
            return response()->json([
                'success' => false,
                'message' => 'Only skipped queues can be recalled'
            ], 422);
        }

        // Prevent having multiple active queues at once.
        $currentQueue = $counter->getCurrentQueue();
        if ($currentQueue && $currentQueue->id !== $queue->id) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete the current queue before recalling another'
            ], 422);
        }

        $queue->update([
            'status' => 'serving',
            'skipped_at' => null,
            // Ensure this recalled queue becomes the active/visible one.
            'called_at' => now(),
            'notified_at' => now(),
        ]);
        
        // Invalidate cache
        $this->invalidateCounterCache($counter);

        // Mark this recall so the monitor can play bell sound once
        Cache::put('recall_queue_' . $queue->id, now()->timestamp, 10);

        return response()->json([
            'success' => true,
            'queue' => $queue
        ]);
    }

    /**
     * Get analytics data for dashboard reports
     */
    private function getAnalyticsData($counter)
    {
        $today = Carbon::now()->startOfDay();
        $thisWeekStart = Carbon::now()->startOfWeek();
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();

        // Hourly completions for today
        $hourlyCompletions = DB::table('queues')
            ->where('counter_id', $counter->id)
            ->where('status', 'completed')
            ->where('completed_at', '>=', $today)
            ->selectRaw('HOUR(completed_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $hourlyData = [];
        for ($h = 0; $h < 24; $h++) {
            $hourlyData[] = $hourlyCompletions[$h] ?? 0;
        }

        // Weekly trend (last 7 days)
        $weeklyCompletions = DB::table('queues')
            ->where('counter_id', $counter->id)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$thisWeekStart->subDays(6), Carbon::now()->endOfDay()])
            ->selectRaw('DATE(completed_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $weeklyDays = [];
        $weeklyData = [];
        $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dayName = $dayNames[Carbon::parse($date)->dayOfWeek - 1] ?? 'Monday';
            $weeklyDays[] = $dayName;
            $weeklyData[] = $weeklyCompletions[$date] ?? 0;
        }

        // Average wait time by day of week
        $waitTimes = DB::table('queues')
            ->where('counter_id', $counter->id)
            ->where('status', 'completed')
            ->whereNotNull('called_at')
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$thisWeekStart->subDays(6), Carbon::now()->endOfDay()])
            ->selectRaw('DAYNAME(completed_at) as day, 
                        AVG(TIMESTAMPDIFF(SECOND, called_at, completed_at) / 60) as avg_wait_minutes')
            ->groupBy('day')
            ->get();

        $waitTimeData = [];
        foreach ($dayNames as $day) {
            $waitTime = $waitTimes->firstWhere('day', $day);
            $waitTimeData[] = $waitTime ? round($waitTime->avg_wait_minutes, 1) : 0;
        }

        // Peak hours distribution (where most work is done)
        $peakHours = DB::table('queues')
            ->where('counter_id', $counter->id)
            ->where('status', 'completed')
            ->where('completed_at', '>=', $today)
            ->selectRaw("
                CASE 
                    WHEN HOUR(completed_at) BETWEEN 9 AND 16 THEN 'Peak Hours (9am-5pm)'
                    WHEN HOUR(completed_at) BETWEEN 6 AND 8 THEN 'Morning (6am-9am)'
                    WHEN HOUR(completed_at) BETWEEN 17 AND 20 THEN 'Evening (5pm-9pm)'
                    ELSE 'Night (9pm-6am)'
                END as period,
                COUNT(*) as count
            ")
            ->groupBy('period')
            ->pluck('count', 'period')
            ->toArray();

        $totalToday = array_sum($peakHours);
        $peakHoursData = [
            round(($peakHours['Peak Hours (9am-5pm)'] ?? 0) / max($totalToday, 1) * 100, 1),
            round(($peakHours['Morning (6am-9am)'] ?? 0) / max($totalToday, 1) * 100, 1),
            round(($peakHours['Evening (5pm-9pm)'] ?? 0) / max($totalToday, 1) * 100, 1),
            round(($peakHours['Night (9pm-6am)'] ?? 0) / max($totalToday, 1) * 100, 1),
        ];

        return [
            'hourly' => $hourlyData,
            'weekly_days' => $weeklyDays,
            'weekly' => $weeklyData,
            'wait_time' => $waitTimeData,
            'peak_hours' => $peakHoursData,
            'total_completed_today' => $totalToday,
        ];
    }

    public function autoLogout(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->isCounter()) {
            $user->is_online = false;
            $user->save();
            event(new CounterStatusUpdated($user->organization->organization_code, $user->id, 'offline'));
            auth()->logout();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 403);
    }
}
