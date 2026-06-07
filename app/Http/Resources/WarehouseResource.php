<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class WarehouseResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,
            'is_active' => $this->is_active,
            'stocks_count' => $this->whenCounted('warehouseStocks'),
            'created_at' => $this->created_at,
        ];
    }
}
