<?php

namespace App\Actions\User;

use App\Models\User;

class UpdateProfile
{
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

        return $user;
    }
}
