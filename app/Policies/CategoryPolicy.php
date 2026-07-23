<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::CategoriesView);
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasPermissionTo(Permission::CategoriesView);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::CategoriesCreate);
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasPermissionTo(Permission::CategoriesUpdate);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasPermissionTo(Permission::CategoriesDelete);
    }

    public function restore(User $user, Category $category): bool
    {
        return $user->hasPermissionTo(Permission::CategoriesRestore);
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return $user->hasPermissionTo(Permission::CategoriesForceDelete);
    }
}
