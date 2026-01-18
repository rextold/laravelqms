<?php

namespace App\Http\Controllers;

use App\Http\Requests\Counter\TransferQueueRequest;
use App\Http\Requests\Counter\RecallQueueRequest;
use App\Models\Queue;
use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Services\CounterService;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CounterController extends Controller
{
    protected $queueService;
    protected $counterService;

    public function __construct(QueueService $queueService, CounterService $counterService)
    {
        $this->queueService = $queueService;
        $this->counterService = $counterService;
    }

    /**
     * Get analytics data for counter performance
     */

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
     * Toggle counter online/offline status (GET method for simple toggle)
     */
    public function toggleOnline(Request $request)
    {
        $user = Auth::user();
        $organization = $request->attributes->get('organization');
        
        if (!$user || !$user->isCounter()) {
            return response()->json([
                'success' => false,
                'error' => 'Access denied - Counter role required'
            ], 403);
        }

        try {
            $newOnlineStatus = DB::transaction(function () use ($user) {
                $user->is_online = !$user->is_online;
                $user->save();

                // Broadcast status update if broadcasting is enabled
                try {
                    if (config('broadcasting.default') !== 'null') {
                        broadcast(new \App\Events\CounterStatusUpdated($user));
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to broadcast counter status: ' . $e->getMessage());
                    // Continue even if broadcast fails
                }

                return $user->is_online;
            });

            $message = $newOnlineStatus ? 
                'You are now online and ready to serve customers.' : 
                'You are now offline.';
            
            // Always return JSON for GET requests (makes it easier for AJAX/Fetch calls)
            if ($request->expectsJson() || $request->is('*/counter/toggle-online')) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'is_online' => $newOnlineStatus
                ], 200);
            }

            // Fallback to redirect if not expecting JSON
            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error toggling counter status: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null,
                'organization_id' => $organization->id ?? null
            ]);
            
            if ($request->expectsJson() || $request->is('*/counter/toggle-online')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to update status',
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update status');
        }
    }    /**
     * Call next queue in line
     */
    public function callNext(Request $request = null)
    {
        $user = Auth::user();
        $organization = $request ? $request->attributes->get('organization') : null;
        
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
    public function moveToNext(Request $request = null)
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

        // Get parameters from GET request
        $queueId = $request->query('queue_id') ?? $request->input('queue_id');
        $targetCounterId = $request->query('to_counter_id') ?? $request->input('to_counter_id');

        if (!$queueId || !$targetCounterId) {
            return response()->json([
                'error' => 'Missing required parameters: queue_id and to_counter_id'
            ], 422);
        }

        // Validate queue exists
        $queue = Queue::find($queueId);
        if (!$queue) {
            return response()->json(['error' => 'Queue not found'], 404);
        }

        // Validate target counter exists
        $targetCounter = User::find($targetCounterId);
        if (!$targetCounter) {
            return response()->json(['error' => 'Target counter not found'], 404);
        }

        try {
            $result = DB::transaction(function () use ($queue, $targetCounter, $user) {

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
     * Supports both authenticated counter users and public requests with counter_id
     */
    public function getData(Request $request)
    {
        try {
            $organization = $request->attributes->get('organization');
            
            // Fallback: if no organization in context, get the first/default organization
            if (!$organization) {
                $organization = Organization::first();
                if (!$organization) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No organization found'
                    ], 404);
                }
            }
            
            $user = Auth::user();
            // Support both query parameter and route parameter for counter_id
            $counterId = $request->query('counter_id') ?? $request->route('counter_id');
            
            // If authenticated counter user, get their specific data
            if ($user && $user->isCounter()) {
                $currentQueue = $user->getCurrentQueue();
                $waitingQueues = $user->getWaitingQueues();
                $skippedQueues = $user->getSkippedQueues();
                $onlineCounters = $user->getOnlineCounters();                $stats = $this->counterService->getCounterStats($user->id, $organization->id);
                 $analytics = $this->getAnalyticsData($user);

                return response()->json([
                    'success' => true,
                    'online_status' => $user->is_online,
                    'current_queue' => $currentQueue,
                    'waiting_queues' => $waitingQueues,
                    'skipped' => $skippedQueues,
                    'online_counters' => $onlineCounters,
                    'served_today' => $stats['completed_today'],
                    'stats' => $stats,
                    'analytics' => $analytics
                ]);
            }

            // If counter_id is provided, get public data for that counter
            if ($counterId) {
                $counter = User::find($counterId);
                if (!$counter || $counter->organization_id !== $organization->id) {
                    return response()->json(['success' => false, 'message' => 'Counter not found'], 404);
                }
                
                $currentQueue = $counter->getCurrentQueue();
                $waitingQueues = $counter->getWaitingQueues();
                $stats = $this->counterService->getCounterStats($counter->id, $organization->id);

                return response()->json([
                    'success' => true,
                    'online_status' => $counter->is_online,
                    'current_queue' => $currentQueue,
                    'waiting_queues' => $waitingQueues,
                    'stats' => $stats
                ]);
            }

            // For other unauthenticated requests, return basic organization data
            $onlineCounters = User::where('organization_id', $organization->id)
                ->where('role', 'counter')
                ->where('is_online', true)
                ->orderBy('counter_number')
                ->get(['id', 'counter_number', 'display_name']);

            return response()->json([
                'success' => true,
                'online_status' => false,
                'current_queue' => null,
                'waiting_queues' => [],
                'skipped' => [],
                'online_counters' => $onlineCounters,
                'served_today' => 0,
                'stats' => [
                    'waiting' => 0,
                    'completed_today' => 0
                ],
                'analytics' => [
                    'hourly' => array_fill(0, 24, 0),
                    'weekly' => array_fill(0, 7, 0),
                    'weekly_days' => [],
                    'wait_time' => array_fill(0, 7, 0),
                    'peak_hours' => [0, 0, 0, 0]
                ]
            ]);

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
        // Average wait time (from created_at to called_at)
        $avgWaitTime = Queue::where('counter_id', $counter->id)
            ->where('status', 'completed')
            ->whereDate('completed_at', $today)
            ->whereNotNull('called_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, called_at)) as avg_wait')
            ->value('avg_wait');
        // Average service time (from called_at to completed_at)
        $avgServiceTime = Queue::where('counter_id', $counter->id)
            ->where('status', 'completed')
            ->whereDate('completed_at', $today)
            ->whereNotNull('called_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, called_at, completed_at)) as avg_service')
            ->value('avg_service');
        // Compute current number for dashboard display
        $currentNumber = null;
        $currentQueue = $counter->getCurrentQueue();
        if ($currentQueue && $currentQueue->queue_number) {
            $queueParts = explode('-', $currentQueue->queue_number);
            $currentNumber = $queueParts[array_key_last($queueParts)];
        }
        return [
            'waiting' => Queue::where('counter_id', $counter->id)
                ->where('status', 'waiting')
                ->count(),
            'completed_today' => Queue::where('counter_id', $counter->id)
                ->where('status', 'completed')
                ->whereDate('completed_at', $today)
                ->count(),
            'current_queue' => $currentQueue,
            'current_number' => $currentNumber,
            'total_served' => Queue::where('counter_id', $counter->id)
                ->where('status', 'completed')
                ->count(),
            'skipped_today' => Queue::where('counter_id', $counter->id)
                ->where('status', 'skipped')
                ->whereDate('skipped_at', $today)
                ->count(),
            'avg_wait_time' => round($avgWaitTime ?? 0, 1),
            'avg_service_time' => round($avgServiceTime ?? 0, 1)
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
    /**
     * Send customer notification
     */
    public function notifyCustomer(Request $request)
    {
        $user = Auth::user();
        $organization = $request->attributes->get('organization');

        try {
            // Get current queue for this counter
            $currentQueue = $user->getCurrentQueue();
            if (!$currentQueue) {
                return response()->json([
                    'success' => false,
                    'message' => 'No current queue to notify'
                ], 400);
            }

            // Send notification through configured channel
            $result = $this->counterService->sendCustomerNotification(
                $user,
                $currentQueue->queue_number,
                $request->notification_method,
                $request->message
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully',
                'queue_number' => $currentQueue->queue_number
            ]);

        } catch (\Exception $e) {
            Log::error('Notification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification'
            ], 500);
        }
    }
}