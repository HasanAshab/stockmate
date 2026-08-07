<?php

use App\Enums\Permission;
use App\Models\Supplier;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\putJson;

describe('Update Supplier', function () {
    it('requires authentication', function () {
        $supplier = Supplier::factory()->create();

        $response = putJson("/api/v1/suppliers/{$supplier->id}", []);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires suppliers-update permission', function () {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();

        $response = actingAs($user)->putJson("/api/v1/suppliers/{$supplier->id}", []);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('updates supplier and returns 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersUpdate->value);

        $supplier = Supplier::factory()->create([
            'name' => 'Old Supplier Name',
        ]);

        $payload = [
            'name' => 'New Supplier Name',
            'email' => 'newemail@supplier.com',
        ];

        $response = actingAs($user)->putJson("/api/v1/suppliers/{$supplier->id}", $payload);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment([
                'name' => 'New Supplier Name',
                'email' => 'newemail@supplier.com',
            ]);

        $supplier->refresh();

        expect($supplier->name)->toBe('New Supplier Name')
            ->and($supplier->email)->toBe('newemail@supplier.com');
    });

    it('rejects duplicate name', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersUpdate->value);

        Supplier::factory()->create(['name' => 'Existing Supplier']);
        $supplierToUpdate = Supplier::factory()->create(['name' => 'Original Supplier']);

        $payload = [
            'name' => 'Existing Supplier',
        ];

        $response = actingAs($user)->putJson("/api/v1/suppliers/{$supplierToUpdate->id}", $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('allows partial updates', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersUpdate->value);

        $supplier = Supplier::factory()->create([
            'name' => 'Original Name',
            'phone' => '+8801700000000',
        ]);

        $payload = [
            'name' => 'Partially Updated Supplier',
        ];

        $response = actingAs($user)->putJson("/api/v1/suppliers/{$supplier->id}", $payload);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment([
                'name' => 'Partially Updated Supplier',
            ]);
    });

    it('validates email format', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersUpdate->value);

        $supplier = Supplier::factory()->create();

        $payload = [
            'email' => 'not-an-email',
        ];

        $response = actingAs($user)->putJson("/api/v1/suppliers/{$supplier->id}", $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('returns 401 for unauthenticated request', function () {
        $supplier = Supplier::factory()->create();

        $response = putJson("/api/v1/suppliers/{$supplier->id}", [
            'name' => 'Test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();

        $response = actingAs($user)->putJson("/api/v1/suppliers/{$supplier->id}", [
            'name' => 'Test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent supplier', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersUpdate->value);

        $response = actingAs($user)->putJson('/api/v1/suppliers/999999', [
            'name' => 'Test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns updated supplier resource', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersUpdate->value);

        $supplier = Supplier::factory()->create();

        $response = actingAs($user)->putJson("/api/v1/suppliers/{$supplier->id}", [
            'name' => 'Updated Supplier Corp',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
