<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use Spatie\Permission\Models\Permission as SpatiePermission;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::PermissionsView);
    }

    public function view(User $user, SpatiePermission $permission): bool
    {
        return $user->hasPermissionTo(Permission::PermissionsView);
    }
}
