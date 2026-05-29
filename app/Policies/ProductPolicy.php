<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->roleIs(Role::Admin, Role::Staff);
    }

    public function view(User $user, Product $product): bool
    {
        return $user->roleIs(Role::Admin, Role::Staff);
    }

    public function create(User $user): bool
    {
        return $user->roleIs(Role::Admin);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->roleIs(Role::Admin);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->roleIs(Role::Admin);
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->roleIs(Role::Admin);
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return $user->roleIs(Role::Admin);
    }
}
