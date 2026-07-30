<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;

class SendPasswordResetOtp
{
    public function execute(string $identifier): void
    {
        $user = User::findByIdentifier($identifier);

        if (! $user) {
            // Don't leak information about existing users
            sleep(1);
            return;
        }

        $otp = $user->createOneTimePassword();
        $identifierType = User::identifierColumn($identifier);

        $user->notify(new PasswordResetOtpNotification($otp, $identifierType));
    }
}
