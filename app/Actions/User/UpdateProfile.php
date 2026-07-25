<?php

namespace App\Actions\User;

use App\Actions\Auth\SendVerificationOtp;
use App\Models\User;

class UpdateProfile
{
    public function __construct(protected SendVerificationOtp $verification) {}

    public function execute(User $user, array $data): User
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->forceFill([
                'email_verified_at' => null,
            ]);
        }

        if ($user->isDirty('phone')) {
            $user->forceFill([
                'phone_verified_at' => null,
            ]);
        }

        $user->save();

        if ($user->wasChanged('email')) {
            $this->verification->execute($user->email);
        }

        if ($user->wasChanged('phone')) {
            $this->verification->execute($user->phone);
        }

        return $user;
    }
}
