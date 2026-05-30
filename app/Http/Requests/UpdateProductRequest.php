<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['exists:categories,id'],
            'supplier_id' => ['exists:suppliers,id'],
            'name' => ['string', 'max:255'],
            'sku' => ['string', 'max:255', 'unique:products'],
            'price' => ['numeric:strict', 'min:0'],
            'reorder_threshold' => ['integer:strict', 'min:0'],
            'image' => ['nullable', 'string', 'max:255'],
        ];
    }
}
