<?php

use App\Enums\Permission;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\putJson;

describe('Update Product', function () {
    it('requires authentication', function () {
        $product = Product::factory()->create();

        $response = putJson("/api/v1/products/{$product->id}", []);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires products-update permission', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = actingAs($user)->putJson("/api/v1/products/{$product->id}", []);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('updates product and returns 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsUpdate->value);

        $product = Product::factory()->create([
            'name' => 'Old Product Name',
            'price' => 20.00,
        ]);

        $payload = [
            'name' => 'New Product Name',
            'price' => 29.99,
        ];

        $response = actingAs($user)->putJson("/api/v1/products/{$product->id}", $payload);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment([
                'name' => 'New Product Name',
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'New Product Name',
        ]);
    });

    it('returns validation errors for invalid input', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsUpdate->value);

        $product = Product::factory()->create();

        $payload = [
            'price' => -5.00,
        ];

        $response = actingAs($user)->putJson("/api/v1/products/{$product->id}", $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('rejects duplicate SKU', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsUpdate->value);

        Product::factory()->create(['sku' => 'TAKEN-SKU']);
        $productToUpdate = Product::factory()->create(['sku' => 'MY-SKU']);

        $payload = [
            'sku' => 'TAKEN-SKU',
        ];

        $response = actingAs($user)->putJson("/api/v1/products/{$productToUpdate->id}", $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates price is positive', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsUpdate->value);

        $product = Product::factory()->create();

        $payload = [
            'price' => -100.00,
        ];

        $response = actingAs($user)->putJson("/api/v1/products/{$product->id}", $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('allows partial updates', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsUpdate->value);

        $product = Product::factory()->create([
            'name' => 'Original Name',
            'price' => 50.00,
        ]);

        $payload = [
            'name' => 'Partially Updated Name',
        ];

        $response = actingAs($user)->putJson("/api/v1/products/{$product->id}", $payload);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment([
                'name' => 'Partially Updated Name',
            ]);
    });

    it('validates category exists', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsUpdate->value);

        $product = Product::factory()->create();

        $payload = [
            'category_id' => 999999,
        ];

        $response = actingAs($user)->putJson("/api/v1/products/{$product->id}", $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('returns 401 for unauthenticated request', function () {
        $product = Product::factory()->create();

        $response = putJson("/api/v1/products/{$product->id}", [
            'name' => 'Test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without products-update permission', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = actingAs($user)->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent product', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsUpdate->value);

        $response = actingAs($user)->putJson('/api/v1/products/999999', [
            'name' => 'Test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns updated product resource', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsUpdate->value);

        $product = Product::factory()->create();

        $response = actingAs($user)->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Updated Product',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
