<?php

use App\Enums\Permission;
use App\Models\Supplier;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('List Trashed Suppliers', function () {
    it('requires authentication', function () {
        $response = getJson('/api/v1/suppliers/trashed');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires suppliers-view permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/suppliers/trashed');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns list of soft-deleted suppliers with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersView->value);

        $activeSupplier = Supplier::factory()->create();
        $trashedSupplier = Supplier::factory()->create();
        $trashedSupplier->delete();

        $response = actingAs($user)->getJson('/api/v1/suppliers/trashed');

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $trashedSupplier->id])
            ->assertJsonMissing(['id' => $activeSupplier->id]);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = getJson('/api/v1/suppliers/trashed');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/suppliers/trashed');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns supplier resource collection', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersView->value);

        $trashedSupplier = Supplier::factory()->create();
        $trashedSupplier->delete();

        $response = actingAs($user)->getJson('/api/v1/suppliers/trashed');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
