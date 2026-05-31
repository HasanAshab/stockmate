<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use App\Models\Warehouse;

class WarehousePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->roleIs(Role::Admin, Role::Staff);
    }

    public function view(User $user, Warehouse $warehouse): bool
    {
        return $user->roleIs(Role::Admin, Role::Staff);
    }

    public function create(User $user): bool
    {
        return $user->roleIs(Role::Admin);
    }

    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->roleIs(Role::Admin);
    }

    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $user->roleIs(Role::Admin);
    }
}
