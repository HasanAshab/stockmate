<?php

use App\Enums\Permission;
use App\Models\Supplier;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('Show Supplier', function () {
    it('requires authentication', function () {
        $supplier = Supplier::factory()->create();

        $response = getJson("/api/v1/suppliers/{$supplier->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires suppliers-view permission', function () {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/suppliers/{$supplier->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns supplier details with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersView->value);

        $supplier = Supplier::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/suppliers/{$supplier->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $supplier->id]);
    });

    it('returns 401 for unauthenticated request', function () {
        $supplier = Supplier::factory()->create();

        $response = getJson("/api/v1/suppliers/{$supplier->id}");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/suppliers/{$supplier->id}");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent supplier', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersView->value);

        $response = actingAs($user)->getJson('/api/v1/suppliers/999999');

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns supplier resource', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersView->value);

        $supplier = Supplier::factory()->create();

        $response = actingAs($user)->getJson("/api/v1/suppliers/{$supplier->id}");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
