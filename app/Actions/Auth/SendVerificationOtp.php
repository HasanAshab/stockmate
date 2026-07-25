<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Notifications\AuthOtpNotification;

class SendVerificationOtp
{
    public function execute(string $identifier): void
    {
        $user = User::findByIdentifier($identifier);
        $identifierType = User::identifierColumn($identifier);

        if (!$user || $user->isVerified($identifierType)) {
            return;
        }

        $otp = $user->createOneTimePassword();
        $user->notify(new AuthOtpNotification($otp, $identifierType));
    }
}
