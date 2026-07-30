<?php

use App\Enums\Permission;
use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;

describe('Delete Product', function () {
    it('requires authentication', function () {
        $product = Product::factory()->create();

        $response = deleteJson("/api/v1/products/{$product->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires products-delete permission', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = actingAs($user)->deleteJson("/api/v1/products/{$product->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('soft deletes product and returns 204', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsDelete->value);

        $product = Product::factory()->create();

        $response = actingAs($user)->deleteJson("/api/v1/products/{$product->id}");

        $response->assertValidRequest()
            ->assertValidResponse(204);

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    });

    it('returns 401 for unauthenticated request', function () {
        $product = Product::factory()->create();

        $response = deleteJson("/api/v1/products/{$product->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without products-delete permission', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = actingAs($user)->deleteJson("/api/v1/products/{$product->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent product', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsDelete->value);

        $response = actingAs($user)->deleteJson('/api/v1/products/999999');

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });
});
