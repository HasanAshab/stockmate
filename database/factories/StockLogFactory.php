<?php

namespace Database\Factories;

use App\Enums\StockLogType;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockLogFactory extends Factory
{
    protected $model = StockLog::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'warehouse_id' => Warehouse::factory(),
            'user_id' => User::factory(),
            'type' => StockLogType::In,
            'quantity' => fake()->numberBetween(1, 500),
            'unit_cost' => fake()->optional()->randomFloat(2, 1, 5000),
            'note' => fake()->optional()->sentence(),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}