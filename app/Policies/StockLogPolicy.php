<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\StockLog;
use App\Models\User;

class StockLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::StockLogsView);
    }

    public function view(User $user, StockLog $stockLog): bool
    {
        return $user->hasPermissionTo(Permission::StockLogsView);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::StockLogsCreate);
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
