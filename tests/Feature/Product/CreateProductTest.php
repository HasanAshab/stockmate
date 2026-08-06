<?php

use App\Enums\Permission;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;

use Illuminate\Http\UploadedFile;
use function Pest\Laravel\actingAs;

describe('Create Product', function () {
    it('requires authentication', function () {
        $response = $this->postJson('/api/v1/products', []);

        $response->assertValidResponse(401);
    });

    it('requires products-create permission', function () {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $image = UploadedFile::fake()->image('product.jpg');
        $payload = [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'name' => 'Gaming Mouse',
            'sku' => 'SKU-MOUSE-001',
            'price' => 49.99,
            'image' => $image,
        ];
        $response = actingAs($user)
            ->withHeaders([
                'Content-Type' => 'multipart/form-data',
            ])
            ->post('/api/v1/products', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(403);

        $this->assertDatabaseMissing('products', [
            'name' => 'Gaming Mouse',
            'sku' => 'SKU-MOUSE-001',
        ]);
    });

    it('creates a new product and returns 201', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsCreate);

        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $image = UploadedFile::fake()->image('product.jpg');
        $payload = [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'name' => 'Gaming Mouse',
            'sku' => 'SKU-MOUSE-001',
            'price' => 49.99,
            'image' => $image,
        ];

        $response = actingAs($user)
            ->withHeaders([
                'Content-Type' => 'multipart/form-data',
            ])
            ->post('/api/v1/products', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);

        $this->assertDatabaseHas('products', [
            'name' => 'Gaming Mouse',
            'sku' => 'SKU-MOUSE-001',
        ]);
    });

    it('returns validation errors for invalid input', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsCreate);

        $response = actingAs($user)
            ->withHeaders([
                'Content-Type' => 'multipart/form-data',
            ])
            ->post('/api/v1/products', ['foo' => 'bar']);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('rejects duplicate SKU', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsCreate);

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

        $response = actingAs($user)
            ->withHeaders([
                'Content-Type' => 'multipart/form-data',
            ])
            ->post('/api/v1/products', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);
        
        $this->assertDatabaseMissing('products', [
            'name' => 'Another Product',
        ]);
    });

    it('validates price is positive', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsCreate);

        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $payload = [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'name' => 'Invalid Price Product',
            'sku' => 'SKU-NEG-001',
            'price' => -10.00,
        ];

        $response = actingAs($user)
            ->withHeaders([
                'Content-Type' => 'multipart/form-data',
            ])    
            ->post('/api/v1/products', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);

        $this->assertDatabaseMissing('products', [
            'name' => 'Invalid Price Product',
            'sku' => 'SKU-NEG-001',
        ]);
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

        $response = actingAs($user)
            ->withHeaders([
                'Content-Type' => 'multipart/form-data',
            ])
            ->post('/api/v1/products', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);

        $this->assertDatabaseMissing('products', [
            'name' => 'Product Name',
            'sku' => 'SKU-NOCAT-001',
        ]);
    });

    it('validates supplier exists', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsCreate);

        $category = Category::factory()->create();

        $payload = [
            'category_id' => $category->id,
            'supplier_id' => 999999,
            'name' => 'Product Name',
            'sku' => 'SKU-NOSUP-001',
            'price' => 10.00,
        ];

        $response = actingAs($user)
            ->withHeaders([
                'Content-Type' => 'multipart/form-data',
            ])
            ->post('/api/v1/products', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);

        $this->assertDatabaseMissing('products', [
            'name' => 'Product Name',
            'sku' => 'SKU-NOSUP-001',
        ]);
    });

    it('validates max length for name field', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsCreate);

        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $payload = [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'name' => str_repeat('a', 256),
            'sku' => 'SKU-LONGNAME-001',
            'price' => 10.00,
        ];

        $response = actingAs($user)
            ->withHeaders([
                'Content-Type' => 'multipart/form-data',
            ])
            ->post('/api/v1/products', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
        
        $this->assertDatabaseMissing('products', [
            'sku' => 'SKU-LONGNAME-001',
        ]);
    });

    it('validates max length for SKU field', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::ProductsCreate);

        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $payload = [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'name' => 'Product Name',
            'sku' => str_repeat('a', 256),
            'price' => 10.00,
        ];

        $response = actingAs($user)
            ->withHeaders([
                'Content-Type' => 'multipart/form-data',
            ])
            ->post('/api/v1/products', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
        
        $this->assertDatabaseMissing('products', [
            'name' => 'Product Name',
        ]);
    });
});
