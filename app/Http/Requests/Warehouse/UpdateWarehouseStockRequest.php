<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWarehouseStockRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reorder_threshold' => ['required', 'integer:strict', 'min:0'],
        ];
    }
}
