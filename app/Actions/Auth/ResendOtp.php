<?php

namespace App\Actions\Auth;

use App\Models\User;

class ResendOtp
{
    public function execute(string $identifier): void
    {
        $user = User::where('email', $identifier)->first();

        if (!$user || $user->isVerified()) {
            return;
        }

        $user->sendOneTimePassword();
    }
}
