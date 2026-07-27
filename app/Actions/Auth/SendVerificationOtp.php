<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Notifications\AuthOtpNotification;

class SendVerificationOtp
{
    public function execute(string|User $identifierOrUser): void
    {
        [$user, $identifierType] = $this->resolveUser($identifierOrUser);

        if (! $user || $user->isVerified($identifierType)) {
            return;
        }

        $otp = $user->createOneTimePassword();

        $user->notify(new AuthOtpNotification($otp, $identifierType));
    }

    /**
     * @return array{0: User|null, 1: string|null}
     */
    private function resolveUser(string|User $identifierOrUser): array
    {
        if ($identifierOrUser instanceof User) {
            return [$identifierOrUser, null];
        }

        return [
            User::findByIdentifier($identifierOrUser),
            User::identifierColumn($identifierOrUser),
        ];
    }
}
