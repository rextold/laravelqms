<?php

namespace App\Events;

use App\Models\Queue;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueTransferred implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queue;
    public $toCounter;

    public function __construct(Queue $queue, User $toCounter)
    {
        $this->queue = $queue;
        $this->toCounter = $toCounter;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('queues'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'queue' => $this->queue,
            'to_counter' => $this->toCounter,
        ];
    }
}
