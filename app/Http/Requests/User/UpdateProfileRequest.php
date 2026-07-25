<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function rules(): array
    {
        $user = $this->user();

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

    public function after(): array
    {
        return [
            function ($validator) {
                $user = $this->user();

                $email = $this->input('email', $user->email);
                $phone = $this->input('phone', $user->phone);

                if ($email === null && $phone === null) {
                    $validator->errors()->add(
                        'email',
                        'Either an email or a phone number is required.'
                    );
                }
            },
        ];
    }
}
