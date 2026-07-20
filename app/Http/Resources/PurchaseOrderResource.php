<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class PurchaseOrderResource extends JsonApiResource
{
    public $relationships = [
        'supplier',
        'warehouse',
        'creator',
        'items',
    ];

    public function toAttributes(Request $request): array
    {
        return [
            'supplier_name' => $this->whenLoaded('supplier', fn () => $this->supplier->name),
            'warehouse_name' => $this->whenLoaded('warehouse', fn () => $this->warehouse->name),
            'status' => $this->status->name,
            'item_count' => $this->whenLoaded('items', fn () => $this->items->count()),
            'note' => $this->note,
            'ordered_at' => $this->ordered_at,
            'received_at' => $this->received_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
