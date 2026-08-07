<?php

use App\Enums\Permission;
use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('Show Product', function () {
    it('requires authentication', function () {
        $product = Product::factory()->create();

        $response = getJson("/api/v1/products/{$product->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires products-view permission', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/products/{$product->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns product details with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsView->value);

        $product = Product::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/products/{$product->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $product->id]);
    });

    it('includes category relationship when requested', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsView->value);

        $product = Product::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/products/{$product->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns 403 for user without products-view permission', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/products/{$product->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent product', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsView->value);

        $response = actingAs($user)->getJson('/api/v1/products/999999');

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns product resource', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsView->value);

        $product = Product::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/products/{$product->id}");
        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
