<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class FailPaymentCallbackRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tran_id' => ['required', 'string'],
        ];
    }
}
