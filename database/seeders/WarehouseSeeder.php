<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = Warehouse::factory()->count(3)->create([
            ['name' => 'Main Warehouse', 'location' => 'Downtown District'],
            ['name' => 'North Warehouse', 'location' => 'North Industrial Zone'],
            ['name' => 'South Warehouse', 'location' => 'South Business Park'],
        ]);

        $products = Product::all();

        foreach ($warehouses as $warehouse) {
            foreach ($products->random(min(10, $products->count())) as $product) {
                WarehouseStock::factory()->create([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                ]);
            }
        }
    }
}
