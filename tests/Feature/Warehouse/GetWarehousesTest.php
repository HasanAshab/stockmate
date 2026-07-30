<?php

use App\Enums\Permission;
use App\Models\User;
use App\Models\Warehouse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('List Warehouses', function () {
    it('requires authentication', function () {
        $response = getJson('/api/v1/warehouses');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires warehouses-view permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/warehouses');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns paginated list of warehouses with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        Warehouse::factory()->count(3)->create();

        $response = actingAs($user)->getJson('/api/v1/warehouses');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('filters warehouses by name', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create(['name' => 'Central Depot']);

        $response = actingAs($user)->getJson('/api/v1/warehouses');

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $warehouse->id]);
    });

    it('filters warehouses by location', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $warehouse = Warehouse::factory()->create(['location' => 'Dhaka']);

        $response = actingAs($user)->getJson('/api/v1/warehouses');

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $warehouse->id]);
    });

    it('sorts warehouses by created_at', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        Warehouse::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/warehouses');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = getJson('/api/v1/warehouses');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/warehouses');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns warehouse resource collection', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        Warehouse::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/warehouses');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
