<?php

namespace Database\Seeders;

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
        $this->call([
            // Roles and Permissions
            PermissionSeeder::class,
            RoleSeeder::class,

            // Foundation data
            UserSeeder::class,
            CategorySeeder::class,
            SupplierSeeder::class,
            WarehouseSeeder::class,

            // Products
            ProductSeeder::class,

            // Warehouse stock
            WarehouseStockSeeder::class,

            // Orders
            PurchaseOrderSeeder::class,
            SalesOrderSeeder::class,

            // Activity tracking
            StockLogSeeder::class,
            NotificationSeeder::class,
        ]);

        $this->command->info('✅ Database seeding completed successfully!');
    }
}
