<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use Illuminate\Validation\ValidationException;

class SendPasswordResetOtp
{
    public function execute(string $identifier): void
    {
        $user = User::findByIdentifier($identifier);

        if (!$user) {
            throw ValidationException::withMessages([
                'identifier' => ['We could not find an account with that email or phone.'],
            ]);
        }

        $otp = $user->createOneTimePassword();
        $user->notify(new PasswordResetOtpNotification($otp));
    }
}
