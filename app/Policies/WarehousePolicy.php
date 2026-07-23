<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use App\Models\Warehouse;

class WarehousePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::WarehousesView);
    }

    public function view(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermissionTo(Permission::WarehousesView);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::WarehousesCreate);
    }

    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermissionTo(Permission::WarehousesUpdate);
    }

    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermissionTo(Permission::WarehousesDelete);
    }
}
