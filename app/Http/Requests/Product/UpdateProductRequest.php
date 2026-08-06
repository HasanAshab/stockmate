<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['exists:categories,id'],
            'supplier_id' => ['exists:suppliers,id'],
            'name' => ['string', 'max:255'],
            'sku' => ['string', 'max:255', 'unique:products,sku,'.$this->product->id],
            'price' => ['numeric:strict', 'min:0'],
            'reorder_threshold' => ['integer:strict', 'min:0'],
            'image' => ['image', 'max:2048', 'mimes:jpeg,png,jpg,webp'],
        ];
    }
}
