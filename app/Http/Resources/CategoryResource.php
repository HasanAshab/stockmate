<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class CategoryResource extends JsonApiResource
{
    public function toAttributes(Request $request)
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->when(
                ! $request->routeIs('categories.index'),
                $this->description
            ),
        ];
    }
}
