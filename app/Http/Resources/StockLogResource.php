<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class StockLogResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'product_id' => $this->product_id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'unit_cost' => $this->unit_cost,
            'note' => $this->note,
            'created_at' => $this->created_at,
        ];
    }
}
