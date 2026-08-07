<?php

use App\Enums\Permission;
use App\Enums\SalesOrderStatus;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('Create Sales Order', function () {
    it('requires authentication', function () {
        $response = postJson('/api/v1/sales-orders', []);

        $response->assertValidResponse(401);
    });

    it('requires sales-orders-create permission', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        WarehouseStock::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 20,
        ]);

        $payload = [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '+8801712345678',
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_price' => 100.00,
                ],
            ],
        ];
        $response = actingAs($user)->postJson('/api/v1/sales-orders', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('creates a new sales order and returns 201', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        WarehouseStock::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 20,
        ]);

        $payload = [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '+8801712345678',
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_price' => 100.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/sales-orders', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201)
            ->assertJsonFragment([
                'customer_name' => 'John Doe',
                'customer_email' => 'john@example.com',
            ]);

        $this->assertDatabaseHas('sales_orders', [
            'customer_name' => 'John Doe',
            'warehouse_id' => $warehouse->id,
            'creator_id' => $user->id,
        ]);
    });

    it('creates sales order with multiple items', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        WarehouseStock::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product1->id,
            'quantity' => 10,
        ]);

        WarehouseStock::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product2->id,
            'quantity' => 10,
        ]);

        $payload = [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'customer_phone' => null,
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 2,
                    'unit_price' => 50.00,
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 3,
                    'unit_price' => 30.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/sales-orders', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);
    });

    it('reserves stock from warehouse', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        WarehouseStock::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $payload = [
            'customer_name' => 'Bob',
            'customer_email' => 'bob@example.com',
            'customer_phone' => null,
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 4,
                    'unit_price' => 100.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/sales-orders', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);
    });

    it('sets order status to pending', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        WarehouseStock::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $payload = [
            'customer_name' => 'Charlie',
            'customer_email' => 'charlie@example.com',
            'customer_phone' => null,
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 10.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/sales-orders', $payload);
        $response->assertValidRequest()
            ->assertValidResponse(201)
            ->assertJsonFragment([
                'status' => SalesOrderStatus::Pending->value,
            ]);
    });

    it('assigns creator as authenticated user', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        WarehouseStock::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $payload = [
            'customer_name' => 'Dave',
            'customer_email' => 'dave@example.com',
            'customer_phone' => null,
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 10.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/sales-orders', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);

        $orderId = $response->json('data.id');
        $this->assertDatabaseHas('sales_orders', [
            'id' => $orderId,
            'creator_id' => $user->id,
        ]);
    });

    it('returns validation errors for invalid input', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCreate->value);

        $response = actingAs($user)->postJson('/api/v1/sales-orders', []);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('returns 422 for insufficient stock', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        WarehouseStock::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $payload = [
            'customer_name' => 'Eve',
            'customer_email' => 'eve@example.com',
            'customer_phone' => null,
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_price' => 100.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/sales-orders', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('validates required warehouse_id', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCreate->value);

        $product = Product::factory()->create();

        $payload = [
            'customer_name' => 'Frank',
            'customer_email' => 'frank@example.com',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 10.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/sales-orders', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates required items array', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();

        $payload = [
            'customer_name' => 'Grace',
            'customer_email' => 'grace@example.com',
            'warehouse_id' => $warehouse->id,
            'items' => [],
        ];

        $response = actingAs($user)->postJson('/api/v1/sales-orders', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates each item has product_id, quantity, unit_price', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();

        $payload = [
            'customer_name' => 'Heidi',
            'customer_email' => 'heidi@example.com',
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'quantity' => 1,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/sales-orders', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates warehouse exists', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCreate->value);

        $product = Product::factory()->create();

        $payload = [
            'customer_name' => 'Ivan',
            'customer_email' => 'ivan@example.com',
            'warehouse_id' => 999999,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 10.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/sales-orders', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('validates products exist', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();

        $payload = [
            'customer_name' => 'Judy',
            'customer_email' => 'judy@example.com',
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'product_id' => 999999,
                    'quantity' => 1,
                    'unit_price' => 10.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/sales-orders', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('validates quantity is positive', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'customer_name' => 'Kevin',
            'customer_email' => 'kevin@example.com',
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 0,
                    'unit_price' => 10.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/sales-orders', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates unit_price is positive', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'customer_name' => 'Laura',
            'customer_email' => 'laura@example.com',
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => -5.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/sales-orders', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates customer_email format', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'customer_name' => 'Mallory',
            'customer_email' => 'invalid-email',
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 10.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/sales-orders', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('returns sales order resource on success', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SalesOrdersCreate->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        WarehouseStock::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $payload = [
            'customer_name' => 'Niaj',
            'customer_email' => 'niaj@example.com',
            'customer_phone' => null,
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 50.00,
                ],
            ],
        ];

        $response = actingAs($user)->postJson('/api/v1/sales-orders', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);
    });
});
