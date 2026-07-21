<?php

namespace App\Http\Resources\Search;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSearchResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => 'product',
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'image_url' => $this->getFirstMediaUrl('product_images') ?: null,
            'image_thumb_url' => $this->getFirstMediaUrl('product_images', 'thumb') ?: null,
        ];
    }
}
