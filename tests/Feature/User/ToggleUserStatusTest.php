<?php

use App\Enums\Permission;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patchJson;

describe('Toggle User Status', function () {
    it('requires authentication', function () {
        $targetUser = User::factory()->create();

        $response = patchJson("/api/v1/users/{$targetUser->id}/toggle-status");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('requires users-deactivate permission', function () {
        $authUser = User::factory()->create();
        $targetUser = User::factory()->create();

        $response = actingAs($authUser)->patchJson("/api/v1/users/{$targetUser->id}/toggle-status");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('toggles user status from active to inactive', function () {
        $authUser = User::factory()->create();
        $authUser->givePermissionTo(Permission::UsersDeactivate->value);

        $targetUser = User::factory()->create(['is_active' => true]);

        $response = actingAs($authUser)->patchJson("/api/v1/users/{$targetUser->id}/toggle-status");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment([
                'is_active' => false,
            ]);

        expect($targetUser->fresh()->is_active)->toBeFalse();
    });

    it('toggles user status from inactive to active', function () {
        $authUser = User::factory()->create();
        $authUser->givePermissionTo(Permission::UsersDeactivate->value);

        $targetUser = User::factory()->create(['is_active' => false]);

        $response = actingAs($authUser)->patchJson("/api/v1/users/{$targetUser->id}/toggle-status");

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment([
                'is_active' => true,
            ]);

        expect($targetUser->fresh()->is_active)->toBeTrue();
    });

    it('prevents self-deactivation', function () {
        $authUser = User::factory()->create();
        $authUser->givePermissionTo(Permission::UsersDeactivate->value);

        $response = actingAs($authUser)->patchJson("/api/v1/users/{$authUser->id}/toggle-status");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 401 for unauthenticated request', function () {
        $targetUser = User::factory()->create();

        $response = patchJson("/api/v1/users/{$targetUser->id}/toggle-status");

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 403 for user without permission', function () {
        $authUser = User::factory()->create();
        $targetUser = User::factory()->create();

        $response = actingAs($authUser)->patchJson("/api/v1/users/{$targetUser->id}/toggle-status");

        $response->assertValidRequest()
            ->assertValidResponse(403);
    });

    it('returns 404 for non-existent user', function () {
        $authUser = User::factory()->create();
        $authUser->givePermissionTo(Permission::UsersDeactivate->value);

        $response = actingAs($authUser)->patchJson('/api/v1/users/999999/toggle-status');

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns updated user resource', function () {
        $authUser = User::factory()->create();
        $authUser->givePermissionTo(Permission::UsersDeactivate->value);

        $targetUser = User::factory()->create();

        $response = actingAs($authUser)->patchJson("/api/v1/users/{$targetUser->id}/toggle-status");

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
