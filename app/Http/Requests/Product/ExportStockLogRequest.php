<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ExportStockLogRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'from' => ['date', 'before_or_equal:to'],
            'to' => ['date', 'after_or_equal:from'],
        ];
    }
}
