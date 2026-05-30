<?php

namespace App\Http\Requests;

use App\Enums\StockLogType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockLogRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', Rule::enum(StockLogType::class)],
            'quantity' => ['required', 'integer:strict', 'min:1'],
            'unit_cost' => ['nullable', 'numeric:strict', 'min:1'],
            'note' => ['nullable', 'string'],
        ];
    }
}
