<?php

namespace App\Actions\Auth;

use App\Models\User;

class SendAuthOtp
{
    public function execute(string $identifier): void
    {
        $user = User::findByIdentifier($identifier);

        if (!$user || $user->isVerified()) {
            return;
        }

        $user->sendOneTimePassword();
    }
}
