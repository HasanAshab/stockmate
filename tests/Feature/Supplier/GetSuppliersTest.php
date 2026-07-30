<?php

use App\Enums\Permission;
use App\Models\Supplier;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('List Suppliers', function () {
    it('requires authentication', function () {
        $response = getJson('/api/v1/suppliers');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires suppliers-view permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/suppliers');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns paginated list of suppliers with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersView->value);

        Supplier::factory()->count(3)->create();

        $response = actingAs($user)->getJson('/api/v1/suppliers');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('filters suppliers by name', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersView->value);

        $supplier = Supplier::factory()->create(['name' => 'Acme Corp']);

        $response = actingAs($user)->getJson('/api/v1/suppliers');

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $supplier->id]);
    });

    it('filters suppliers by email', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersView->value);

        $supplier = Supplier::factory()->create(['email' => 'supplier@acme.com']);

        $response = actingAs($user)->getJson('/api/v1/suppliers');

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $supplier->id]);
    });

    it('filters suppliers by phone', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersView->value);

        $supplier = Supplier::factory()->create(['phone' => '+8801712345678']);

        $response = actingAs($user)->getJson('/api/v1/suppliers');

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['id' => $supplier->id]);
    });

    it('sorts suppliers by created_at', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersView->value);

        Supplier::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/suppliers');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = getJson('/api/v1/suppliers');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/suppliers');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns supplier resource collection', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::SuppliersView->value);

        Supplier::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/suppliers');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
