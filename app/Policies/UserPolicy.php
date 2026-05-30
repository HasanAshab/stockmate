<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function updateRole(User $authUser, User $targetUser): bool
    {
        return $authUser->id !== $targetUser->id;
    }

    public function deactivate(User $authUser, User $targetUser): bool
    {
        return $authUser->id !== $targetUser->id;
    }
}
