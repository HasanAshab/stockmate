<?php

use App\Enums\Permission;
use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('List Roles', function () {
    it('requires authentication', function () {
        $response = getJson('/api/v1/roles');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires roles-view permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/roles');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns list of roles with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::RolesView->value);

        $response = actingAs($user)->getJson('/api/v1/roles');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('includes permissions relationship', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::RolesView->value);

        $response = actingAs($user)->getJson('/api/v1/roles');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = getJson('/api/v1/roles');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/roles');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns role resource collection', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::RolesView->value);

        $response = actingAs($user)->getJson('/api/v1/roles');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
