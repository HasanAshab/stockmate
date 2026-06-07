<?php

namespace Database\Seeders;

use App\Events\ProductStockLow;
use App\Models\Product;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::lowStock()->limit(3)->get();

        foreach ($products as $product) {
            ProductStockLow::dispatch($product);
        }

        $this->command->info("Created notifications");
    }
}
