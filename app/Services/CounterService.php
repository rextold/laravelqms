<?php

namespace App\Services;

use App\Events\CounterStatusUpdated;
use App\Events\QueueCalled;
use App\Models\Queue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CounterService
{
    protected QueueService $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * Toggle counter online/offline status
     */
    public function toggleOnlineStatus(User $counter): array
    {
        if (!$counter->isCounter()) {
            throw new \InvalidArgumentException('User must be a counter');
        }

        return DB::transaction(function () use ($counter) {
            $wasOnline = $counter->is_online;
            $counter->is_online = !$counter->is_online;
            $counter->save();

            // Broadcast status update
            if ($this->shouldBroadcast()) {
                broadcast(new CounterStatusUpdated($counter));
            }

            $message = $counter->is_online 
                ? 'You are now online and ready to serve customers.' 
                : 'You are now offline.';

            return [
                'success' => true,
                'message' => $message,
                'is_online' => $counter->is_online,
                'was_online' => $wasOnline
            ];
        });
    }

    /**
     * Call next queue for counter
     */
    public function callNextQueue(User $counter): ?Queue
    {
        if (!$counter->isCounter() || !$counter->is_online) {
            throw new \InvalidArgumentException('Counter must be online to call queues');
        }

        return DB::transaction(function () use ($counter) {
            // Get next waiting queue for this counter
            $queue = Queue::where('counter_id', $counter->id)
                ->where('status', 'waiting')
                ->orderBy('created_at')
                ->first();

            if (!$queue) {
                return null;
            }

            // Update queue status
            $queue->update([
                'status' => 'called',
                'called_at' => now(),
            ]);

            // Broadcast queue called event
            if ($this->shouldBroadcast()) {
                broadcast(new QueueCalled($queue));
            }

            return $queue->fresh();
        });
    }

    /**
     * Complete current queue and optionally call next
     */
    public function completeCurrentQueue(User $counter, bool $callNext = false): ?Queue
    {
        if (!$counter->isCounter() || !$counter->is_online) {
            throw new \InvalidArgumentException('Counter must be online');
        }

        return DB::transaction(function () use ($counter, $callNext) {
            // Complete current queue
            $currentQueue = $counter->getCurrentQueue();
            if ($currentQueue) {
                $currentQueue->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }

            // Optionally call next queue
            if ($callNext) {
                return $this->callNextQueue($counter);
            }

            return null;
        });
    }

    /**
     * Skip current queue
     */
    public function skipCurrentQueue(User $counter): Queue
    {
        if (!$counter->isCounter() || !$counter->is_online) {
            throw new \InvalidArgumentException('Counter must be online');
        }

        return DB::transaction(function () use ($counter) {
            $currentQueue = $counter->getCurrentQueue();
            
            if (!$currentQueue) {
                throw new \RuntimeException('No queue to skip');
            }

            $currentQueue->update([
                'status' => 'skipped',
                'skipped_at' => now(),
            ]);

            return $currentQueue->fresh();
        });
    }

    /**
     * Recall a skipped queue
     */
    public function recallQueue(User $counter, int $queueId): Queue
    {
        if (!$counter->isCounter() || !$counter->is_online) {
            throw new \InvalidArgumentException('Counter must be online');
        }

        return DB::transaction(function () use ($counter, $queueId) {
            $queue = Queue::findOrFail($queueId);

            // Verify queue belongs to current counter
            if ($queue->counter_id !== $counter->id) {
                throw new \RuntimeException('Queue does not belong to this counter');
            }

            // Verify queue is in skipped status
            if ($queue->status !== 'skipped') {
                throw new \RuntimeException('Only skipped queues can be recalled');
            }

            // Complete any current queue first
            $currentQueue = $counter->getCurrentQueue();
            if ($currentQueue) {
                $currentQueue->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }

            // Recall the queue
            $queue->update([
                'status' => 'called',
                'called_at' => now(),
                'skipped_at' => null,
            ]);

            // Broadcast queue called event
            if ($this->shouldBroadcast()) {
                broadcast(new QueueCalled($queue));
            }

            return $queue->fresh();
        });
    }

    /**
     * Transfer queue to another counter
     */
    public function transferQueue(User $fromCounter, int $queueId, int $toCounterId): Queue
    {
        if (!$fromCounter->isCounter() || !$fromCounter->is_online) {
            throw new \InvalidArgumentException('Source counter must be online');
        }

        return DB::transaction(function () use ($fromCounter, $queueId, $toCounterId) {
            $queue = Queue::findOrFail($queueId);
            $targetCounter = User::findOrFail($toCounterId);

            // Verify queue belongs to current counter
            if ($queue->counter_id !== $fromCounter->id) {
                throw new \RuntimeException('Queue does not belong to this counter');
            }

            // Verify target counter is online and is a counter
            if (!$targetCounter->isCounter() || !$targetCounter->is_online) {
                throw new \RuntimeException('Target counter must be online');
            }

            // Transfer queue
            $queue->update([
                'counter_id' => $targetCounter->id,
                'transferred_to' => $targetCounter->id,
                'status' => 'waiting', // Reset to waiting for new counter
                'called_at' => null,
            ]);

            return $queue->fresh();
        });
    }

    /**
     * Get counter statistics
     */
    public function getCounterStats(User $counter): array
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

        // Current queue number for display
        $currentNumber = null;
        $currentQueue = $counter->getCurrentQueue();
        if ($currentQueue && $currentQueue->queue_number) {
            $queueParts = explode('-', $currentQueue->queue_number);
            $currentNumber = end($queueParts);
        }

        return [
            'waiting' => $counter->getWaitingQueues()->count(),
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
            'avg_service_time' => round($avgServiceTime ?? 0, 1),
        ];
    }

    /**
     * Get analytics data for dashboard charts
     */
    public function getAnalyticsData(User $counter): array
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
     * Get online counters for organization
     */
    public function getOnlineCounters(int $organizationId, ?int $excludeCounterId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = User::where('organization_id', $organizationId)
            ->where('role', 'counter')
            ->where('is_online', true)
            ->orderBy('counter_number');

        if ($excludeCounterId) {
            $query->where('id', '!=', $excludeCounterId);
        }

        return $query->get(['id', 'counter_number', 'display_name']);
    }

    /**
     * Check if broadcasting should be enabled
     */
    private function shouldBroadcast(): bool
    {
        $broadcastDriver = (string) config('broadcasting.default');
        $queueDriver = (string) config('queue.default');

        if ($broadcastDriver === '' || $broadcastDriver === 'null') {
            return false;
        }

        // If the queue driver is sync, broadcasting will happen inline and can cause noticeable UI delay.
        // Since the app already uses polling for kiosk/monitor, we prefer fast responses here.
        if ($queueDriver === 'sync') {
            return false;
        }

        return true;
    }
}