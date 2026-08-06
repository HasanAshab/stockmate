<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'image_url' => $this->getFirstMediaUrl('product_images') ?: null,
            'image_thumb_url' => $this->getFirstMediaUrl('product_images', 'thumb') ?: null,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'supplier' => SupplierResource::make($this->whenLoaded('supplier')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
