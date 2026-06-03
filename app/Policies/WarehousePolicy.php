<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;

class WarehousePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->isAdmin() || $user->role->isStaff();
    }

    public function view(User $user, Warehouse $warehouse): bool
    {
        return $user->role->isAdmin() || $user->role->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->role->isAdmin();
    }

    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->role->isAdmin();
    }

    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $user->role->isAdmin();
    }
}
