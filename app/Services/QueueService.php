<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\User;
use App\Models\OrganizationSetting;
use App\Events\QueueCreated;
use App\Events\QueueCalled;
use App\Events\QueueTransferred;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QueueService
{
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

    public function createQueue(User $counter): Queue
    {
        $organizationId = $counter->organization_id;

        $queue = DB::transaction(function () use ($counter, $organizationId) {
            // Ensure we have settings for THIS organization (do not rely on session)
            $settingsQuery = OrganizationSetting::query();
            if ($organizationId) {
                $settingsQuery->where('organization_id', $organizationId);
            }

            /** @var OrganizationSetting|null $settings */
            $settings = $settingsQuery->lockForUpdate()->first();

            if (!$settings) {
                // Fallback to singleton getter (uses session), but prefer explicit org
                $settings = OrganizationSetting::getSettings();
            }

            $digits = (int) ($settings->queue_number_digits ?? 4);
            if ($digits <= 0) {
                $digits = 4;
            }

            // Initialize sequence from existing queues if needed
            $lastSeq = (int) ($settings->last_queue_sequence ?? 0);
            if ($organizationId && $lastSeq <= 0) {
                // Fast path: queue numbers are now digits-only; use most recent row and parse suffix.
                // This avoids full-table scans on large queue history.
                $latestQueueNumber = (string) (Queue::where('organization_id', $organizationId)
                    ->orderByDesc('id')
                    ->value('queue_number') ?? '');

                if ($latestQueueNumber !== '') {
                    $parts = explode('-', $latestQueueNumber);
                    $suffix = end($parts);
                    $lastSeq = max($lastSeq, (int) $suffix);
                }
            }

            $nextSeq = $lastSeq + 1;
            $settings->last_queue_sequence = $nextSeq;
            $settings->save();

            $queueNumber = str_pad((string) $nextSeq, $digits, '0', STR_PAD_LEFT);

            return Queue::create([
                'queue_number' => $queueNumber,
                'counter_id' => $counter->id,
                'organization_id' => $organizationId,
                'status' => 'waiting',
            ]);
        });

        if ($this->shouldBroadcast()) {
            broadcast(new QueueCreated($queue))->toOthers();
        }

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

            if ($this->shouldBroadcast()) {
                broadcast(new QueueCalled($nextQueue))->toOthers();
            }
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

        // Do NOT auto-call the next queue. Counter must explicitly press "Call Next".
        return null;
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

        if ($this->shouldBroadcast()) {
            broadcast(new QueueTransferred($queue, $toCounter))->toOthers();
        }

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

    public function skipQueue(User $counter): ?Queue
    {
        $currentQueue = $counter->getCurrentQueue();

        if ($currentQueue) {
            $currentQueue->update([
                'status' => 'skipped',
                'skipped_at' => now(),
            ]);
        }

        return $currentQueue;
    }

    public function recallQueue(User $counter): ?Queue
    {
        // Find the last skipped queue for this counter
        $recalledQueue = $counter->queues()
            ->where('status', 'skipped')
            ->orderByDesc('skipped_at')
            ->first();

        if ($recalledQueue) {
            // Mark any currently "called" queue as completed first
            $currentQueue = $counter->getCurrentQueue();
            if ($currentQueue) {
                $currentQueue->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }
            
            $recalledQueue->update([
                'status' => 'called',
                'called_at' => now(),
                'skipped_at' => null,
            ]);

            if ($this->shouldBroadcast()) {
                broadcast(new QueueCalled($recalledQueue))->toOthers();
            }
        }

        return $recalledQueue;
    }

    public function notifyCustomer(User $counter): void
    {
        $currentQueue = $counter->getCurrentQueue();

        if ($currentQueue && $this->shouldBroadcast()) {
            broadcast(new QueueCalled($currentQueue))->toOthers();
        }
    }
}