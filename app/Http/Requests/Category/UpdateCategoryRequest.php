<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['string', 'max:70'],
            'slug' => ['string', 'max:70', 'unique:categories'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
