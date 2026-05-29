<?php

use App\Enums\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => Role::Admin]);
    $this->staff = User::factory()->create(['role' => Role::Staff]);
});

test('guest cannot view suppliers', function () {
    $response = $this->getJson('/api/v1/suppliers');
    $response->assertStatus(403);
});

test('staff and admin can view suppliers', function () {
    Supplier::factory()->count(3)->create();

    // Staff check
    Sanctum::actingAs($this->staff);
    $response = $this->getJson('/api/v1/suppliers');
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');

    // Admin check
    Sanctum::actingAs($this->admin);
    $response = $this->getJson('/api/v1/suppliers');
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('staff and admin can create supplier', function () {
    $data = [
        'name' => 'Acme Corp',
        'email' => 'john@acme.com',
        'phone' => '1234567890',
        'address' => '123 Acme Road',
    ];

    // Staff can
    Sanctum::actingAs($this->staff);
    $response = $this->postJson('/api/v1/suppliers', $data);
    $response->assertStatus(201)
        ->assertJsonPath('data.attributes.name', 'Acme Corp');

    $this->assertDatabaseHas('suppliers', [
        'name' => 'Acme Corp',
        'email' => 'john@acme.com',
    ]);
});

test('staff and admin can update supplier', function () {
    $supplier = Supplier::factory()->create([
        'name' => 'Old Name',
    ]);

    $data = ['name' => 'New Name'];

    // Staff can
    Sanctum::actingAs($this->staff);
    $response = $this->patchJson("/api/v1/suppliers/{$supplier->id}", $data);
    $response->assertStatus(200)
        ->assertJsonPath('data.attributes.name', 'New Name');

    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'name' => 'New Name',
    ]);
});

test('staff and admin can delete supplier', function () {
    $supplier = Supplier::factory()->create();

    // Staff can
    Sanctum::actingAs($this->staff);
    $response = $this->deleteJson("/api/v1/suppliers/{$supplier->id}");
    $response->assertStatus(204);

    $this->assertSoftDeleted('suppliers', [
        'id' => $supplier->id,
    ]);
});

test('staff and admin can restore supplier', function () {
    $supplier = Supplier::factory()->create();
    $supplier->delete();

    // Staff can
    Sanctum::actingAs($this->staff);
    $response = $this->postJson("/api/v1/suppliers/{$supplier->id}/restore");
    $response->assertStatus(200);

    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'deleted_at' => null,
    ]);
});
