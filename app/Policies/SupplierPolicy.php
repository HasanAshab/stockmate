<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->role->isAdmin() || $user->role->isStaff()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role->isAdmin();
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $user->role->isAdmin();
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->role->isAdmin();
    }

    public function restore(User $user, Supplier $supplier): bool
    {
        return $user->role->isAdmin();
    }

    public function forceDelete(User $user, Supplier $supplier): bool
    {
        return $user->role->isAdmin();
    }
}
