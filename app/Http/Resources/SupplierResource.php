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
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->when(
                $request->routeIs('suppliers.show'),
                $this->address
            ),
        ];
    }
}
