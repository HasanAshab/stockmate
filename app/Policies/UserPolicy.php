<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

class UserPolicy
{
    public function assignRoles(User $authUser, User $targetUser): bool
    {
        return $authUser->hasPermissionTo(Permission::RolesManage);
    }

    public function assignPermissions(User $authUser, User $targetUser): bool
    {
        return $authUser->hasPermissionTo(Permission::PermissionsManage);
    }

    public function deactivate(User $authUser, User $targetUser): bool
    {
        if ($authUser->id === $targetUser->id) {
            return false;
        }

        return $authUser->hasPermissionTo(Permission::UsersDeactivate);
    }
}
