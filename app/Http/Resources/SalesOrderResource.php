<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'warehouse_name' => $this->whenLoaded('warehouse', fn () => $this->warehouse->name),
            'status' => $this->status->value,
            'total_amount' => $this->total_amount,
            'transaction_reference' => $this->transaction_reference,
            'item_count' => $this->whenLoaded('items', fn () => $this->items->count()),
            'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'items' => SalesOrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
