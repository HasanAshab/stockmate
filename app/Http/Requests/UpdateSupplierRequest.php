<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['string', 'max:70'],
            'slug' => ['string', 'max:70', 'unique:suppliers'],
            'contact_name' => ['string', 'max:70'],
            'email' => ['email', 'max:100'],
            'phone' => ['string', 'max:30'],
            'description' => ['max:255'],
        ];
    }
}
