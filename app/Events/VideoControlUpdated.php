<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
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
        // Broadcast on an organization-scoped public channel so only monitors for
        // that organization receive updates. Fallback to global channel if missing.
        $orgId = null;
        try {
            $orgId = $this->control->organization_id ?? null;
        } catch (\Throwable $_) {
            $orgId = null;
        }

        $channel = $orgId ? 'video-control.' . $orgId : 'video-control';
        return new Channel($channel);
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
