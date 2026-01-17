<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QueueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'queue_number' => $this->queue_number,
            'status' => $this->status,
            'counter_id' => $this->counter_id,
            'counter' => new CounterResource($this->whenLoaded('counter')),
            'transferred_to' => $this->transferred_to,
            'transferred_counter' => new CounterResource($this->whenLoaded('transferredCounter')),
            'organization_id' => $this->organization_id,
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
            'called_at' => $this->called_at,
            'notified_at' => $this->notified_at,
            'skipped_at' => $this->skipped_at,
            'completed_at' => $this->completed_at,
            'is_notified_recently' => $this->isNotifiedRecently(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}