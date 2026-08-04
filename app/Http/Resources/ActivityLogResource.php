<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Activitylog\Models\Activity;

/**
 * @property Activity $resource
 */
class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'log_name' => $this->log_name,
            'description' => $this->description,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'done_by' => $this->causer?->name ?? 'System',
            /** @var array<string, mixed>|null */
            'changes' => $this->properties['attributes'] ?? null,
            /** @var array<string, mixed>|null */
            'old_values' => $this->properties['old'] ?? null,
            'created_at' => $this->created_at,
            'causer' => UserResource::make($this->whenLoaded('causer')),
        ];
    }
}
