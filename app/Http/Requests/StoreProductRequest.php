<?php

namespace App\Http\Requests;

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
            'reorder_threshold' => ['integer', 'min:0'],
            'image' => ['nullable', 'string', 'max:255'],
        ];
    }
}
