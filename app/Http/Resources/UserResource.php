<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class UserResource extends JsonApiResource
{
    public $attributes = [
        'name',
        'email',
        'role',
        'is_active',
        'created_at',
        'updated_at',
    ];
}
