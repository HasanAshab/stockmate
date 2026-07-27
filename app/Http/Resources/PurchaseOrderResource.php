<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supplier_name' => $this->whenLoaded('supplier', fn () => $this->supplier->name),
            'warehouse_name' => $this->whenLoaded('warehouse', fn () => $this->warehouse->name),
            'status' => $this->status->name,
            'item_count' => $this->whenLoaded('items', fn () => $this->items->count()),
            'note' => $this->note,
            'ordered_at' => $this->ordered_at,
            'received_at' => $this->received_at,
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'items' => PurchaseOrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
