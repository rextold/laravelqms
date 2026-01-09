<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\User;
use App\Models\CompanySetting;
use App\Events\QueueCreated;
use App\Events\QueueCalled;
use App\Events\QueueTransferred;
use Carbon\Carbon;

class QueueService
{
    public function generateQueueNumber(User $counter): string
    {
        $settings = CompanySetting::getSettings();
        // Default to 4 digits if setting is missing or invalid to avoid TypeErrors
        $digits = (int) ($settings->queue_number_digits ?? 4);
        if ($digits <= 0) {
            $digits = 4;
        }

        // If a priority code exists on the counter, use it as the prefix (e.g., C1-1001)
        $priorityCode = trim((string) ($counter->priority_code ?? ''));
        if ($priorityCode !== '') {
            $prefix = $priorityCode;
            $lastQueue = Queue::where('queue_number', 'like', "{$prefix}-%")
                ->orderBy('queue_number', 'desc')
                ->first();

            $base = 1; // start from 0001 for priority codes
            if ($lastQueue) {
                $lastNumber = (int) substr($lastQueue->queue_number, -$digits);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = $base;
            }

            $newNumber = str_pad($nextNumber, $digits, '0', STR_PAD_LEFT);

            return "{$prefix}-{$newNumber}";
        }

        // Fallback: original date + counter number format
        $date = Carbon::now()->format('Ymd');
        $counterPrefix = str_pad($counter->counter_number, 2, '0', STR_PAD_LEFT);
        
        $lastQueue = Queue::where('queue_number', 'like', "{$date}-{$counterPrefix}-%")
            ->orderBy('queue_number', 'desc')
            ->first();

        if ($lastQueue) {
            $lastNumber = (int) substr($lastQueue->queue_number, -$digits);
            $newNumber = str_pad($lastNumber + 1, $digits, '0', STR_PAD_LEFT);
        } else {
            $newNumber = str_pad(1, $digits, '0', STR_PAD_LEFT);
        }

        return "{$date}-{$counterPrefix}-{$newNumber}";
    }

    public function createQueue(User $counter): Queue
    {
        $queueNumber = $this->generateQueueNumber($counter);
        
        $queue = Queue::create([
            'queue_number' => $queueNumber,
            'counter_id' => $counter->id,
            'status' => 'waiting',
        ]);

        broadcast(new QueueCreated($queue))->toOthers();

        return $queue;
    }

    public function callNextQueue(User $counter): ?Queue
    {
        // Complete current serving queue
        $currentQueue = $counter->getCurrentQueue();
        if ($currentQueue) {
            $currentQueue->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        // Get next waiting queue ordered by updated_at
        $nextQueue = $counter->queues()
            ->where('status', 'waiting')
            ->orderBy('updated_at')
            ->first();

        if ($nextQueue) {
            $nextQueue->update([
                'status' => 'called',
                'called_at' => now(),
            ]);

            broadcast(new QueueCalled($nextQueue))->toOthers();
        }

        return $nextQueue;
    }

    public function moveToNext(User $counter): ?Queue
    {
        $currentQueue = $counter->getCurrentQueue();
        
        if ($currentQueue) {
            $currentQueue->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        return $this->callNextQueue($counter);
    }

    public function transferQueue(Queue $queue, User $toCounter): Queue
    {
        // Transfer the queue to the new counter while retaining the same number
        // Set status back to waiting so the new counter must call it explicitly
        $queue->update([
            'counter_id' => $toCounter->id,
            'transferred_to' => $toCounter->id,
            'status' => 'waiting',
            'called_at' => null,
        ]);

        broadcast(new QueueTransferred($queue, $toCounter))->toOthers();

        return $queue;
    }

    public function getCounterStats(User $counter): array
    {
        $today = Carbon::today();

        return [
            'waiting' => $counter->queues()->waiting()->count(),
            'completed_today' => $counter->queues()
                ->completed()
                ->whereDate('completed_at', $today)
                ->count(),
            'current_queue' => $counter->getCurrentQueue(),
        ];
    }
}
