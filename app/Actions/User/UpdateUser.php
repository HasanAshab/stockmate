<?php

namespace App\Actions\User;

use App\Models\User;

class UpdateUser
{
    public function execute(User $user, array $data): User
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->forceFill([
                'email_verified_at' => now(),
            ]);
        }

        if ($user->isDirty('phone')) {
            $user->forceFill([
                'phone_verified_at' => now(),
            ]);
        }

        $user->save();

        return $user;
    }
}
