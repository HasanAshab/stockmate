<?php

namespace Database\Seeders;

use App\Enums\StockLogType;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class StockLogSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $users = User::all();
        $warehouses = Warehouse::all();

        // Create various stock log entries
        $types = [
            StockLogType::In,
            StockLogType::Out,
        ];

        $logCount = 0;

        foreach ($products->random(30) as $product) {
            $logsForProduct = rand(2, 5);

            for ($i = 0; $i < $logsForProduct; $i++) {
                StockLog::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouses->random()->id,
                    'user_id' => $users->random()->id,
                    'type' => fake()->randomElement($types),
                    'quantity' => fake()->numberBetween(1, 100),
                    'unit_cost' => fake()->randomFloat(2, 10, 500),
                    'note' => fake()->optional(0.3)->sentence(),
                    'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
                ]);
                $logCount++;
            }
        }

        $this->command->info("Created {$logCount} stock log entries");
    }
}
