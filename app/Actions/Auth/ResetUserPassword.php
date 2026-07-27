<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ResetUserPassword
{
    public function execute(array $credentials): void
    {
        $user = User::findByIdentifier($credentials['identifier']);

        if (! $user) {
            throw ValidationException::withMessages([
                'identifier' => ['We could not find an account with that email or phone.'],
            ]);
        }

        $result = $user->consumeOneTimePassword($credentials['code']);

        if (! $result->isOk()) {
            throw ValidationException::withMessages([
                'code' => [$result->validationMessage()],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($credentials['password']),
        ])->save();

        $user->tokens()->delete();
    }
}
