<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ActivityLogResource extends JsonApiResource
{
    public $relationships = [
        'causer',
        'subject',
    ];

    public function toAttributes(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'subject_type' => class_basename($this->subject_type),
            'subject_id' => $this->subject_id,
            'done_by' => $this->causer?->name ?? 'System',
            'changes' => $this->properties['attributes'] ?? null,
            'old_values' => $this->properties['old'] ?? null,
            'when' => $this->created_at,
        ];
    }
}
