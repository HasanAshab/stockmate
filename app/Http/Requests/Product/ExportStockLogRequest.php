<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ExportStockLogRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'from' => ['date_format:Y-m-d'],
            'to' => ['date_format:Y-m-d', 'after_or_equal:from'],
        ];
    }
}
