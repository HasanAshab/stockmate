<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create 3 admins with specific emails
        $admins = collect([
            User::factory()->admin()->create([
                'email' => 'admin@example.com',
                'name' => 'Admin User One',
            ]),
            User::factory()->admin()->create([
                'email' => 'admin2@example.com',
                'name' => 'Admin User Two',
            ]),
            User::factory()->admin()->create([
                'email' => 'admin3@example.com',
                'name' => 'Admin User Three',
            ]),
        ]);

        // Create 1 demo staff member with specific email + 14 random staff
        $demoStaff = User::factory()->staff()->create([
            'email' => 'staff@example.com',
            'name' => 'Staff User One',
        ]);

        $staff = User::factory()->count(14)->staff()->create();

        // Store all users for use by other seeders
        $this->command->info('Created 3 admins and 15 staff members (1 demo + 14 random)');
    }
}
