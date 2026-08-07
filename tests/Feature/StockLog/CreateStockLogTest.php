<?php

use App\Enums\Permission;
use App\Enums\StockLogType;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('Create Stock Log', function () {
    it('requires authentication', function () {
        $response = postJson('/api/v1/stock-logs', []);

        $response->assertValidResponse(401);
    });

    it('requires stock-logs-create permission', function () {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'type' => StockLogType::In->value,
            'quantity' => 10,
            'unit_cost' => 15.00,
            'note' => 'Manual stock count adjustment',
        ];
        $response = actingAs($user)->postJson('/api/v1/stock-logs', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('creates a new stock log and returns 201', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::StockLogsCreate->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'type' => StockLogType::In->value,
            'quantity' => 10,
            'unit_cost' => 15.00,
            'note' => 'Manual stock count adjustment',
        ];

        $response = actingAs($user)->postJson('/api/v1/stock-logs', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);

        $this->assertDatabaseHas('stock_logs', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);
    });

    it('validates required warehouse_id', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::StockLogsCreate->value);

        $product = Product::factory()->create();

        $payload = [
            'product_id' => $product->id,
            'type' => StockLogType::In->value,
            'quantity' => 5,
        ];

        $response = actingAs($user)->postJson('/api/v1/stock-logs', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates required product_id', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::StockLogsCreate->value);

        $warehouse = Warehouse::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'type' => StockLogType::In->value,
            'quantity' => 5,
        ];

        $response = actingAs($user)->postJson('/api/v1/stock-logs', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates required type', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::StockLogsCreate->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ];

        $response = actingAs($user)->postJson('/api/v1/stock-logs', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates required quantity', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::StockLogsCreate->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'type' => StockLogType::In->value,
        ];

        $response = actingAs($user)->postJson('/api/v1/stock-logs', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('validates warehouse exists', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::StockLogsCreate->value);

        $product = Product::factory()->create();

        $payload = [
            'warehouse_id' => 999999,
            'product_id' => $product->id,
            'type' => StockLogType::In->value,
            'quantity' => 5,
        ];

        $response = actingAs($user)->postJson('/api/v1/stock-logs', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('validates product exists', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::StockLogsCreate->value);

        $warehouse = Warehouse::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'product_id' => 999999,
            'type' => StockLogType::In->value,
            'quantity' => 5,
        ];

        $response = actingAs($user)->postJson('/api/v1/stock-logs', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('allows nullable note', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::StockLogsCreate->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'type' => StockLogType::In->value,
            'quantity' => 5,
            'note' => null,
        ];

        $response = actingAs($user)->postJson('/api/v1/stock-logs', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);
    });

    it('assigns creator as authenticated user', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::StockLogsCreate->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'type' => StockLogType::In->value,
            'quantity' => 5,
        ];

        $response = actingAs($user)->postJson('/api/v1/stock-logs', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);

        $logId = $response->json('data.id');
        $this->assertDatabaseHas('stock_logs', [
            'id' => $logId,
            'user_id' => $user->id,
        ]);
    });

    it('returns stock log resource on success', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::StockLogsCreate->value);

        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'type' => StockLogType::In->value,
            'quantity' => 12,
        ];

        $response = actingAs($user)->postJson('/api/v1/stock-logs', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(201);
    });
});
