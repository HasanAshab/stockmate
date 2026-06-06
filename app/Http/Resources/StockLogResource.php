<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class StockLogResource extends JsonApiResource
{
    public $relationships = [
        'user',
        'product',
        'warehouse',
    ];

    public function toAttributes(Request $request): array
    {
        return [
            'type' => $this->type,
            'quantity' => $this->quantity,
            'unit_cost' => $this->unit_cost,
            'note' => $this->note,
            'created_at' => $this->created_at,
        ];
    }
}
