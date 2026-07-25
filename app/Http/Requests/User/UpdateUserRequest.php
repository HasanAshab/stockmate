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
            'email' => ['string', 'email', 'unique:users'],
            'phone' => ['nullable', 'phone:BD', 'unique:users'],
            'password' => ['string', Password::default()],
        ];
    }
}
