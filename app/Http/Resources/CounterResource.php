<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CounterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'display_name' => $this->display_name,
            'counter_number' => $this->counter_number,
            'counter_code' => $this->counter_code,
            'priority_code' => $this->priority_code,
            'short_description' => $this->short_description,
            'is_online' => $this->is_online,
            'is_active' => $this->is_active,
            'organization_id' => $this->organization_id,
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
            'current_queue' => new QueueResource($this->whenLoaded('currentQueue')),
            'waiting_queues_count' => $this->when(
                $this->relationLoaded('queues'),
                fn() => $this->queues->where('status', 'waiting')->count()
            ),
            'completed_today_count' => $this->when(
                isset($this->completed_today_count),
                $this->completed_today_count
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}