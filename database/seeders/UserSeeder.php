<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create super admin
        $superAdmin = User::factory()->superAdmin()->create([
            'email' => 'superadmin@example.com',
            'name' => 'Super Administrator',
        ]);
        $superAdmin->assignRole(Role::SuperAdmin->value);

        // Create administrators
        $admin1 = User::factory()->administrator()->create([
            'email' => 'admin@example.com',
            'name' => 'Administrator One',
        ]);
        $admin1->assignRole(Role::Administrator->value);

        $admin2 = User::factory()->administrator()->create([
            'email' => 'admin2@example.com',
            'name' => 'Administrator Two',
        ]);
        $admin2->assignRole(Role::Administrator->value);

        // Create store manager
        $storeManager = User::factory()->storeManager()->create([
            'email' => 'storemanager@example.com',
            'name' => 'Store Manager',
        ]);
        $storeManager->assignRole(Role::StoreManager->value);

        // Create warehouse manager
        $warehouseManager = User::factory()->warehouseManager()->create([
            'email' => 'warehousemanager@example.com',
            'name' => 'Warehouse Manager',
        ]);
        $warehouseManager->assignRole(Role::WarehouseManager->value);

        // Create warehouse staff (3 members)
        $warehouseStaff = collect(range(1, 3))->map(function ($i) {
            $user = User::factory()->warehouseStaff()->create([
                'email' => "warehousestaff{$i}@example.com",
                'name' => "Warehouse Staff {$i}",
            ]);
            $user->assignRole(Role::WarehouseStaff->value);

            return $user;
        });

        // Create purchasing officer
        $purchasingOfficer = User::factory()->purchasingOfficer()->create([
            'email' => 'purchasing@example.com',
            'name' => 'Purchasing Officer',
        ]);
        $purchasingOfficer->assignRole(Role::PurchasingOfficer->value);

        // Create sales representatives (2 members)
        $salesReps = collect(range(1, 2))->map(function ($i) {
            $user = User::factory()->salesRepresentative()->create([
                'email' => "sales{$i}@example.com",
                'name' => "Sales Representative {$i}",
            ]);
            $user->assignRole(Role::SalesRepresentative->value);

            return $user;
        });

        // Create cashiers (2 members)
        $cashiers = collect(range(1, 2))->map(function ($i) {
            $user = User::factory()->cashier()->create([
                'email' => "cashier{$i}@example.com",
                'name' => "Cashier {$i}",
            ]);
            $user->assignRole(Role::Cashier->value);

            return $user;
        });

        // Create inventory clerk
        $inventoryClerk = User::factory()->inventoryClerk()->create([
            'email' => 'inventory@example.com',
            'name' => 'Inventory Clerk',
        ]);
        $inventoryClerk->assignRole(Role::InventoryClerk->value);

        // Create accountant
        $accountant = User::factory()->accountant()->create([
            'email' => 'accountant@example.com',
            'name' => 'Accountant',
        ]);
        $accountant->assignRole(Role::Accountant->value);

        // Create auditor
        $auditor = User::factory()->auditor()->create([
            'email' => 'auditor@example.com',
            'name' => 'Auditor',
        ]);
        $auditor->assignRole(Role::Auditor->value);

        // Create viewer
        $viewer = User::factory()->viewer()->create([
            'email' => 'viewer@example.com',
            'name' => 'Viewer User',
        ]);
        $viewer->assignRole(Role::Viewer->value);

        $this->command->info('Created 18 users with various roles:');
        $this->command->info('  - 1 Super Administrator');
        $this->command->info('  - 2 Administrators');
        $this->command->info('  - 1 Store Manager');
        $this->command->info('  - 1 Warehouse Manager');
        $this->command->info('  - 3 Warehouse Staff');
        $this->command->info('  - 1 Purchasing Officer');
        $this->command->info('  - 2 Sales Representatives');
        $this->command->info('  - 2 Cashiers');
        $this->command->info('  - 1 Inventory Clerk');
        $this->command->info('  - 1 Accountant');
        $this->command->info('  - 1 Auditor');
        $this->command->info('  - 1 Viewer');
    }
}
