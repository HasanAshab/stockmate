<?php

namespace Database\Seeders;

use App\Enums\Role;
use Illuminate\Database\Seeder;
use App\Models\Role as RoleModel;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Role::cases() as $roleEnum) {
            $role = RoleModel::findOrCreate($roleEnum);

            $role->syncPermissions($roleEnum->permissions());
        }
    }
}
