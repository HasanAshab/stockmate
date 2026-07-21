<?php

namespace App\Http\Resources\Search;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseSearchResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => 'warehouse',
            'name' => $this->name,
            'location' => $this->location,
            'is_active' => $this->is_active,
        ];
    }
}
