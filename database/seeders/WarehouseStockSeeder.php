<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Database\Seeder;

class WarehouseStockSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $warehouses = Warehouse::all();

        $stockCount = 0;

        // Create warehouse stock for products
        foreach ($products as $product) {
            $warehousesForProduct = $warehouses->random(rand(1, 3));

            foreach ($warehousesForProduct as $warehouse) {
                WarehouseStock::factory()->create([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                ]);
                $stockCount++;
            }
        }

        // Create some low-stock items
        $existingStockIds = WarehouseStock::pluck('product_id')->toArray();
        $availableProducts = $products->whereNotIn('id', $existingStockIds);

        $lowStockCount = 5;
        WarehouseStock::factory()
            ->count($lowStockCount)
            ->low()
            ->recycle([$warehouses, Product::factory()->count($lowStockCount)->create()])
            ->create();

        $this->command->info("Created {$stockCount} warehouse stock records ({$lowStockCount} low stock)");
    }
}
