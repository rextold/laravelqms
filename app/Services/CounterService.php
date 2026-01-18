<?php

namespace App\Services;

use App\Events\CounterStatusUpdated;
use App\Events\QueueCalled;
use App\Models\Queue;
use App\Models\User;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CounterService
{
    /**
     * Toggle counter online/offline status
     */
    public function toggleOnlineStatus(User $counter): array
    {
        try {
            DB::transaction(function () use ($counter) {
                $counter->is_online = !$counter->is_online;
                $counter->save();

                // Broadcast status update if broadcasting is enabled
                if ($this->shouldBroadcast()) {
                    broadcast(new CounterStatusUpdated($counter));
                }
            });

            $message = $counter->is_online 
                ? 'You are now online and ready to serve customers.' 
                : 'You are now offline.';

            return [
                'success' => true,
                'message' => $message,
                'is_online' => $counter->is_online
            ];

        } catch (\Exception $e) {
            Log::error('Error toggling counter status: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update status'
            ];
        }
    }

    /**
     * Call next queue in line
     */
    public function callNextQueue(User $counter): array
    {
        if (!$counter->is_online) {
            return [
                'success' => false,
                'message' => 'Counter must be online to call queues'
            ];
        }

        try {
            $queue = DB::transaction(function () use ($counter) {
                // Get next waiting queue for this counter
                $queue = Queue::where('counter_id', $counter->id)
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
                if ($this->shouldBroadcast()) {
                    broadcast(new QueueCalled($queue));
                }

                return $queue;
            });

            if (!$queue) {
                return [
                    'success' => false,
                    'message' => 'No queues waiting'
                ];
            }

            return [
                'success' => true,
                'message' => 'Queue ' . $queue->queue_number . ' has been called',
                'queue' => $queue
            ];

        } catch (\Exception $e) {
            Log::error('Error calling next queue: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to call next queue'
            ];
        }
    }

    /**
     * Complete current queue and call next
     */
    public function moveToNext(User $counter): array
    {
        if (!$counter->is_online) {
            return [
                'success' => false,
                'message' => 'Counter must be online'
            ];
        }

        try {
            $result = DB::transaction(function () use ($counter) {
                // Complete current queue
                $currentQueue = $counter->getCurrentQueue();
                if ($currentQueue) {
                    $currentQueue->status = 'completed';
                    $currentQueue->completed_at = now();
                    $currentQueue->save();
                }

                // Call next queue
                $nextQueue = Queue::where('counter_id', $counter->id)
                    ->where('status', 'waiting')
                    ->orderBy('created_at')
                    ->first();

                if ($nextQueue) {
                    $nextQueue->status = 'called';
                    $nextQueue->called_at = now();
                    $nextQueue->save();

                    // Broadcast queue called event
                    if ($this->shouldBroadcast()) {
                        broadcast(new QueueCalled($nextQueue));
                    }
                }

                return $nextQueue;
            });

            return [
                'success' => true,
                'message' => $result ? 'Next queue called: ' . $result->queue_number : 'No more queues waiting',
                'queue' => $result
            ];

        } catch (\Exception $e) {
            Log::error('Error moving to next queue: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to move to next queue'
            ];
        }
    }

    /**
     * Skip current queue
     */
    public function skipQueue(User $counter): array
    {
        if (!$counter->is_online) {
            return [
                'success' => false,
                'message' => 'Counter must be online'
            ];
        }

        try {
            $queue = DB::transaction(function () use ($counter) {
                $currentQueue = $counter->getCurrentQueue();
                
                if (!$currentQueue) {
                    throw new \Exception('No queue to skip');
                }

                $currentQueue->status = 'skipped';
                $currentQueue->skipped_at = now();
                $currentQueue->save();

                return $currentQueue;
            });

            return [
                'success' => true,
                'message' => 'Queue ' . $queue->queue_number . ' has been skipped',
                'queue' => $queue
            ];

        } catch (\Exception $e) {
            Log::error('Error skipping queue: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Recall a skipped queue
     */
    public function recallQueue(User $counter, int $queueId): array
    {
        if (!$counter->is_online) {
            return [
                'success' => false,
                'message' => 'Counter must be online'
            ];
        }

        try {
            $queue = Queue::find($queueId);
            
            if (!$queue) {
                return [
                    'success' => false,
                    'message' => 'Queue not found'
                ];
            }

            // Verify queue belongs to current counter
            if ($queue->counter_id !== $counter->id) {
                return [
                    'success' => false,
                    'message' => 'This queue belongs to Counter ' . $queue->counter_id . '. Only the assigned counter can perform this action.'
                ];
            }

            // Verify queue is in skipped status
            if ($queue->status !== 'skipped') {
                return [
                    'success' => false,
                    'message' => 'Only skipped queues can be recalled. Current status: ' . $queue->status
                ];
            }

            // Recall the queue
            $result = DB::transaction(function () use ($queue) {
                $queue->status = 'called';
                $queue->called_at = now();
                $queue->skipped_at = null;
                $queue->save();

                // Broadcast queue called event
                if ($this->shouldBroadcast()) {
                    broadcast(new QueueCalled($queue));
                }

                return $queue;
            });

            return [
                'success' => true,
                'message' => 'Queue ' . $result->queue_number . ' has been recalled',
                'queue' => $result
            ];

        } catch (\Exception $e) {
            Log::error('Error recalling queue: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Transfer queue to another counter
     */
    public function transferQueue(User $fromCounter, int $queueId, int $toCounterId): array
    {
        if (!$fromCounter->is_online) {
            return [
                'success' => false,
                'message' => 'Counter must be online'
            ];
        }

        try {
            $queue = Queue::find($queueId);
            if (!$queue) {
                return [
                    'success' => false,
                    'message' => 'Queue not found'
                ];
            }

            $targetCounter = User::find($toCounterId);
            if (!$targetCounter) {
                return [
                    'success' => false,
                    'message' => 'Target counter not found'
                ];
            }

            $result = DB::transaction(function () use ($queue, $targetCounter, $fromCounter) {
                // Verify queue belongs to current counter
                if ($queue->counter_id !== $fromCounter->id) {
                    throw new \Exception('Queue does not belong to this counter');
                }

                // Verify target counter is online
                if (!$targetCounter->is_online) {
                    throw new \Exception('Target counter is offline');
                }

                // Transfer queue
                $queue->counter_id = $targetCounter->id;
                $queue->transferred_from = $fromCounter->id;
                $queue->transferred_at = now();
                $queue->status = 'waiting'; // Reset to waiting for new counter
                $queue->save();

                return $queue;
            });

            return [
                'success' => true,
                'message' => 'Queue transferred successfully',
                'queue' => $result
            ];

        } catch (\Exception $e) {
            Log::error('Error transferring queue: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Notify customer (placeholder for future implementation)
     */
    public function notifyCustomer(User $counter): array
    {
        try {
            $currentQueue = $counter->getCurrentQueue();

            if (!$currentQueue) {
                return [
                    'success' => false,
                    'message' => 'No current queue to notify'
                ];
            }

            // Update notified_at timestamp
            $currentQueue->notified_at = now();
            $currentQueue->save();

            Log::info('Customer notified successfully', [
                'counter_id' => $counter->id,
                'queue_id' => $currentQueue->id,
                'queue_number' => $currentQueue->queue_number,
                'notified_at' => $currentQueue->notified_at
            ]);

            return [
                'success' => true,
                'message' => 'Customer notification sent',
                'queue_number' => $currentQueue->queue_number
            ];

        } catch (\Exception $e) {
            Log::error('Error notifying customer: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to notify customer'
            ];
        }
    }

    /**
     * Get counter statistics with caching
     */
    public function getCounterStats(int $counterId, int $organizationId): array
    {
        $cacheKey = "counter_stats_{$counterId}_" . Carbon::today()->format('Y-m-d');
        
        return Cache::remember($cacheKey, 300, function () use ($counterId, $organizationId) {
            $counter = User::find($counterId);
            if (!$counter) {
                return $this->getEmptyStats();
            }

            $today = Carbon::today();
            
            // Average wait time (from created_at to called_at)
            $avgWaitTime = Queue::where('counter_id', $counterId)
                ->where('status', 'completed')
                ->whereDate('completed_at', $today)
                ->whereNotNull('called_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, called_at)) as avg_wait')
                ->value('avg_wait');

            // Average service time (from called_at to completed_at)
            $avgServiceTime = Queue::where('counter_id', $counterId)
                ->where('status', 'completed')
                ->whereDate('completed_at', $today)
                ->whereNotNull('called_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, called_at, completed_at)) as avg_service')
                ->value('avg_service');

            // Current queue number for display
            $currentNumber = null;
            $currentQueue = $counter->getCurrentQueue();
            if ($currentQueue && $currentQueue->queue_number) {
                $queueParts = explode('-', $currentQueue->queue_number);
                $currentNumber = $queueParts[array_key_last($queueParts)];
            }

            return [
                'waiting' => Queue::where('counter_id', $counterId)
                    ->where('status', 'waiting')
                    ->count(),
                'completed_today' => Queue::where('counter_id', $counterId)
                    ->where('status', 'completed')
                    ->whereDate('completed_at', $today)
                    ->count(),
                'current_queue' => $currentQueue,
                'current_number' => $currentNumber,
                'total_served' => Queue::where('counter_id', $counterId)
                    ->where('status', 'completed')
                    ->count(),
                'skipped_today' => Queue::where('counter_id', $counterId)
                    ->where('status', 'skipped')
                    ->whereDate('skipped_at', $today)
                    ->count(),
                'avg_wait_time' => round($avgWaitTime ?? 0, 1),
                'avg_service_time' => round($avgServiceTime ?? 0, 1)
            ];
        });
    }

    /**
     * Get analytics data for dashboard charts with caching
     */
    public function getAnalyticsData(int $counterId, int $organizationId): array
    {
        $cacheKey = "counter_analytics_{$counterId}_" . Carbon::today()->format('Y-m-d');
        
        return Cache::remember($cacheKey, 600, function () use ($counterId) {
            try {
                $today = Carbon::today();
                
                // Hourly completions for today
                $hourlyData = $this->getHourlyCompletions($counterId, $today);
                
                // Weekly completions (last 7 days)
                [$weeklyData, $weeklyDays] = $this->getWeeklyCompletions($counterId, $today);
                
                // Average wait time by day (last 7 days)
                $waitTimeData = $this->getWaitTimeData($counterId, $today);
                
                // Peak hours distribution (last 30 days)
                $peakHours = $this->getPeakHoursData($counterId, $today);
                
                return [
                    'hourly' => $hourlyData,
                    'weekly' => $weeklyData,
                    'weekly_days' => $weeklyDays,
                    'wait_time' => $waitTimeData,
                    'peak_hours' => $peakHours
                ];
                
            } catch (\Exception $e) {
                Log::error('Error generating analytics data: ' . $e->getMessage());
                return $this->getEmptyAnalytics();
            }
        });
    }

    /**
     * Get counter data for real-time updates
     */
    public function getCounterData(User $counter, Organization $organization): array
    {
        try {
            $currentQueue = $counter->getCurrentQueue();
            $waitingQueues = $counter->getWaitingQueues();
            $skippedQueues = $counter->getSkippedQueues();
            $onlineCounters = $this->getOnlineCounters($organization->id, $counter->id);
            $stats = $this->getCounterStats($counter->id, $organization->id);
            $analytics = $this->getAnalyticsData($counter->id, $organization->id);

            return [
                'success' => true,
                'online_status' => $counter->is_online,
                'current_queue' => $currentQueue,
                'waiting_queues' => $waitingQueues,
                'skipped' => $skippedQueues,
                'online_counters' => $onlineCounters,
                'served_today' => $stats['completed_today'],
                'stats' => $stats,
                'analytics' => $analytics
            ];

        } catch (\Exception $e) {
            Log::error('Error fetching counter data: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch counter data'
            ];
        }
    }

    /**
     * Get online counters excluding current counter
     */
    private function getOnlineCounters(int $organizationId, int $excludeCounterId): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('organization_id', $organizationId)
            ->where('role', 'counter')
            ->where('is_online', true)
            ->where('id', '!=', $excludeCounterId)
            ->orderBy('counter_number')
            ->get();
    }

    /**
     * Check if broadcasting should be enabled
     */
    private function shouldBroadcast(): bool
    {
        return config('broadcasting.default') !== 'null';
    }

    /**
     * Get hourly completions for today
     */
    private function getHourlyCompletions(int $counterId, Carbon $today): array
    {
        $hourlyData = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $count = Queue::where('counter_id', $counterId)
                ->where('status', 'completed')
                ->whereDate('completed_at', $today)
                ->whereRaw('HOUR(completed_at) = ?', [$hour])
                ->count();
            $hourlyData[] = $count;
        }
        return $hourlyData;
    }

    /**
     * Get weekly completions (last 7 days)
     */
    private function getWeeklyCompletions(int $counterId, Carbon $today): array
    {
        $weeklyData = [];
        $weeklyDays = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $count = Queue::where('counter_id', $counterId)
                ->where('status', 'completed')
                ->whereDate('completed_at', $date)
                ->count();
            $weeklyData[] = $count;
            $weeklyDays[] = $date->format('M j');
        }
        
        return [$weeklyData, $weeklyDays];
    }

    /**
     * Get average wait time by day (last 7 days)
     */
    private function getWaitTimeData(int $counterId, Carbon $today): array
    {
        $waitTimeData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $avgWaitTime = Queue::where('counter_id', $counterId)
                ->where('status', 'completed')
                ->whereDate('completed_at', $date)
                ->whereNotNull('called_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, called_at)) as avg_wait')
                ->value('avg_wait');
            $waitTimeData[] = round($avgWaitTime ?? 0, 1);
        }
        
        return $waitTimeData;
    }

    /**
     * Get peak hours distribution (last 30 days)
     */
    /**
     * Get specific counter analytics metric
     */
    public function getCounterAnalytics(int $counterId, string $metric): mixed
    {
        try {
            switch ($metric) {
                case 'daily_served':
                    return Queue::where('counter_id', $counterId)
                        ->where('status', 'completed')
                        ->whereDate('completed_at', Carbon::today())
                        ->count();

                case 'avg_wait_time':
                    return Queue::where('counter_id', $counterId)
                        ->where('status', 'completed')
                        ->whereDate('completed_at', Carbon::today())
                        ->average(DB::raw('TIMESTAMPDIFF(MINUTE, created_at, called_at)'));

                case 'avg_service_time':
                    return Queue::where('counter_id', $counterId)
                        ->where('status', 'completed')
                        ->whereDate('completed_at', Carbon::today())
                        ->average(DB::raw('TIMESTAMPDIFF(MINUTE, called_at, completed_at)'));

                case 'efficiency':
                    $totalTime = Queue::where('counter_id', $counterId)
                        ->where('status', 'completed')
                        ->whereDate('completed_at', Carbon::today())
                        ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, called_at, completed_at)'));

                    $onlineTime = Carbon::now()->diffInMinutes(
                        User::find($counterId)->updated_at
                    );

                    return $onlineTime > 0 ? round(($totalTime / $onlineTime) * 100, 2) : 0;

                case 'peak_hours':
                    return Queue::where('counter_id', $counterId)
                        ->where('status', 'completed')
                        ->whereDate('completed_at', '>=', Carbon::today()->subDays(30))
                        ->selectRaw('HOUR(completed_at) as hour, COUNT(*) as count')
                        ->groupBy('hour')
                        ->orderByDesc('count')
                        ->limit(3)
                        ->pluck('count', 'hour');

                default:
                    return null;
            }
        } catch (\Exception $e) {
            Log::error("Counter analytics error [{$metric}]: " . $e->getMessage());
            return null;
        }
    }

    private function getPeakHoursData(int $counterId, Carbon $today): array
    {
        $thirtyDaysAgo = $today->copy()->subDays(30);
        
        return [
            // Peak Hours (9am-5pm)
            Queue::where('counter_id', $counterId)
                ->where('status', 'completed')
                ->where('completed_at', '>=', $thirtyDaysAgo)
                ->whereRaw('TIME(completed_at) >= ?', ['09:00:00'])
                ->whereRaw('TIME(completed_at) < ?', ['17:00:00'])
                ->count(),
            // Morning (6am-9am)
            Queue::where('counter_id', $counterId)
                ->where('status', 'completed')
                ->where('completed_at', '>=', $thirtyDaysAgo)
                ->whereRaw('TIME(completed_at) >= ?', ['06:00:00'])
                ->whereRaw('TIME(completed_at) < ?', ['09:00:00'])
                ->count(),
            // Evening (5pm-9pm)
            Queue::where('counter_id', $counterId)
                ->where('status', 'completed')
                ->where('completed_at', '>=', $thirtyDaysAgo)
                ->whereRaw('TIME(completed_at) >= ?', ['17:00:00'])
                ->whereRaw('TIME(completed_at) < ?', ['21:00:00'])
                ->count(),
            // Night (9pm-6am)
            Queue::where('counter_id', $counterId)
                ->where('status', 'completed')
                ->where('completed_at', '>=', $thirtyDaysAgo)
                ->whereRaw('(TIME(completed_at) >= ? OR TIME(completed_at) < ?)', ['21:00:00', '06:00:00'])
                ->count()
        ];
    }

    /**
     * Get empty stats structure
     */
    private function getEmptyStats(): array
    {
        return [
            'waiting' => 0,
            'completed_today' => 0,
            'current_queue' => null,
            'current_number' => null,
            'total_served' => 0,
            'skipped_today' => 0,
            'avg_wait_time' => 0,
            'avg_service_time' => 0
        ];
    }

    /**
     * Get empty analytics structure
     */
    private function getEmptyAnalytics(): array
    {
        return [
            'hourly' => array_fill(0, 24, 0),
            'weekly' => array_fill(0, 7, 0),
            'weekly_days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'wait_time' => array_fill(0, 7, 0),
            'peak_hours' => [0, 0, 0, 0]
        ];
    }
}