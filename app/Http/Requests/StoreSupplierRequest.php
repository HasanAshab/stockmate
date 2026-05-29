<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:70'],
            'slug' => ['required', 'string', 'max:70', 'unique:suppliers'],
            'contact_name' => ['string', 'max:70'],
            'email' => ['email', 'max:100'],
            'phone' => ['string', 'max:30'],
            'description' => ['string', 'max:255'],
        ];
    }
}
