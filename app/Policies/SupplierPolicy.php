<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SuppliersView);
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $user->hasPermissionTo(Permission::SuppliersView);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SuppliersCreate);
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $user->hasPermissionTo(Permission::SuppliersUpdate);
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->hasPermissionTo(Permission::SuppliersDelete);
    }

    public function restore(User $user, Supplier $supplier): bool
    {
        return $user->hasPermissionTo(Permission::SuppliersRestore);
    }

    public function forceDelete(User $user, Supplier $supplier): bool
    {
        return $user->hasPermissionTo(Permission::SuppliersForceDelete);
    }
}
