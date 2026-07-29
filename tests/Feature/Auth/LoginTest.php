<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\postJson;

describe('Login', function () {
    it('authenticates user with email and returns 200 with token', function () {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = postJson('/api/v1/auth/login', [
            'identifier' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['email' => 'john@example.com']);
    });

    it('authenticates user with phone and returns 200 with token', function () {
        User::factory()->create([
            'phone' => '+8801712345678',
            'password' => Hash::make('Password123!'),
        ]);

        $response = postJson('/api/v1/auth/login', [
            'identifier' => '+8801712345678',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['phone' => '+8801712345678']);
    });

    it('returns validation errors for invalid input', function () {
        $response = postJson('/api/v1/auth/login', [
            'identifier' => '',
            'password' => '',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('returns 401 for non-existent user', function () {
        $response = postJson('/api/v1/auth/login', [
            'identifier' => 'nonexistent@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns 401 for incorrect password', function () {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('CorrectPassword123!'),
        ]);

        $response = postJson('/api/v1/auth/login', [
            'identifier' => 'john@example.com',
            'password' => 'WrongPassword123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(401);
    });

    it('returns user resource and bearer token on success', function () {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = postJson('/api/v1/auth/login', [
            'identifier' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(200)
            ->assertJsonFragment(['token_type' => 'Bearer']);
    });
});
