<?php

use App\Enums\Role;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\patchJson;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => Role::Admin]);
    $this->staff = User::factory()->create(['role' => Role::Staff]);
    $this->warehouse = Warehouse::factory()->create();
    $this->product = Product::factory()->create();
    $this->warehouseStock = WarehouseStock::factory()->create([
        'warehouse_id' => $this->warehouse->id,
        'product_id' => $this->product->id,
        'quantity' => 100,
        'reorder_threshold' => 10,
    ]);
});

test('admin can update warehouse stock reorder threshold', function () {
    actingAs($this->admin, 'sanctum')
        ->patchJson("/api/v1/warehouses/{$this->warehouse->id}/stock/{$this->warehouseStock->id}", [
            'reorder_threshold' => 25,
        ])
        ->assertOk()
        ->assertJsonPath('data.reorder_threshold', 25);

    assertDatabaseHas('warehouse_stock', [
        'id' => $this->warehouseStock->id,
        'reorder_threshold' => 25,
    ]);
});

test('staff can update warehouse stock reorder threshold', function () {
    actingAs($this->staff, 'sanctum')
        ->patchJson("/api/v1/warehouses/{$this->warehouse->id}/stock/{$this->warehouseStock->id}", [
            'reorder_threshold' => 30,
        ])
        ->assertOk()
        ->assertJsonPath('data.reorder_threshold', 30);

    assertDatabaseHas('warehouse_stock', [
        'id' => $this->warehouseStock->id,
        'reorder_threshold' => 30,
    ]);
});

test('reorder threshold must be a non-negative integer', function () {
    actingAs($this->admin, 'sanctum')
        ->patchJson("/api/v1/warehouses/{$this->warehouse->id}/stock/{$this->warehouseStock->id}", [
            'reorder_threshold' => -5,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('reorder_threshold');
});

test('reorder threshold is required', function () {
    actingAs($this->admin, 'sanctum')
        ->patchJson("/api/v1/warehouses/{$this->warehouse->id}/stock/{$this->warehouseStock->id}", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('reorder_threshold');
});

test('reorder threshold must be an integer', function () {
    actingAs($this->admin, 'sanctum')
        ->patchJson("/api/v1/warehouses/{$this->warehouse->id}/stock/{$this->warehouseStock->id}", [
            'reorder_threshold' => 'not-an-integer',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('reorder_threshold');
});

test('cannot update warehouse stock from different warehouse', function () {
    $otherWarehouse = Warehouse::factory()->create();

    actingAs($this->admin, 'sanctum')
        ->patchJson("/api/v1/warehouses/{$otherWarehouse->id}/stock/{$this->warehouseStock->id}", [
            'reorder_threshold' => 25,
        ])
        ->assertNotFound();
});

test('guest cannot update warehouse stock reorder threshold', function () {
    patchJson("/api/v1/warehouses/{$this->warehouse->id}/stock/{$this->warehouseStock->id}", [
        'reorder_threshold' => 25,
    ])->assertUnauthorized();
});

test('updating reorder threshold does not change quantity', function () {
    actingAs($this->admin, 'sanctum')
        ->patchJson("/api/v1/warehouses/{$this->warehouse->id}/stock/{$this->warehouseStock->id}", [
            'reorder_threshold' => 50,
        ])
        ->assertOk();

    assertDatabaseHas('warehouse_stock', [
        'id' => $this->warehouseStock->id,
        'quantity' => 100, // unchanged
        'reorder_threshold' => 50,
    ]);
});
