<?php

use App\Enums\Permission;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('List Products', function () {
    it('requires authentication', function () {
        $response = getJson('/api/v1/products');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires products-view permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/products');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns paginated list of products with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsView->value);

        Product::factory()->count(3)->create();

        $response = actingAs($user)->getJson('/api/v1/products');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('filters products by category', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsView->value);

        $category = Category::factory()->create();
        $matchingProduct = Product::factory()->create(['category_id' => $category->id]);
        $otherProduct = Product::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/products?filter[category]={$category->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('filters products by supplier', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsView->value);

        $supplier = Supplier::factory()->create();
        $matchingProduct = Product::factory()->create(['supplier_id' => $supplier->id]);

        $response = actingAs($user)->getJson("/api/v1/products?filter[supplier]={$supplier->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('sorts products by price', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsView->value);

        Product::factory()->create(['price' => 10.00]);
        Product::factory()->create(['price' => 50.00]);

        $response = actingAs($user)->getJson('/api/v1/products?sort=price');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('includes category relationship', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsView->value);

        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $response = actingAs($user)->getJson('/api/v1/products?include=category');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = getJson('/api/v1/products');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without products-view permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/products');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns product resource collection', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsView->value);

        Product::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/products');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
