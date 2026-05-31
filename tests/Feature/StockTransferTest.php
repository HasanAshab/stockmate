<?php

use App\Enums\Role;
use App\Enums\StockLogType;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => Role::Admin]);
    $this->staff = User::factory()->create(['role' => Role::Staff]);
});

test('guest cannot transfer stock', function () {
    $response = $this->postJson('/api/v1/stock-logs/transfer', []);
    $response->assertStatus(401);
});

test('only admin can transfer stock', function () {
    $sourceWarehouse = Warehouse::factory()->create();
    $destinationWarehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    WarehouseStock::factory()->create([
        'warehouse_id' => $sourceWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 100,
    ]);

    $data = [
        'from_warehouse_id' => $sourceWarehouse->id,
        'to_warehouse_id' => $destinationWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 50,
        'note' => 'Restocking',
    ];

    Sanctum::actingAs($this->staff);
    $response = $this->postJson('/api/v1/stock-logs/transfer', $data);
    $response->assertStatus(403);

    Sanctum::actingAs($this->admin);
    $response = $this->postJson('/api/v1/stock-logs/transfer', $data);
    $response->assertStatus(200)
        ->assertJsonPath('message', 'Stock transferred successfully.');
});

test('transfer decrements source warehouse stock', function () {
    $sourceWarehouse = Warehouse::factory()->create();
    $destinationWarehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    WarehouseStock::factory()->create([
        'warehouse_id' => $sourceWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 100,
    ]);

    Sanctum::actingAs($this->admin);
    $this->postJson('/api/v1/stock-logs/transfer', [
        'from_warehouse_id' => $sourceWarehouse->id,
        'to_warehouse_id' => $destinationWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 50,
    ]);

    $this->assertDatabaseHas('warehouse_stock', [
        'warehouse_id' => $sourceWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 50,
    ]);
});

test('transfer increments destination warehouse stock', function () {
    $sourceWarehouse = Warehouse::factory()->create();
    $destinationWarehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    WarehouseStock::factory()->create([
        'warehouse_id' => $sourceWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 100,
    ]);

    Sanctum::actingAs($this->admin);
    $this->postJson('/api/v1/stock-logs/transfer', [
        'from_warehouse_id' => $sourceWarehouse->id,
        'to_warehouse_id' => $destinationWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 50,
    ]);

    $this->assertDatabaseHas('warehouse_stock', [
        'warehouse_id' => $destinationWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 50,
    ]);
});

test('transfer creates two stock log entries', function () {
    $sourceWarehouse = Warehouse::factory()->create();
    $destinationWarehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    WarehouseStock::factory()->create([
        'warehouse_id' => $sourceWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 100,
    ]);

    Sanctum::actingAs($this->admin);
    $this->postJson('/api/v1/stock-logs/transfer', [
        'from_warehouse_id' => $sourceWarehouse->id,
        'to_warehouse_id' => $destinationWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 50,
        'note' => 'Restocking',
    ]);

    $this->assertDatabaseHas('stock_logs', [
        'warehouse_id' => $sourceWarehouse->id,
        'product_id' => $product->id,
        'type' => StockLogType::Out,
        'quantity' => 50,
    ]);

    $this->assertDatabaseHas('stock_logs', [
        'warehouse_id' => $destinationWarehouse->id,
        'product_id' => $product->id,
        'type' => StockLogType::In,
        'quantity' => 50,
    ]);

    expect(StockLog::count())->toBe(2);
});

test('transfer notes include warehouse references', function () {
    $sourceWarehouse = Warehouse::factory()->create();
    $destinationWarehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    WarehouseStock::factory()->create([
        'warehouse_id' => $sourceWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 100,
    ]);

    Sanctum::actingAs($this->admin);
    $this->postJson('/api/v1/stock-logs/transfer', [
        'from_warehouse_id' => $sourceWarehouse->id,
        'to_warehouse_id' => $destinationWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 50,
        'note' => 'Urgent restocking',
    ]);

    $outLog = StockLog::where('type', StockLogType::Out)->first();
    $inLog = StockLog::where('type', StockLogType::In)->first();

    expect($outLog->note)->toContain("Transfer to warehouse #{$destinationWarehouse->id}")
        ->and($outLog->note)->toContain('Urgent restocking')
        ->and($inLog->note)->toContain("Transfer from warehouse #{$sourceWarehouse->id}")
        ->and($inLog->note)->toContain('Urgent restocking');
});

test('cannot transfer more stock than available', function () {
    $sourceWarehouse = Warehouse::factory()->create();
    $destinationWarehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    WarehouseStock::factory()->create([
        'warehouse_id' => $sourceWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 30,
    ]);

    Sanctum::actingAs($this->admin);
    $response = $this->postJson('/api/v1/stock-logs/transfer', [
        'from_warehouse_id' => $sourceWarehouse->id,
        'to_warehouse_id' => $destinationWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 50,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('quantity');
});

test('cannot transfer from non-existent warehouse stock', function () {
    $sourceWarehouse = Warehouse::factory()->create();
    $destinationWarehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    Sanctum::actingAs($this->admin);
    $response = $this->postJson('/api/v1/stock-logs/transfer', [
        'from_warehouse_id' => $sourceWarehouse->id,
        'to_warehouse_id' => $destinationWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 50,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('quantity');
});

test('cannot transfer to same warehouse', function () {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    WarehouseStock::factory()->create([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'quantity' => 100,
    ]);

    Sanctum::actingAs($this->admin);
    $response = $this->postJson('/api/v1/stock-logs/transfer', [
        'from_warehouse_id' => $warehouse->id,
        'to_warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'quantity' => 50,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('to_warehouse_id');
});

test('transfer creates destination warehouse stock if not exists', function () {
    $sourceWarehouse = Warehouse::factory()->create();
    $destinationWarehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    WarehouseStock::factory()->create([
        'warehouse_id' => $sourceWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 100,
    ]);

    expect(WarehouseStock::where('warehouse_id', $destinationWarehouse->id)->exists())->toBeFalse();

    Sanctum::actingAs($this->admin);
    $this->postJson('/api/v1/stock-logs/transfer', [
        'from_warehouse_id' => $sourceWarehouse->id,
        'to_warehouse_id' => $destinationWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 50,
    ]);

    $this->assertDatabaseHas('warehouse_stock', [
        'warehouse_id' => $destinationWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 50,
        'reorder_threshold' => 10,
    ]);
});

test('transfer unit_cost is null for both logs', function () {
    $sourceWarehouse = Warehouse::factory()->create();
    $destinationWarehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    WarehouseStock::factory()->create([
        'warehouse_id' => $sourceWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 100,
    ]);

    Sanctum::actingAs($this->admin);
    $this->postJson('/api/v1/stock-logs/transfer', [
        'from_warehouse_id' => $sourceWarehouse->id,
        'to_warehouse_id' => $destinationWarehouse->id,
        'product_id' => $product->id,
        'quantity' => 50,
    ]);

    $logs = StockLog::all();

    expect($logs)->toHaveCount(2)
        ->and($logs->every(fn ($log) => $log->unit_cost === null))->toBeTrue();
});
