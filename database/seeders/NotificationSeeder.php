<?php

namespace Database\Seeders;

use App\Events\WarehouseStockLow;
use App\Models\WarehouseStock;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $products = WarehouseStock::lowStock()->limit(3)->get();

        foreach ($products as $product) {
            WarehouseStockLow::dispatch($product);
        }

        $this->command->info("Created notifications");
    }
}
