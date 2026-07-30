<?php

use App\Enums\Permission;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('List Permissions', function () {
    it('requires authentication', function () {
        $response = getJson('/api/v1/permissions');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires permissions-view permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/permissions');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns list of permissions with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PermissionsView->value);

        $response = actingAs($user)->getJson('/api/v1/permissions');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = getJson('/api/v1/permissions');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/permissions');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns permission resource collection', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::PermissionsView->value);

        $response = actingAs($user)->getJson('/api/v1/permissions');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
