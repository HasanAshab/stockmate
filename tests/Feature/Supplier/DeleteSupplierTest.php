<?php

use App\Enums\Permission;
use App\Models\Supplier;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;

describe('Delete Supplier', function () {
    it('requires authentication', function () {
        $supplier = Supplier::factory()->create();

        $response = deleteJson("/api/v1/suppliers/{$supplier->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires suppliers-delete permission', function () {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();

        $response = actingAs($user)->deleteJson("/api/v1/suppliers/{$supplier->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('soft deletes supplier and returns 204', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersDelete->value);

        $supplier = Supplier::factory()->create();

        $response = actingAs($user)->deleteJson("/api/v1/suppliers/{$supplier->id}");

        $response->assertValidRequest()
            ->assertValidResponse(204);

        $this->assertSoftDeleted('suppliers', [
            'id' => $supplier->id,
        ]);
    });

    it('returns 401 for unauthenticated request', function () {
        $supplier = Supplier::factory()->create();

        $response = deleteJson("/api/v1/suppliers/{$supplier->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();

        $response = actingAs($user)->deleteJson("/api/v1/suppliers/{$supplier->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent supplier', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersDelete->value);

        $response = actingAs($user)->deleteJson('/api/v1/suppliers/999999');

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });
});
