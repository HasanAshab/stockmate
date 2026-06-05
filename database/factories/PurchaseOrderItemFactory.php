<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrderItem>
 */
class PurchaseOrderItemFactory extends Factory
{
    public function definition(): array
    {
        $orderedQuantity = fake()->numberBetween(10, 200);

        return [
            'purchase_order_id' => PurchaseOrder::factory(),
            'product_id' => Product::factory(),
            'ordered_quantity' => $orderedQuantity,
            'received_quantity' => 0,
            'unit_cost' => fake()->randomFloat(2, 5, 500),
        ];
    }

    public function fullyReceived(): static
    {
        return $this->state(fn (array $attributes) => [
            'received_quantity' => $attributes['ordered_quantity'],
        ]);
    }

    public function partiallyReceived(): static
    {
        return $this->state(fn (array $attributes) => [
            'received_quantity' => fake()->numberBetween(1, $attributes['ordered_quantity'] - 1),
        ]);
    }
}
