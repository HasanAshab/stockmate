<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ProductResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'reorder_threshold' => $this->reorder_threshold,
            'image_url' => $this->getFirstMediaUrl('product_images') ?: null,
            'image_thumb_url' => $this->getFirstMediaUrl('product_images', 'thumb') ?: null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function toRelationships(Request $request) {
        return [
            'category' => $this->whenLoaded('category'),
            'supplier' => $this->whenLoaded('supplier'),
        ];
    }
}
