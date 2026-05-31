<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->roleIs(Role::Admin, Role::Staff)) {
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
        return $user->roleIs(Role::Admin);
    }

    public function update(User $user, Category $category): bool
    {
        return $user->roleIs(Role::Admin);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->roleIs(Role::Admin);
    }

    public function restore(User $user, Category $category): bool
    {
        return $user->roleIs(Role::Admin);
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return $user->role(Role::Admin);
    }
}
