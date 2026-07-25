<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Notifications\AuthOtpNotification;

class SendAuthOtp
{
    public function execute(string $identifier): void
    {
        $user = User::findByIdentifier($identifier);

        if (!$user || $user->isVerified()) {
            return;
        }

        $otp = $user->createOneTimePassword();
        $identifierType = User::identifierColumn($identifier);
        $user->notify(new AuthOtpNotification($otp, $identifierType));
    }
}
