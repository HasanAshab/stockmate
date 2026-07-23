<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ProductsView);
    }

    public function view(User $user, Product $product): bool
    {
        return $user->hasPermissionTo(Permission::ProductsView);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ProductsCreate);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasPermissionTo(Permission::ProductsUpdate);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasPermissionTo(Permission::ProductsDelete);
    }
}
