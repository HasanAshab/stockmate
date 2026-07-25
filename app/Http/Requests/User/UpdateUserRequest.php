<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'unique:users'],
            'phone' => ['nullable', 'string', 'phone:BD', 'unique:users'],
            'password' => ['string', Password::default()],
        ];
    }
}
