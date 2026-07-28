<?php

namespace Database\Factories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'type' => 'App\Notifications\ProductLowStockNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => 1,
            'data' => [
                'message' => $this->faker->sentence(),
                'product_name' => $this->faker->word(),
            ],
            'read_at' => null,
        ];
    }
}
