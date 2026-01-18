<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class VideoControlUpdated implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $control;
    public $meta;

    public function __construct($control, $meta = [])
    {
        $this->control = $control;
        $this->meta = $meta;
    }

    public function broadcastOn()
    {
        // Broadcast on a public channel; monitors can subscribe if configured.
        return new Channel('video-control');
    }

    public function broadcastWith()
    {
        // Limit payload to useful control fields
        return [
            'control' => [
                'current_video_id' => $this->control->current_video_id ?? null,
                'is_playing' => (bool) ($this->control->is_playing ?? false),
                'volume' => $this->control->volume ?? 50,
                'bell_volume' => $this->control->bell_volume ?? 100,
                'video_muted' => $this->control->video_muted ?? false,
                'autoplay' => $this->control->autoplay ?? false,
                'loop' => $this->control->loop ?? false,
                'bell_choice' => $this->control->bell_choice ?? null,
            ],
            'meta' => $this->meta ?? []
        ];
    }
}
