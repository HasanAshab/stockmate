<?php

namespace Database\Factories;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'warehouse_id' => Warehouse::factory(),
            'created_by' => User::factory(),
            'status' => fake()->randomElement(PurchaseOrderStatus::cases()),
            'note' => fake()->optional(default: '')->sentence(),
            'ordered_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'received_at' => fake()->optional()->dateTimeBetween('-2 months', 'now'),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PurchaseOrderStatus::Draft,
            'ordered_at' => null,
            'received_at' => null,
        ]);
    }

    public function ordered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PurchaseOrderStatus::Ordered,
            'ordered_at' => fake()->dateTimeBetween('-2 months', '-1 week'),
            'received_at' => null,
        ]);
    }

    public function received(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PurchaseOrderStatus::Received,
            'ordered_at' => fake()->dateTimeBetween('-3 months', '-1 month'),
            'received_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}
