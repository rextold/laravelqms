<?php

namespace App\Http\Controllers;

use App\Events\CounterStatusUpdated;
use App\Events\QueueCalled;
use App\Models\Queue;
use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Services\QueueService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CounterController extends Controller
{
    protected $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * Show counter dashboard with statistics and reports
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $organization = $request->attributes->get('organization');
        
        if (!$user->isCounter()) {
            abort(403, 'Access denied. Counter role required.');
        }

        // Get counter statistics
        $stats = $this->getCounterStats($user);
        
        // Get analytics data for charts
        $analyticsData = $this->getAnalyticsData($user);
        
        // Get organization settings
        $settings = OrganizationSetting::getSettings($organization->id);

        return view('counter.dashboard', [
            'counter' => $user,
            'organization' => $organization,
            'settings' => $settings,
            'stats' => $stats,
            'analyticsData' => $analyticsData
        ]);
    }

    /**
     * Show counter call/service panel
     */
    public function callView(Request $request)
    {
        $user = Auth::user();
        $organization = $request->attributes->get('organization');
        
        if (!$user->isCounter()) {
            abort(403, 'Access denied. Counter role required.');
        }

        // Get current queue being served
        $currentQueue = $user->getCurrentQueue();
        
        // Get waiting queues for this counter
        $waitingQueues = $user->getWaitingQueues();
        
        // Get skipped queues that can be recalled
        $skippedQueues = $user->getSkippedQueues();
        
        // Get other online counters for transfer
        $onlineCounters = User::where('organization_id', $organization->id)
            ->where('role', 'counter')
            ->where('is_online', true)
            ->where('id', '!=', $user->id)
            ->orderBy('counter_number')
            ->get();

        // Get organization settings
        $settings = OrganizationSetting::getSettings($organization->id);

        return view('counter.call', [
            'counter' => $user,
            'organization' => $organization,
            'settings' => $settings,
            'currentQueue' => $currentQueue,
            'waitingQueues' => $waitingQueues,
            'skippedQueues' => $skippedQueues,
            'onlineCounters' => $onlineCounters
        ]);
    }

    /**
     * Toggle counter online/offline status
     */
    public function toggleOnline(Request $request)
    {
        $user = Auth::user();
        $organization = $request->attributes->get('organization');
        
        if (!$user->isCounter()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            DB::transaction(function () use ($user) {
                $user->is_online = !$user->is_online;
                $user->save();

                // Broadcast status update
                if (config('broadcasting.default') !== 'null') {
                    broadcast(new CounterStatusUpdated($user));
                }
            });

            $message = $user->is_online ? 'You are now online and ready to serve customers.' : 'You are now offline.';
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'is_online' => $user->is_online
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error toggling counter status: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to update status'], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update status');
        }
    }

    /**
     * Call next queue in line
     */
    public function callNext(Request $request)
    {
        $user = Auth::user();
        $organization = $request->attributes->get('organization');
        
        if (!$user->isCounter() || !$user->is_online) {
            return response()->json(['error' => 'Counter must be online to call queues'], 403);
        }

        try {
            $queue = DB::transaction(function () use ($user) {
                // Get next waiting queue for this counter
                $queue = Queue::where('counter_id', $user->id)
                    ->where('status', 'waiting')
                    ->orderBy('created_at')
                    ->first();

                if (!$queue) {
                    return null;
                }

                // Update queue status
                $queue->status = 'called';
                $queue->called_at = now();
                $queue->save();

                // Broadcast queue called event
                if (config('broadcasting.default') !== 'null') {
                    broadcast(new QueueCalled($queue));
                }

                return $queue;
            });

            if (!$queue) {
                return response()->json(['error' => 'No queues waiting'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Queue ' . $queue->queue_number . ' has been called',
                'queue' => $queue
            ]);

        } catch (\Exception $e) {
            Log::error('Error calling next queue: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to call next queue'], 500);
        }
    }

    /**
     * Move current queue to completed and call next
     */
    public function moveToNext(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isCounter() || !$user->is_online) {
            return response()->json(['error' => 'Counter must be online'], 403);
        }

        try {
            $result = DB::transaction(function () use ($user) {
                // Complete current queue
                $currentQueue = $user->getCurrentQueue();
                if ($currentQueue) {
                    $currentQueue->status = 'completed';
                    $currentQueue->completed_at = now();
                    $currentQueue->save();
                }

                // Call next queue
                $nextQueue = Queue::where('counter_id', $user->id)
                    ->where('status', 'waiting')
                    ->orderBy('created_at')
                    ->first();

                if ($nextQueue) {
                    $nextQueue->status = 'called';
                    $nextQueue->called_at = now();
                    $nextQueue->save();

                    // Broadcast queue called event
                    if (config('broadcasting.default') !== 'null') {
                        broadcast(new QueueCalled($nextQueue));
                    }
                }

                return $nextQueue;
            });

            return response()->json([
                'success' => true,
                'message' => $result ? 'Next queue called: ' . $result->queue_number : 'No more queues waiting',
                'queue' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Error moving to next queue: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to move to next queue'], 500);
        }
    }

    /**
     * Transfer queue to another counter
     */
    public function transferQueue(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isCounter() || !$user->is_online) {
            return response()->json(['error' => 'Counter must be online'], 403);
        }

        $request->validate([
            'queue_id' => 'required|exists:queues,id',
            'target_counter_id' => 'required|exists:users,id'
        ]);

        try {
            $result = DB::transaction(function () use ($request, $user) {
                $queue = Queue::findOrFail($request->queue_id);
                $targetCounter = User::findOrFail($request->target_counter_id);

                // Verify queue belongs to current counter
                if ($queue->counter_id !== $user->id) {
                    throw new \Exception('Queue does not belong to this counter');
                }

                // Verify target counter is online
                if (!$targetCounter->is_online) {
                    throw new \Exception('Target counter is offline');
                }

                // Transfer queue
                $queue->counter_id = $targetCounter->id;
                $queue->transferred_from = $user->id;
                $queue->transferred_at = now();
                $queue->status = 'waiting'; // Reset to waiting for new counter
                $queue->save();

                return $queue;
            });

            return response()->json([
                'success' => true,
                'message' => 'Queue transferred successfully',
                'queue' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Error transferring queue: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Skip current queue
     */
    public function skipQueue(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isCounter() || !$user->is_online) {
            return response()->json(['error' => 'Counter must be online'], 403);
        }

        try {
            $queue = DB::transaction(function () use ($user) {
                $currentQueue = $user->getCurrentQueue();
                
                if (!$currentQueue) {
                    throw new \Exception('No queue to skip');
                }

                $currentQueue->status = 'skipped';
                $currentQueue->skipped_at = now();
                $currentQueue->save();

                return $currentQueue;
            });

            return response()->json([
                'success' => true,
                'message' => 'Queue ' . $queue->queue_number . ' has been skipped',
                'queue' => $queue
            ]);

        } catch (\Exception $e) {
            Log::error('Error skipping queue: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Recall a skipped queue
     * Accepts both GET and POST requests
     */
    public function recallQueue(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !$user->isCounter()) {
            return response()->json([
                'success' => false,
                'message' => 'Counter role required'
            ], 403);
        }

        if (!$user->is_online) {
            return response()->json([
                'success' => false,
                'message' => 'Counter must be online'
            ], 403);
        }

        // Get queue_id from GET, POST, or request body
        $queueId = $request->input('queue_id') ?? $request->query('queue_id');

        if (!$queueId) {
            return response()->json([
                'success' => false,
                'message' => 'The queue id field is required.'
            ], 422);
        }

        try {
            // Verify queue exists
            $queue = Queue::find($queueId);
            
            if (!$queue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Queue not found'
                ], 404);
            }

            // Log verification details for debugging
            Log::info('Recall queue verification', [
                'user_id' => $user->id,
                'user_counter_id' => $user->id,
                'queue_id' => $queue->id,
                'queue_counter_id' => $queue->counter_id,
                'queue_status' => $queue->status,
                'user_matches' => $queue->counter_id == $user->id
            ]);

            // Verify queue belongs to current counter
            if ($queue->counter_id != $user->id) {
                Log::warning('Queue ownership mismatch', [
                    'queue_counter_id' => $queue->counter_id,
                    'user_id' => $user->id,
                    'user_counter_id' => $user->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'This queue belongs to Counter ' . $queue->counter_id . '. Only the assigned counter can perform this action.'
                ], 403);
            }

            // Verify queue is in skipped status
            if ($queue->status !== 'skipped') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only skipped queues can be recalled. Current status: ' . $queue->status
                ], 422);
            }

            // Recall the queue
            $result = DB::transaction(function () use ($queue) {
                $queue->status = 'called';
                $queue->called_at = now();
                $queue->skipped_at = null;
                $queue->save();

                // Broadcast queue called event
                if (config('broadcasting.default') !== 'null') {
                    broadcast(new QueueCalled($queue));
                }

                return $queue;
            });

            return response()->json([
                'success' => true,
                'message' => 'Queue ' . $result->queue_number . ' has been recalled',
                'queue' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Error recalling queue: ' . $e->getMessage(), [
                'queue_id' => $queueId,
                'user_id' => $user->id,
                'exception' => get_class($e)
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Notify customer (placeholder for future implementation)
     */
    public function notifyCustomer(Request $request)
    {
        // Placeholder for SMS/notification functionality
        return response()->json([
            'success' => true,
            'message' => 'Customer notification sent'
        ]);
    }

    /**
     * Auto logout when counter goes offline
     */
    public function autoLogout(Request $request)
    {
        $user = Auth::user();
        
        if ($user->isCounter()) {
            $user->is_online = false;
            $user->save();
        }

        Auth::logout();
        
        return response()->json(['success' => true]);
    }

    /**
     * Get counter data for real-time updates (both panel and dashboard)
     */
    public function getData(Request $request)
    {
        try {
            $organization = $request->attributes->get('organization');
            $user = Auth::user();
            
            // If authenticated counter user, get their specific data
            if ($user && $user->isCounter()) {
                $currentQueue = $user->getCurrentQueue();
                $waitingQueues = $user->getWaitingQueues();
                $skippedQueues = $user->getSkippedQueues();
                $onlineCounters = User::where('organization_id', $organization->id)
                    ->where('role', 'counter')
                    ->where('is_online', true)
                    ->where('id', '!=', $user->id)
                    ->orderBy('counter_number')
                    ->get(['id', 'counter_number', 'display_name']);

                // Get stats and analytics safely
                $stats = $this->getCounterStats($user);
                $analytics = $this->getAnalyticsData($user);

                return response()->json([
                    'success' => true,
                    'online_status' => $user->is_online,
                    'current_queue' => $currentQueue ? [
                        'id' => $currentQueue->id,
                        'queue_number' => $currentQueue->queue_number,
                        'counter_id' => $currentQueue->counter_id,
                        'status' => $currentQueue->status,
                    ] : null,
                    'waiting_queues' => $waitingQueues->map(fn($q) => [
                        'id' => $q->id,
                        'queue_number' => $q->queue_number,
                    ])->values(),
                    'skipped' => $skippedQueues->map(fn($q) => [
                        'id' => $q->id,
                        'queue_number' => $q->queue_number,
                    ])->values(),
                    'online_counters' => $onlineCounters,
                    // Dashboard stats and analytics
                    'stats' => $stats,
                    'analytics' => $analytics
                ]);
            }

            // For unauthenticated requests (kiosk, monitor), return limited data
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);

        } catch (\Exception $e) {
            Log::error('Error fetching counter data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch counter data'
            ], 500);
        }
    }

    /**
     * Get counter statistics
     */
    private function getCounterStats(User $counter)
    {
        $today = Carbon::today();
        
        return [
            'waiting' => Queue::where('counter_id', $counter->id)
                ->where('status', 'waiting')
                ->count(),
            'completed_today' => Queue::where('counter_id', $counter->id)
                ->where('status', 'completed')
                ->whereDate('completed_at', $today)
                ->count(),
            'current_queue' => $counter->getCurrentQueue(),
            'total_served' => Queue::where('counter_id', $counter->id)
                ->where('status', 'completed')
                ->count(),
            'skipped_today' => Queue::where('counter_id', $counter->id)
                ->where('status', 'skipped')
                ->whereDate('skipped_at', $today)
                ->count()
        ];
    }

    /**
     * Get analytics data for dashboard charts
     */
    private function getAnalyticsData(User $counter)
    {
        try {
            $today = Carbon::today();
            
            // Hourly completions for today
            $hourlyData = [];
            for ($hour = 0; $hour < 24; $hour++) {
                $count = Queue::where('counter_id', $counter->id)
                    ->where('status', 'completed')
                    ->whereDate('completed_at', $today)
                    ->whereRaw('HOUR(completed_at) = ?', [$hour])
                    ->count();
                $hourlyData[] = $count;
            }
            
            // Weekly completions (last 7 days)
            $weeklyData = [];
            $weeklyDays = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                $count = Queue::where('counter_id', $counter->id)
                    ->where('status', 'completed')
                    ->whereDate('completed_at', $date)
                    ->count();
                $weeklyData[] = $count;
                $weeklyDays[] = $date->format('M j');
            }
            
            // Average wait time by day (last 7 days)
            $waitTimeData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                $avgWaitTime = Queue::where('counter_id', $counter->id)
                    ->where('status', 'completed')
                    ->whereDate('completed_at', $date)
                    ->whereNotNull('called_at')
                    ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, called_at)) as avg_wait')
                    ->value('avg_wait');
                $waitTimeData[] = round($avgWaitTime ?? 0, 1);
            }
            
            // Peak hours distribution (last 30 days)
            $thirtyDaysAgo = $today->copy()->subDays(30);
            $peakHours = [
                // Peak Hours (9am-5pm)
                Queue::where('counter_id', $counter->id)
                    ->where('status', 'completed')
                    ->where('completed_at', '>=', $thirtyDaysAgo)
                    ->whereRaw('TIME(completed_at) >= ?', ['09:00:00'])
                    ->whereRaw('TIME(completed_at) < ?', ['17:00:00'])
                    ->count(),
                // Morning (6am-9am)
                Queue::where('counter_id', $counter->id)
                    ->where('status', 'completed')
                    ->where('completed_at', '>=', $thirtyDaysAgo)
                    ->whereRaw('TIME(completed_at) >= ?', ['06:00:00'])
                    ->whereRaw('TIME(completed_at) < ?', ['09:00:00'])
                    ->count(),
                // Evening (5pm-9pm)
                Queue::where('counter_id', $counter->id)
                    ->where('status', 'completed')
                    ->where('completed_at', '>=', $thirtyDaysAgo)
                    ->whereRaw('TIME(completed_at) >= ?', ['17:00:00'])
                    ->whereRaw('TIME(completed_at) < ?', ['21:00:00'])
                    ->count(),
                // Night (9pm-6am)
                Queue::where('counter_id', $counter->id)
                    ->where('status', 'completed')
                    ->where('completed_at', '>=', $thirtyDaysAgo)
                    ->whereRaw('(TIME(completed_at) >= ? OR TIME(completed_at) < ?)', ['21:00:00', '06:00:00'])
                    ->count()
            ];
            
            return [
                'hourly' => $hourlyData,
                'weekly' => $weeklyData,
                'weekly_days' => $weeklyDays,
                'wait_time' => $waitTimeData,
                'peak_hours' => $peakHours
            ];
        } catch (\Exception $e) {
            Log::error('Error generating analytics data: ' . $e->getMessage());
            
            // Return empty data structure to prevent crashes
            return [
                'hourly' => array_fill(0, 24, 0),
                'weekly' => array_fill(0, 7, 0),
                'weekly_days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'wait_time' => array_fill(0, 7, 0),
                'peak_hours' => [0, 0, 0, 0]
            ];
        }
    }
}
