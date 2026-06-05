<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default admin
        User::create([
            'role' => Role::Admin,
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => 'admin123',
            'is_active' => true,
        ]);
        $this->call(WarehouseSeeder::class);
    }
}
