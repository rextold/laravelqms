<?php

namespace App\Events;

use App\Models\Queue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queue;

    public function __construct(Queue $queue)
    {
        $this->queue = $queue->load('counter');
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
            'counter' => $this->queue->counter,
        ];
    }
}
