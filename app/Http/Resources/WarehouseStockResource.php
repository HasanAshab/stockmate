<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseStockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->product_id,
            'product_name' => $this->product->name,
            'sku' => $this->product->sku,
            'category' => $this->product->category->name,
            'quantity' => $this->quantity,
            'reorder_threshold' => $this->reorder_threshold,
            'status' => $this->isLow() ? 'low' : 'ok',
        ];
    }
}
