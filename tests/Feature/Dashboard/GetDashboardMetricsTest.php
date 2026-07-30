<?php

use App\Enums\Permission;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

describe('Dashboard Metrics', function () {
    it('requires authentication', function () {
        $response = getJson('/api/v1/dashboard');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires dashboard-view permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/dashboard');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns dashboard metrics with 200', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::DashboardView->value);

        $response = actingAs($user)->getJson('/api/v1/dashboard');

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = getJson('/api/v1/dashboard');

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->getJson('/api/v1/dashboard');

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });
});
