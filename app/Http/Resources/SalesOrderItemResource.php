<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_name' => $this->whenLoaded('product', fn () => $this->product->name),
            'product_sku' => $this->whenLoaded('product', fn () => $this->product->sku),
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'product' => new ProductResource($this->whenLoaded('product')),
            'sales_order' => new SalesOrderResource($this->whenLoaded('salesOrder')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
