<?php

use App\Enums\Permission;
use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\actingAs;

describe('Update Product', function () {
    it('requires authentication', function () {
        $product = Product::factory()->create();

        $response = $this->withHeaders([
            'Content-Type' => 'multipart/form-data',
            'Accept' => 'application/json',
        ])->put("/api/v1/products/{$product->id}", [
            'name' => 'Test',
        ]);

        $response->assertValidResponse(401);
    });

    it('requires products-update permission', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = actingAs($user)
            ->withHeaders([
                'Content-Type' => 'multipart/form-data',
            ])->put("/api/v1/products/{$product->id}", [
                'name' => 'Test',
            ]);

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

        $response = actingAs($user)
            ->withHeaders([
                'Content-Type' => 'multipart/form-data',
            ])->put("/api/v1/products/{$product->id}", $payload);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['name' => 'New Product Name']);

        $product->refresh();

        expect($product->name)->toBe('New Product Name')
            ->and((float) $product->price)->toBe(29.99);
    });

    it('returns validation errors for invalid input', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsUpdate->value);

        $product = Product::factory()->create();

        $response = actingAs($user)
            ->withHeaders([
                'Content-Type' => 'multipart/form-data',
            ])->put("/api/v1/products/{$product->id}", [
                'price' => -5.00,
            ]);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('rejects duplicate SKU', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsUpdate->value);

        Product::factory()->create(['sku' => 'TAKEN-SKU']);
        $product = Product::factory()->create(['sku' => 'MY-SKU']);

        $response = actingAs($user)
            ->withHeaders([
                'Content-Type' => 'multipart/form-data',
            ])->put("/api/v1/products/{$product->id}", [
                'sku' => 'TAKEN-SKU',
            ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('validates price is positive', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsUpdate->value);

        $product = Product::factory()->create();

        $response = actingAs($user)
            ->withHeaders([
                'Content-Type' => 'multipart/form-data',
            ])->put("/api/v1/products/{$product->id}", [
                'price' => -100.00,
            ]);

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

        $response = actingAs($user)
            ->withHeaders([
                'Content-Type' => 'multipart/form-data',
            ])->put("/api/v1/products/{$product->id}", [
                'name' => 'Partially Updated Name',
            ]);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['name' => 'Partially Updated Name']);

        $product->refresh();

        expect($product->name)->toBe('Partially Updated Name')
            ->and((float) $product->price)->toBe(50.00);
    });

    it('validates category exists', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsUpdate->value);

        $product = Product::factory()->create();

        $response = actingAs($user)
            ->withHeaders([
                'Content-Type' => 'multipart/form-data',
            ])->put("/api/v1/products/{$product->id}", [
                'category_id' => 999999,
            ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('returns 404 for non-existent product', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsUpdate->value);

        $response = actingAs($user)
            ->withHeaders([
                'Content-Type' => 'multipart/form-data',
            ])->put('/api/v1/products/999999', [
                'name' => 'Test',
            ]);

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns updated product resource', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsUpdate->value);

        $product = Product::factory()->create();

        $response = actingAs($user)
            ->withHeaders([
                'Content-Type' => 'multipart/form-data',
            ])->put("/api/v1/products/{$product->id}", [
                'name' => 'Updated Product',
            ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
