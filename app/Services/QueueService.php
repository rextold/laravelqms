<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\User;
use App\Events\QueueCreated;
use App\Events\QueueCalled;
use App\Events\QueueTransferred;
use Carbon\Carbon;

class QueueService
{
    public function generateQueueNumber(User $counter): string
    {
        $date = Carbon::now()->format('Ymd');
        $counterPrefix = str_pad($counter->counter_number, 2, '0', STR_PAD_LEFT);
        
        $lastQueue = Queue::where('queue_number', 'like', "{$date}-{$counterPrefix}-%")
            ->orderBy('queue_number', 'desc')
            ->first();

        if ($lastQueue) {
            $lastNumber = (int) substr($lastQueue->queue_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
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

        // Get next waiting queue
        $nextQueue = $counter->queues()
            ->where('status', 'waiting')
            ->orderBy('created_at')
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
        $queue->update([
            'transferred_to' => $toCounter->id,
            'counter_id' => $toCounter->id,
            'status' => 'transferred',
        ]);

        // Create new queue entry for the target counter
        $newQueue = Queue::create([
            'queue_number' => $queue->queue_number,
            'counter_id' => $toCounter->id,
            'status' => 'waiting',
        ]);

        broadcast(new QueueTransferred($newQueue, $toCounter))->toOthers();

        return $newQueue;
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
