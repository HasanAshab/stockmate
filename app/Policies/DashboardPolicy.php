<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

class DashboardPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::DashboardView);
    }
}
