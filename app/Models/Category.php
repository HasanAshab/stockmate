<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[WithoutTimestamps]
#[Fillable('name', 'slug', 'description')]
class Category extends Model
{
    use SoftDeletes;

    protected $attributes = [
        'description' => '',
    ];
}
