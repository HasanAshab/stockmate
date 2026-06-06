<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', 'unique:products'],
            'price' => ['required', 'numeric', 'min:0'],
            'reorder_threshold' => ['integer:strict', 'min:0'],
            'image' => ['image', 'max:2048', 'mimes:jpeg,png,jpg,webp'],
        ];
    }
}
