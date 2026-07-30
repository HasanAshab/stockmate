<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\putJson;

describe('Update User', function () {
    it('requires authentication', function () {
        $targetUser = User::factory()->create();

        $response = putJson("/api/v1/users/{$targetUser->id}", []);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('updates user and returns 200', function () {
        $authUser = User::factory()->create();
        $targetUser = User::factory()->create(['name' => 'Original Name']);

        $payload = [
            'name' => 'Updated User Name',
        ];

        $response = actingAs($authUser)->putJson("/api/v1/users/{$targetUser->id}", $payload);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment([
                'name' => 'Updated User Name',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Updated User Name',
        ]);
    });

    it('returns validation errors for invalid input', function () {
        $authUser = User::factory()->create();
        $targetUser = User::factory()->create();

        $payload = [
            'name' => str_repeat('a', 51),
        ];

        $response = actingAs($authUser)->putJson("/api/v1/users/{$targetUser->id}", $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('rejects duplicate email', function () {
        $authUser = User::factory()->create();
        User::factory()->create(['email' => 'taken@example.com']);
        $targetUser = User::factory()->create(['email' => 'myemail@example.com']);

        $payload = [
            'email' => 'taken@example.com',
        ];

        $response = actingAs($authUser)->putJson("/api/v1/users/{$targetUser->id}", $payload);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('allows partial updates', function () {
        $authUser = User::factory()->create();
        $targetUser = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'keepemail@example.com',
        ]);

        $payload = [
            'name' => 'New Name Only',
        ];

        $response = actingAs($authUser)->putJson("/api/v1/users/{$targetUser->id}", $payload);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment([
                'name' => 'New Name Only',
                'email' => 'keepemail@example.com',
            ]);
    });

    it('returns 401 for unauthenticated request', function () {
        $targetUser = User::factory()->create();

        $response = putJson("/api/v1/users/{$targetUser->id}", [
            'name' => 'Test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 404 for non-existent user', function () {
        $authUser = User::factory()->create();

        $response = actingAs($authUser)->putJson('/api/v1/users/999999', [
            'name' => 'Test',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(404);
    });

    it('returns updated user resource', function () {
        $authUser = User::factory()->create();
        $targetUser = User::factory()->create();

        $response = actingAs($authUser)->putJson("/api/v1/users/{$targetUser->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);
    });

    it('does not return password in response', function () {
        $authUser = User::factory()->create();
        $targetUser = User::factory()->create();

        $response = actingAs($authUser)->putJson("/api/v1/users/{$targetUser->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200);

        expect($response->json('data'))->not->toHaveKey('password');
    });
});
