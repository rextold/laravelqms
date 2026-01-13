<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CounterStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $organizationCode;
    public $counterId;
    public $status; // 'online' or 'offline'

    public function __construct($organizationCode, $counterId, $status)
    {
        $this->organizationCode = $organizationCode;
        $this->counterId = $counterId;
        $this->status = $status;
    }

    public function broadcastOn()
    {
        return new Channel('organization.' . $this->organizationCode . '.counters');
    }

    public function broadcastWith()
    {
        return [
            'counter_id' => $this->counterId,
            'status' => $this->status,
        ];
    }
}
