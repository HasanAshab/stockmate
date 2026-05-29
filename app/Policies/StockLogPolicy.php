<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\StockLog;
use App\Models\User;

class StockLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->roleIs(Role::Admin, Role::Staff);
    }

    public function view(User $user, StockLog $stockLog): bool
    {
        return $user->roleIs(Role::Admin, Role::Staff);
    }

    public function create(User $user): bool
    {
        return $user->roleIs(Role::Admin, Role::Staff);
    }

    public function update(User $user, StockLog $stockLog): bool
    {
        return false;
    }

    public function delete(User $user, StockLog $stockLog): bool
    {
        return false;
    }

    public function restore(User $user, StockLog $stockLog): bool
    {
        return false;
    }

    public function forceDelete(User $user, StockLog $stockLog): bool
    {
        return false;
    }
}
