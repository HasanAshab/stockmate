<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'unique:users'],
            'phone' => ['nullable', 'phone:BD', 'unique:users'],
            'password' => ['required', 'string', Password::default()],
        ];
    }
}
