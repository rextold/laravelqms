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
     * POST /counter/call-next
     */
    public function callNext(Request $request)
    {
        try {
            $user = Auth::user();
            $organization = $request->attributes->get('organization');
            
            // Validate user and counter status
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            if (!$user->isCounter()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Counter role required'
                ], 403);
            }
            
            if (!$user->is_online) {
                return response()->json([
                    'success' => false,
                    'message' => 'Counter must be online to call queues'
                ], 403);
            }

            // Check if counter already has an active queue
            $currentQueue = $user->getCurrentQueue();
            if ($currentQueue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please complete or skip current queue before calling next',
                    'current_queue' => $currentQueue
                ], 400);
            }

            $queue = DB::transaction(function () use ($user) {
                // Get next waiting queue for this counter (FIFO - First In First Out)
                $queue = Queue::where('counter_id', $user->id)
                    ->where('status', 'waiting')
                    ->orderBy('created_at', 'asc')
                    ->lockForUpdate()
                    ->first();

                if (!$queue) {
                    return null;
                }

                // Update queue status to called
                $queue->status = 'called';
                $queue->called_at = now();
                $queue->save();

                Log::info('Queue called', [
                    'queue_id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'counter_id' => $user->id,
                    'counter_number' => $user->counter_number,
                    'called_at' => $queue->called_at
                ]);

                return $queue;
            });

            if (!$queue) {
                return response()->json([
                    'success' => false,
                    'message' => 'No queues waiting'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Queue ' . $queue->queue_number . ' has been called',
                'queue' => [
                    'id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'status' => $queue->status,
                    'called_at' => $queue->called_at->toISOString(),
                    'counter_id' => $queue->counter_id
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error calling next queue: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to call next queue: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Complete current queue (does NOT auto-call next queue)
     * Counter must manually click "Call Next" to serve next customer
     * POST /counter/move-next
     */
    public function moveToNext(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Validate user and counter status
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            if (!$user->isCounter()) {
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

            $completedQueue = DB::transaction(function () use ($user) {
                // Get current queue
                $currentQueue = $user->getCurrentQueue();
                
                if (!$currentQueue) {
                    return null;
                }
                
                // Mark current queue as completed
                $currentQueue->status = 'completed';
                $currentQueue->completed_at = now();
                $currentQueue->save();
                
                Log::info('Queue completed', [
                    'queue_id' => $currentQueue->id,
                    'queue_number' => $currentQueue->queue_number,
                    'counter_id' => $user->id,
                    'counter_number' => $user->counter_number,
                    'completed_at' => $currentQueue->completed_at
                ]);

                // DO NOT auto-call next queue
                // Counter must explicitly click "Call Next" to serve next customer

                return $currentQueue;
            });

            if (!$completedQueue) {
                return response()->json([
                    'success' => false,
                    'message' => 'No current queue to complete'
                ], 404);
            }

            // Count remaining waiting queues for informational message
            $waitingCount = Queue::where('counter_id', $user->id)
                ->where('status', 'waiting')
                ->count();

            $message = 'Queue ' . $completedQueue->queue_number . ' completed.';
            if ($waitingCount > 0) {
                $message .= ' ' . $waitingCount . ' queue(s) waiting. Click "Call Next" to serve.';
            } else {
                $message .= ' No more queues waiting.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'completed_queue' => [
                    'id' => $completedQueue->id,
                    'queue_number' => $completedQueue->queue_number,
                    'completed_at' => $completedQueue->completed_at->toISOString()
                ],
                'waiting_count' => $waitingCount,
                'queue' => null // No auto-called queue
            ]);

        } catch (\Exception $e) {
            Log::error('Error completing queue: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete queue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transfer queue to another counter
     * POST /counter/transfer
     */
    public function transferQueue(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Validate user and counter status
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            if (!$user->isCounter()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Counter role required'
                ], 403);
            }
            
            if (!$user->is_online) {
                return response()->json([
                    'success' => false,
                    'message' => 'Counter must be online to transfer queues'
                ], 403);
            }

            // Get parameters from POST body or query string
            $queueId = $request->input('queue_id') ?? $request->query('queue_id');
            $targetCounterId = $request->input('to_counter_id') ?? $request->query('to_counter_id');

            // Validate required parameters
            if (!$queueId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Queue ID is required'
                ], 422);
            }
            
            if (!$targetCounterId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Target counter ID is required'
                ], 422);
            }

            // Validate queue exists
            $queue = Queue::find($queueId);
            if (!$queue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Queue not found'
                ], 404);
            }

            // Verify queue belongs to current counter
            if ($queue->counter_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This queue does not belong to your counter'
                ], 403);
            }

            // Validate target counter exists and is online
            $targetCounter = User::find($targetCounterId);
            if (!$targetCounter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Target counter not found'
                ], 404);
            }
            
            if (!$targetCounter->isCounter()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Target is not a valid counter'
                ], 400);
            }
            
            if (!$targetCounter->is_online) {
                return response()->json([
                    'success' => false,
                    'message' => 'Target counter is offline'
                ], 400);
            }
            
            if ($targetCounter->id == $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot transfer to the same counter'
                ], 400);
            }

            $result = DB::transaction(function () use ($queue, $targetCounter, $user) {
                // Store original counter for logging
                $originalCounterId = $queue->counter_id;
                
                // Transfer queue
                $queue->counter_id = $targetCounter->id;
                $queue->transferred_from = $user->id;
                $queue->transferred_at = now();
                $queue->status = 'waiting'; // Reset to waiting for new counter
                $queue->called_at = null; // Clear called_at since it's now waiting
                $queue->save();

                Log::info('Queue transferred', [
                    'queue_id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'from_counter_id' => $originalCounterId,
                    'to_counter_id' => $targetCounter->id,
                    'transferred_at' => $queue->transferred_at
                ]);

                return $queue;
            });

            return response()->json([
                'success' => true,
                'message' => 'Queue ' . $result->queue_number . ' transferred to Counter ' . $targetCounter->counter_number,
                'queue' => [
                    'id' => $result->id,
                    'queue_number' => $result->queue_number,
                    'status' => $result->status,
                    'new_counter_id' => $result->counter_id,
                    'new_counter_number' => $targetCounter->counter_number,
                    'transferred_at' => $result->transferred_at->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error transferring queue: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null,
                'queue_id' => $queueId ?? null,
                'target_counter_id' => $targetCounterId ?? null,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to transfer queue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Skip current queue
     * POST /counter/skip
     */
    public function skipQueue(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Validate user and counter status
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            if (!$user->isCounter()) {
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

            $queue = DB::transaction(function () use ($user) {
                $currentQueue = $user->getCurrentQueue();
                
                if (!$currentQueue) {
                    return null;
                }

                $currentQueue->status = 'skipped';
                $currentQueue->skipped_at = now();
                $currentQueue->save();

                Log::info('Queue skipped', [
                    'queue_id' => $currentQueue->id,
                    'queue_number' => $currentQueue->queue_number,
                    'counter_id' => $user->id,
                    'skipped_at' => $currentQueue->skipped_at
                ]);

                return $currentQueue;
            });

            if (!$queue) {
                return response()->json([
                    'success' => false,
                    'message' => 'No current queue to skip'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Queue ' . $queue->queue_number . ' has been skipped',
                'queue' => [
                    'id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'status' => $queue->status,
                    'skipped_at' => $queue->skipped_at->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error skipping queue: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to skip queue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recall a skipped queue
     * POST /counter/recall
     */
    public function recallQueue(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Validate user and counter status
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            if (!$user->isCounter()) {
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

            // Get queue_id from POST body or query string
            $queueId = $request->input('queue_id') ?? $request->query('queue_id');

            if (!$queueId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Queue ID is required'
                ], 422);
            }

            // Check if counter already has an active queue
            $currentQueue = $user->getCurrentQueue();
            if ($currentQueue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please complete or skip current queue before recalling another',
                    'current_queue' => [
                        'id' => $currentQueue->id,
                        'queue_number' => $currentQueue->queue_number
                    ]
                ], 400);
            }

            // Find the queue
            $queue = Queue::find($queueId);
            
            if (!$queue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Queue not found'
                ], 404);
            }

            // Verify queue belongs to current counter
            if ($queue->counter_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This queue belongs to another counter'
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
            $result = DB::transaction(function () use ($queue, $user) {
                $queue->status = 'called';
                $queue->called_at = now();
                $queue->skipped_at = null;
                $queue->save();

                Log::info('Queue recalled', [
                    'queue_id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'counter_id' => $user->id,
                    'recalled_at' => $queue->called_at
                ]);

                return $queue;
            });

            return response()->json([
                'success' => true,
                'message' => 'Queue ' . $result->queue_number . ' has been recalled',
                'queue' => [
                    'id' => $result->id,
                    'queue_number' => $result->queue_number,
                    'status' => $result->status,
                    'called_at' => $result->called_at->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error recalling queue: ' . $e->getMessage(), [
                'queue_id' => $queueId ?? null,
                'user_id' => $user->id ?? null,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to recall queue: ' . $e->getMessage()
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
     * Send customer notification - updates notified_at in database
     */
    public function notifyCustomer(Request $request)
    {
        $user = Auth::user();
        $organization = $request->attributes->get('organization');

        // Verify counter role and online status
        if (!$user || !$user->isCounter()) {
            return response()->json([
                'success' => false,
                'message' => 'Counter role required'
            ], 403);
        }

        try {
            // Get current queue for this counter
            $currentQueue = $user->getCurrentQueue();
            if (!$currentQueue) {
                return response()->json([
                    'success' => false,
                    'message' => 'No current queue to notify'
                ], 400);
            }

            // Update the notified_at timestamp in the database
            $currentQueue->notified_at = now();
            $currentQueue->save();

            Log::info('Customer notification sent', [
                'counter_id' => $user->id,
                'counter_number' => $user->counter_number,
                'queue_id' => $currentQueue->id,
                'queue_number' => $currentQueue->queue_number,
                'notified_at' => $currentQueue->notified_at,
                'organization_id' => $organization->id ?? null
            ]);

            // Broadcast notification event if broadcasting is enabled
            if (config('broadcasting.default') !== 'null') {
                try {
                    broadcast(new \App\Events\CustomerNotified($currentQueue, $user));
                } catch (\Exception $e) {
                    Log::warning('Failed to broadcast notification: ' . $e->getMessage());
                    // Continue even if broadcast fails - the database update is more important
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Customer notified successfully',
                'queue_number' => $currentQueue->queue_number,
                'queue_id' => $currentQueue->id,
                'counter_id' => $user->id,
                'counter_number' => $user->counter_number,
                'notified_at' => $currentQueue->notified_at->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Notification error: ' . $e->getMessage(), [
                'counter_id' => $user->id ?? null,
                'organization_id' => $organization->id ?? null,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ], 500);
        }
    }
}