<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['string', 'max:50'],
            'email' => [
                'nullable',
                'string',
                'email',
                Rule::unique('users')->ignore($user),
            ],
            'phone' => [
                'nullable',
                'string',
                'phone:BD',
                Rule::unique('users')->ignore($user),
            ],
            'password' => ['string', Password::default()],
        ];
    }
}