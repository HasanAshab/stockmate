<?php

use App\Enums\Permission;
use App\Models\Supplier;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

describe('Restore Supplier', function () {
    it('requires authentication', function () {
        $supplier = Supplier::factory()->create();
        $supplier->delete();

        $response = postJson("/api/v1/suppliers/{$supplier->id}/restore");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires suppliers-restore permission', function () {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();
        $supplier->delete();

        $response = actingAs($user)->postJson("/api/v1/suppliers/{$supplier->id}/restore");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('restores soft-deleted supplier and returns 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersRestore->value);

        $supplier = Supplier::factory()->create();
        $supplier->delete();

        $response = actingAs($user)->postJson("/api/v1/suppliers/{$supplier->id}/restore");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $supplier->id]);

        $this->assertNotSoftDeleted('suppliers', [
            'id' => $supplier->id,
        ]);
    });

    it('returns 401 for unauthenticated request', function () {
        $supplier = Supplier::factory()->create();
        $supplier->delete();

        $response = postJson("/api/v1/suppliers/{$supplier->id}/restore");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();
        $supplier->delete();

        $response = actingAs($user)->postJson("/api/v1/suppliers/{$supplier->id}/restore");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent trashed supplier', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersRestore->value);

        $response = actingAs($user)->postJson('/api/v1/suppliers/999999/restore');

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns restored supplier resource', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersRestore->value);

        $supplier = Supplier::factory()->create();
        $supplier->delete();

        $response = actingAs($user)->postJson("/api/v1/suppliers/{$supplier->id}/restore");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
