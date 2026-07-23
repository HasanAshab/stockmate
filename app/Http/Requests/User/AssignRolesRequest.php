<?php

namespace App\Http\Requests\User;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class AssignRolesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'roles' => ['required', 'array'],
            'roles.*' => ['distinct', new Enum(Role::class)],
        ];
    }
}
