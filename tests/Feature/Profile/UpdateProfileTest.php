<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\putJson;

describe('Update Profile', function () {
    it('requires authentication', function () {
        $response = putJson('/api/v1/profile', []);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('updates authenticated user profile and returns 200', function () {
        $user = User::factory()->create(['name' => 'Old Name']);

        $payload = [
            'name' => 'Updated Profile Name',
        ];

        $response = actingAs($user)->putJson('/api/v1/profile', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['name' => 'Updated Profile Name']);

        expect($user->fresh()->name)->toBe('Updated Profile Name');
    });

    it('rejects duplicate email', function () {
        User::factory()->create(['email' => 'other@example.com']);
        $user = User::factory()->create(['email' => 'mine@example.com']);

        $payload = [
            'email' => 'other@example.com',
        ];

        $response = actingAs($user)->putJson('/api/v1/profile', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('allows partial updates', function () {
        $user = User::factory()->create(['name' => 'Original Name']);

        $payload = [
            'name' => 'New Name Only',
        ];

        $response = actingAs($user)->putJson('/api/v1/profile', $payload);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('validates max length for name field', function () {
        $user = User::factory()->create();

        $payload = [
            'name' => str_repeat('a', 51),
        ];

        $response = actingAs($user)->putJson('/api/v1/profile', $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('returns 401 for unauthenticated request', function () {
        $response = putJson('/api/v1/profile', [
            'name' => 'Test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns updated user resource', function () {
        $user = User::factory()->create();

        $response = actingAs($user)->putJson('/api/v1/profile', [
            'name' => 'Valid Updated Name',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });
});
