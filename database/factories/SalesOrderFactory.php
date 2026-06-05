<?php

namespace Database\Factories;

use App\Enums\SalesOrderStatus;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesOrder>
 */
class SalesOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'warehouse_id' => Warehouse::factory(),
            'created_by' => User::factory(),
            'status' => fake()->randomElement(SalesOrderStatus::cases()),
            'total_amount' => fake()->randomFloat(2, 50, 5000),
            'transaction_id' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SalesOrderStatus::Pending,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SalesOrderStatus::Processing,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SalesOrderStatus::Completed,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SalesOrderStatus::Cancelled,
        ]);
    }
}
