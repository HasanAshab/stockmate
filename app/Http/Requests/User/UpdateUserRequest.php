<?php

namespace App\Http\Requests\User;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['string', 'max:50'],
            'email' => ['string', 'email', 'unique:users'],
            'password' => ['string', Password::default()],
            'role' => [Rule::enum(Role::class)],
        ];
    }
}
