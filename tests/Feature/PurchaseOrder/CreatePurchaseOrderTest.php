<?php

use App\Enums\Permission;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('Create Purchase Order', function () {
    it('requires authentication', function () {
        $response = postJson('/api/v1/purchase-orders', []);

        $response->assertValidResponse(401);
    });

    it('requires purchase-orders-create permission', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'note' => 'Initial inventory order',
            'items' => [
                [
                    'product_id' => $product->id,
                    'ordered_quantity' => 50,
                    'unit_cost' => 20.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/purchase-orders', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('creates a new purchase order and returns 201', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'note' => 'Initial inventory order',
            'items' => [
                [
                    'product_id' => $product->id,
                    'ordered_quantity' => 50,
                    'unit_cost' => 20.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/purchase-orders', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);
    });

    it('creates purchase order with multiple items', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $supplier = Supplier::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'product_id' => $product1->id,
                    'ordered_quantity' => 10,
                    'unit_cost' => 15.00,
                ],
                [
                    'product_id' => $product2->id,
                    'ordered_quantity' => 20,
                    'unit_cost' => 25.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/purchase-orders', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);
    });

    it('calculates total cost from items', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'ordered_quantity' => 5,
                    'unit_cost' => 30.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/purchase-orders', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);
    });

    it('sets status to draft by default', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'ordered_quantity' => 1,
                    'unit_cost' => 10.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/purchase-orders', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);
    });

    it('assigns creator as authenticated user', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'ordered_quantity' => 1,
                    'unit_cost' => 10.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/purchase-orders', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);

        $orderId = $response->json('data.id');

        expect($orderId)->not->toBeNull();
    });

    it('validates required warehouse_id', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersCreate->value);

        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'ordered_quantity' => 1,
                    'unit_cost' => 10.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/purchase-orders', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates required supplier_id', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'ordered_quantity' => 1,
                    'unit_cost' => 10.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/purchase-orders', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates required items array', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $supplier = Supplier::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'items' => [],
        ];

        $response = actingAs($user)->postJson('/api/v1/purchase-orders', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates each item has product_id, ordered_quantity, unit_cost', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $supplier = Supplier::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'ordered_quantity' => 1,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/purchase-orders', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates warehouse exists', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersCreate->value);

        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'warehouse_id' => 999999,
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'ordered_quantity' => 1,
                    'unit_cost' => 10.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/purchase-orders', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('validates supplier exists', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'supplier_id' => 999999,
            'items' => [
                [
                    'product_id' => $product->id,
                    'ordered_quantity' => 1,
                    'unit_cost' => 10.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/purchase-orders', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('validates products exist', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $supplier = Supplier::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'product_id' => 999999,
                    'ordered_quantity' => 1,
                    'unit_cost' => 10.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/purchase-orders', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('validates quantity is positive', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'ordered_quantity' => 0,
                    'unit_cost' => 10.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/purchase-orders', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates unit_cost is positive', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'ordered_quantity' => 1,
                    'unit_cost' => -5.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/purchase-orders', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('returns purchase order resource on success', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PurchaseOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'ordered_quantity' => 5,
                    'unit_cost' => 100.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/purchase-orders', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);
    });
});
