<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class PurchaseOrderItemResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'product_name' => $this->whenLoaded('product', fn () => $this->product->name),
            'product_sku' => $this->whenLoaded('product', fn () => $this->product->sku),
            'ordered_quantity' => $this->ordered_quantity,
            'received_quantity' => $this->received_quantity,
            'unit_cost' => $this->unit_cost,
            'is_fully_received' => $this->isFullyReceived(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function toRelationships(Request $request)
    {
        return [
            'product' => $this->whenLoaded('product'),
            'purchaseOrder' => $this->whenLoaded('purchaseOrder'),
        ];
    }
}
