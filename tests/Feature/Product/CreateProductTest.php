<?php

use App\Enums\Permission;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('Create Product', function () {
    it('requires authentication', function () {
        $response = postJson('/api/v1/products', []);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires products-create permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->postJson('/api/v1/products', []);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('creates a new product and returns 201', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsCreate->value);

        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $payload = [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'name' => 'Gaming Mouse',
            'sku' => 'SKU-MOUSE-001',
            'price' => 49.99,
        ];

        $response = actingAs($user)->postJson('/api/v1/products', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201)
            ->assertJsonFragment([
                'name' => 'Gaming Mouse',
                'sku' => 'SKU-MOUSE-001',
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Gaming Mouse',
            'sku' => 'SKU-MOUSE-001',
        ]);
    });

    it('returns validation errors for invalid input', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsCreate->value);

        $response = actingAs($user)->postJson('/api/v1/products', []);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('rejects duplicate SKU', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsCreate->value);

        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        Product::factory()->create(['sku' => 'DUPLICATE-SKU']);

        $payload = [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'name' => 'Another Product',
            'sku' => 'DUPLICATE-SKU',
            'price' => 19.99,
        ];

        $response = actingAs($user)->postJson('/api/v1/products', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates price is positive', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsCreate->value);

        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $payload = [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'name' => 'Invalid Price Product',
            'sku' => 'SKU-NEG-001',
            'price' => -10.00,
        ];

        $response = actingAs($user)->postJson('/api/v1/products', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates category exists', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsCreate->value);

        $supplier = Supplier::factory()->create();

        $payload = [
            'category_id' => 999999,
            'supplier_id' => $supplier->id,
            'name' => 'Product Name',
            'sku' => 'SKU-NOCAT-001',
            'price' => 10.00,
        ];

        $response = actingAs($user)->postJson('/api/v1/products', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates supplier exists', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsCreate->value);

        $category = Category::factory()->create();

        $payload = [
            'category_id' => $category->id,
            'supplier_id' => 999999,
            'name' => 'Product Name',
            'sku' => 'SKU-NOSUP-001',
            'price' => 10.00,
        ];

        $response = actingAs($user)->postJson('/api/v1/products', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates max length for name field', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsCreate->value);

        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $payload = [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'name' => str_repeat('a', 256),
            'sku' => 'SKU-LONGNAME-001',
            'price' => 10.00,
        ];

        $response = actingAs($user)->postJson('/api/v1/products', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates max length for SKU field', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsCreate->value);

        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $payload = [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'name' => 'Product Name',
            'sku' => str_repeat('a', 256),
            'price' => 10.00,
        ];

        $response = actingAs($user)->postJson('/api/v1/products', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = postJson('/api/v1/products', [
            'name' => 'Test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without products-create permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->postJson('/api/v1/products', [
            'name' => 'Test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns product resource on success', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsCreate->value);

        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $payload = [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'name' => 'Mechanical Keyboard',
            'sku' => 'SKU-KB-001',
            'price' => 99.99,
        ];

        $response = actingAs($user)->postJson('/api/v1/products', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);
    });
});
