<?php

use App\Enums\Role;
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

test('guest cannot view warehouses', function () {
    $response = $this->getJson('/api/v1/warehouses');
    $response->assertStatus(401);
});

test('staff and admin can view warehouses', function () {
    Warehouse::factory()->count(3)->create();

    Sanctum::actingAs($this->staff);
    $response = $this->getJson('/api/v1/warehouses');
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');

    Sanctum::actingAs($this->admin);
    $response = $this->getJson('/api/v1/warehouses');
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('only admin can create warehouse', function () {
    $data = [
        'name' => 'Main Warehouse',
        'location' => 'Downtown District',
    ];

    Sanctum::actingAs($this->staff);
    $response = $this->postJson('/api/v1/warehouses', $data);
    $response->assertStatus(403);

    Sanctum::actingAs($this->admin);
    $response = $this->postJson('/api/v1/warehouses', $data);
    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Main Warehouse')
        ->assertJsonPath('data.location', 'Downtown District');

    expect($response->json('data.is_active'))->toBeTrue();

    $this->assertDatabaseHas('warehouses', [
        'name' => 'Main Warehouse',
        'location' => 'Downtown District',
    ]);
});

test('warehouse name must be unique', function () {
    Warehouse::factory()->create(['name' => 'Main Warehouse']);

    $data = [
        'name' => 'Main Warehouse',
        'location' => 'Another Location',
    ];

    Sanctum::actingAs($this->admin);
    $response = $this->postJson('/api/v1/warehouses', $data);
    $response->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('staff and admin can view single warehouse', function () {
    $warehouse = Warehouse::factory()->create();

    Sanctum::actingAs($this->staff);
    $response = $this->getJson("/api/v1/warehouses/{$warehouse->id}");
    $response->assertStatus(200)
        ->assertJsonPath('data.id', $warehouse->id);

    Sanctum::actingAs($this->admin);
    $response = $this->getJson("/api/v1/warehouses/{$warehouse->id}");
    $response->assertStatus(200)
        ->assertJsonPath('data.id', $warehouse->id);
});

test('only admin can update warehouse', function () {
    $warehouse = Warehouse::factory()->create(['name' => 'Old Name']);

    $data = ['name' => 'New Name'];

    Sanctum::actingAs($this->staff);
    $response = $this->patchJson("/api/v1/warehouses/{$warehouse->id}", $data);
    $response->assertStatus(403);

    Sanctum::actingAs($this->admin);
    $response = $this->patchJson("/api/v1/warehouses/{$warehouse->id}", $data);
    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'New Name');

    $this->assertDatabaseHas('warehouses', [
        'id' => $warehouse->id,
        'name' => 'New Name',
    ]);
});

test('admin can deactivate warehouse', function () {
    $warehouse = Warehouse::factory()->create(['is_active' => true]);

    Sanctum::actingAs($this->admin);
    $response = $this->patchJson("/api/v1/warehouses/{$warehouse->id}", [
        'is_active' => false,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.is_active', false);

    $this->assertDatabaseHas('warehouses', [
        'id' => $warehouse->id,
        'is_active' => false,
    ]);
});

test('only admin can delete warehouse', function () {
    $warehouse = Warehouse::factory()->create();

    Sanctum::actingAs($this->staff);
    $response = $this->deleteJson("/api/v1/warehouses/{$warehouse->id}");
    $response->assertStatus(403);

    Sanctum::actingAs($this->admin);
    $response = $this->deleteJson("/api/v1/warehouses/{$warehouse->id}");
    $response->assertStatus(200)
        ->assertJsonPath('message', 'Warehouse deleted.');

    $this->assertDatabaseMissing('warehouses', [
        'id' => $warehouse->id,
    ]);
});

test('cannot delete warehouse with existing stock', function () {
    $warehouse = Warehouse::factory()->create();
    WarehouseStock::factory()->create([
        'warehouse_id' => $warehouse->id,
        'quantity' => 100,
    ]);

    Sanctum::actingAs($this->admin);
    $response = $this->deleteJson("/api/v1/warehouses/{$warehouse->id}");
    $response->assertStatus(422)
        ->assertJsonPath('message', 'Cannot delete warehouse with existing stock. Transfer or clear stock first.');

    $this->assertDatabaseHas('warehouses', [
        'id' => $warehouse->id,
    ]);
});

test('can delete warehouse with zero stock', function () {
    $warehouse = Warehouse::factory()->create();
    WarehouseStock::factory()->create([
        'warehouse_id' => $warehouse->id,
        'quantity' => 0,
    ]);

    Sanctum::actingAs($this->admin);
    $response = $this->deleteJson("/api/v1/warehouses/{$warehouse->id}");
    $response->assertStatus(200);

    $this->assertDatabaseMissing('warehouses', [
        'id' => $warehouse->id,
    ]);
});

test('staff and admin can view warehouse stock', function () {
    $warehouse = Warehouse::factory()->create();
    WarehouseStock::factory()->count(5)->create([
        'warehouse_id' => $warehouse->id,
    ]);

    Sanctum::actingAs($this->staff);
    $response = $this->getJson("/api/v1/warehouses/{$warehouse->id}/stock");
    $response->assertStatus(200)
        ->assertJsonCount(5, 'data');

    Sanctum::actingAs($this->admin);
    $response = $this->getJson("/api/v1/warehouses/{$warehouse->id}/stock");
    $response->assertStatus(200)
        ->assertJsonCount(5, 'data');
});

test('warehouse stock includes product details and status', function () {
    $warehouse = Warehouse::factory()->create();
    $stock = WarehouseStock::factory()->create([
        'warehouse_id' => $warehouse->id,
        'quantity' => 5,
        'reorder_threshold' => 10,
    ]);

    Sanctum::actingAs($this->admin);
    $response = $this->getJson("/api/v1/warehouses/{$warehouse->id}/stock");
    $response->assertStatus(200)
        ->assertJsonPath('data.0.product_id', $stock->product_id)
        ->assertJsonPath('data.0.quantity', 5)
        ->assertJsonPath('data.0.reorder_threshold', 10)
        ->assertJsonPath('data.0.status', 'low')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'product_id',
                    'product_name',
                    'sku',
                    'category',
                    'quantity',
                    'reorder_threshold',
                    'status',
                ],
            ],
        ]);
});
