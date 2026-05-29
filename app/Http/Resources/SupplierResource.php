<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class SupplierResource extends JsonApiResource
{
    public function toAttributes(Request $request)
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'contact_name' => $this->contact_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'description' => $this->when(
                $request->routeIs('suppliers.show'),
                $this->description
            ),
        ];
    }
}
