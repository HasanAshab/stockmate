<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class WarehouseStockResource extends JsonApiResource
{
    public $attributes = [
        'quantity',
        'reorder_threshold',
    ];
}
