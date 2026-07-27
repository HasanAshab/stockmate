<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_name' => $this->whenLoaded('product', fn () => $this->product->name),
            'product_sku' => $this->whenLoaded('product', fn () => $this->product->sku),
            'ordered_quantity' => $this->ordered_quantity,
            'received_quantity' => $this->received_quantity,
            'unit_cost' => $this->unit_cost,
            'is_fully_received' => $this->isFullyReceived(),
            'product' => new ProductResource($this->whenLoaded('product')),
            'purchase_order' => new PurchaseOrderResource($this->whenLoaded('purchaseOrder')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
