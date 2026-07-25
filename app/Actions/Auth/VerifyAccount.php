<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Spatie\OneTimePasswords\Enums\ConsumeOneTimePasswordResult;

class VerifyAccount
{
    public function execute(string $identifier, string $code): void
    {
        $user = User::findByIdentifier($identifier);

        $result = is_null($user)
            ? ConsumeOneTimePasswordResult::IncorrectOneTimePassword
            : $user->consumeOneTimePassword($code);

        if (! $result->isOk())
        {
            throw ValidationException::withMessages([
                'code' => [$result->validationMessage()],
            ]);
        }
        
        match (User::identifierColumn($identifier)) {
            'email' => $user->markEmailAsVerified(),
            'phone' => $user->markPhoneAsVerified(),
        };
    }
}
