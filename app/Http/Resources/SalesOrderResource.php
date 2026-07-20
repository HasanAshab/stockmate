<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class SalesOrderResource extends JsonApiResource
{
    public $relationships = [
        'warehouse',
        'creator',
        'items',
    ];

    public function toAttributes(Request $request): array
    {
        return [
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'warehouse_name' => $this->whenLoaded('warehouse', fn () => $this->warehouse->name),
            'status' => $this->status->name,
            'total_amount' => $this->total_amount,
            'transaction_id' => $this->transaction_id,
            'item_count' => $this->whenLoaded('items', fn () => $this->items->count()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
