<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use App\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::RolesView);
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermissionTo(Permission::RolesView);
    }
}
