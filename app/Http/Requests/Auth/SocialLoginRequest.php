<?php

namespace App\Http\Requests\Auth;

use App\Enums\SocialProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SocialLoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', Rule::enum(SocialProvider::class)],
            'token' => ['required', 'string'],
        ];
    }

    public function provider(): SocialProvider
    {
        return SocialProvider::from($this->input('provider'));
    }
}
