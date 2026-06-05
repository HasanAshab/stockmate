<?php

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockAlert;

test('user can list all notifications', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $user->notify(new LowStockAlert($product));

    $this->actingAs($user)
        ->getJson(route('notifications.index'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'type',
                    'data',
                    'read_at',
                    'created_at',
                ],
            ],
        ]);
});

test('user can list unread notifications', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $user->notify(new LowStockAlert($product));

    $this->actingAs($user)
        ->getJson(route('notifications.unread'))
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('user can mark notification as read', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $user->notify(new LowStockAlert($product));
    $notification = $user->unreadNotifications->first();

    $this->actingAs($user)
        ->patchJson(route('notifications.mark-as-read', $notification->id))
        ->assertOk();

    expect($user->fresh()->unreadNotifications)->toHaveCount(0);
});

test('user can mark all notifications as read', function () {
    $user = User::factory()->create();
    $products = Product::factory()->count(3)->create();

    foreach ($products as $product) {
        $user->notify(new LowStockAlert($product));
    }

    expect($user->unreadNotifications)->toHaveCount(3);

    $this->actingAs($user)
        ->patchJson(route('notifications.mark-all-as-read'))
        ->assertOk()
        ->assertJson([
            'message' => 'All notifications marked as read.',
        ]);

    expect($user->fresh()->unreadNotifications)->toHaveCount(0);
});

test('user can delete a notification', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $user->notify(new LowStockAlert($product));
    $notification = $user->notifications->first();

    expect($user->notifications)->toHaveCount(1);

    $this->actingAs($user)
        ->deleteJson(route('notifications.destroy', $notification->id))
        ->assertNoContent();

    expect($user->fresh()->notifications)->toHaveCount(0);
});

test('user cannot access another users notification', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $product = Product::factory()->create();

    $user1->notify(new LowStockAlert($product));
    $notification = $user1->notifications->first();

    $this->actingAs($user2)
        ->patchJson(route('notifications.mark-as-read', $notification->id))
        ->assertNotFound();
});

test('unauthenticated users cannot access notifications', function () {
    $this->getJson(route('notifications.index'))
        ->assertUnauthorized();
});
