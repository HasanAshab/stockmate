<?php

use App\Enums\Permission;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('Scoped Search', function () {
    it('requires authentication', function () {
        $response = getJson('/api/v1/search/products?q=laptop');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires permission for the specific scope', function () {
        $user = User::factory()->create();
        // No permissions granted

        $response = actingAs($user)->getJson('/api/v1/search/products?q=laptop');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('searches products scope with authorization', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsView->value);

        $response = actingAs($user)->getJson('/api/v1/search/products?q=laptop');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('searches warehouses scope with authorization', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::WarehousesView->value);

        $response = actingAs($user)->getJson('/api/v1/search/warehouses?q=laptop');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('searches purchase_orders scope with authorization', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersView->value);

        $response = actingAs($user)->getJson('/api/v1/search/purchase_orders?q=test');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('searches sales_orders scope with authorization', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersView->value);

        $response = actingAs($user)->getJson('/api/v1/search/sales_orders?q=test');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('searches stock_logs scope with authorization', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::StockLogsView->value);

        $response = actingAs($user)->getJson('/api/v1/search/stock_logs?q=test');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('enforces minimum length of 2 characters for scoped search', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsView->value);

        // 1 character should return empty
        $response = actingAs($user)->getJson('/api/v1/search/products?q=a');

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect($response->json('data'))->toBeEmpty();

        // 2 characters should be valid
        $response = actingAs($user)->getJson('/api/v1/search/products?q=ab');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns 404 for invalid scope', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/search/invalid_scope?q=test');

        $response->assertStatus(404);
    });

    it('returns empty data when query term is too short', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsView->value);

        $response = actingAs($user)->getJson('/api/v1/search/products?q=a');

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect($response->json('data'))->toBeEmpty();
    });

    it('trims whitespace from search term', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsView->value);

        $response = actingAs($user)->getJson('/api/v1/search/products', ['q' => '   laptop   ']);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('handles missing query parameter', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsView->value);

        $response = actingAs($user)->getJson('/api/v1/search/products');

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect($response->json('data'))->toBeEmpty();
    });

    it('handles empty string query', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsView->value);

        $response = actingAs($user)->getJson('/api/v1/search/products?q=');

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect($response->json('data'))->toBeEmpty();
    });

    it('handles only whitespace query', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsView->value);

        $response = actingAs($user)->getJson('/api/v1/search/products', ['q' => '   ']);

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect($response->json('data'))->toBeEmpty();
    });

    it('does not allow access to scope without permission', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsView->value);

        // Has products permission but tries to search warehouses
        $response = actingAs($user)->getJson('/api/v1/search/warehouses?q=test');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('case sensitive scope validation', function () {
        $user = User::factory()->create();

        // Test with uppercase - should 404 because route doesn't match
        $response = actingAs($user)->getJson('/api/v1/search/PRODUCTS?q=test');

        $response->assertStatus(404);
    });
});
