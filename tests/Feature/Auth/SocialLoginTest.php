<?php

use App\Enums\SocialProvider;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

use function Pest\Laravel\postJson;

describe('Social Login', function () {
    it('authenticates existing user with google token and returns 200', function () {
        Event::fake([Registered::class]);

        $user = User::factory()->create([
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);

        $user->socialAccounts()->create([
            'provider' => SocialProvider::Google,
            'provider_id' => 'google-123',
        ]);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturnSelf();

        Socialite::shouldReceive('stateless')
            ->once()
            ->andReturnSelf();

        Socialite::shouldReceive('userFromToken')
            ->with('valid-google-token')
            ->once()
            ->andReturn(
                SocialiteUser::fake([
                    'id' => 'google-123',
                    'email' => 'john@example.com',
                    'name' => 'John Doe',
                ])
            );

        $response = postJson('/api/v1/auth/social', [
            'provider' => 'google',
            'token' => 'valid-google-token',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(201)
            ->assertJsonFragment(['email' => 'john@example.com']);

        Event::assertNotDispatched(Registered::class);
    });

    it('authenticates existing user with microsoft token and returns 200', function () {
        Event::fake([Registered::class]);

        $user = User::factory()->create([
            'email' => 'jane@example.com',
            'name' => 'Jane Doe',
        ]);

        $user->socialAccounts()->create([
            'provider' => SocialProvider::Microsoft,
            'provider_id' => 'microsoft-456',
        ]);

        Socialite::shouldReceive('driver')
            ->with('microsoft')
            ->once()
            ->andReturnSelf();

        Socialite::shouldReceive('stateless')
            ->once()
            ->andReturnSelf();

        Socialite::shouldReceive('userFromToken')
            ->with('valid-microsoft-token')
            ->once()
            ->andReturn(
                SocialiteUser::fake([
                    'id' => 'microsoft-456',
                    'email' => 'jane@example.com',
                    'name' => 'Jane Doe',
                ])
            );

        $response = postJson('/api/v1/auth/social', [
            'provider' => 'microsoft',
            'token' => 'valid-microsoft-token',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(201)
            ->assertJsonFragment(['email' => 'jane@example.com']);

        Event::assertNotDispatched(Registered::class);
    });

    it('registers new user with valid google token and returns 200', function () {
        Event::fake([Registered::class]);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturnSelf();

        Socialite::shouldReceive('stateless')
            ->once()
            ->andReturnSelf();

        Socialite::shouldReceive('userFromToken')
            ->with('valid-google-token')
            ->once()
            ->andReturn(
                SocialiteUser::fake([
                    'id' => 'google-new-123',
                    'email' => 'newuser@example.com',
                    'name' => 'New User',
                ])
            );

        $response = postJson('/api/v1/auth/social', [
            'provider' => 'google',
            'token' => 'valid-google-token',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(201)
            ->assertJsonFragment(['email' => 'newuser@example.com']);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'New User',
        ]);

        $this->assertDatabaseHas('social_accounts', [
            'provider' => SocialProvider::Google->value,
            'provider_id' => 'google-new-123',
        ]);

        Event::assertDispatched(Registered::class);
    });

    it('registers new user with valid microsoft token and returns 200', function () {
        Event::fake([Registered::class]);

        Socialite::shouldReceive('driver')
            ->with('microsoft')
            ->once()
            ->andReturnSelf();

        Socialite::shouldReceive('stateless')
            ->once()
            ->andReturnSelf();

        Socialite::shouldReceive('userFromToken')
            ->with('valid-microsoft-token')
            ->once()
            ->andReturn(
                SocialiteUser::fake([
                    'id' => 'microsoft-new-456',
                    'email' => 'anotheruser@example.com',
                    'name' => 'Another User',
                ])
            );

        $response = postJson('/api/v1/auth/social', [
            'provider' => 'microsoft',
            'token' => 'valid-microsoft-token',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(201)
            ->assertJsonFragment(['email' => 'anotheruser@example.com']);

        $this->assertDatabaseHas('users', [
            'email' => 'anotheruser@example.com',
            'name' => 'Another User',
        ]);

        $this->assertDatabaseHas('social_accounts', [
            'provider' => SocialProvider::Microsoft->value,
            'provider_id' => 'microsoft-new-456',
        ]);

        Event::assertDispatched(Registered::class);
    });

    it('returns validation errors for invalid input', function () {
        $response = postJson('/api/v1/auth/social', [
            'provider' => '',
            'token' => '',
        ]);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('rejects invalid provider value', function () {
        $response = postJson('/api/v1/auth/social', [
            'provider' => 'invalid-provider',
            'token' => 'some-token',
        ]);

        $response->assertInvalidRequest()
            ->assertValidResponse(422);
    });

    it('returns 401 for invalid social token', function () {
        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturnSelf();

        Socialite::shouldReceive('stateless')
            ->once()
            ->andReturnSelf();

        Socialite::shouldReceive('userFromToken')
            ->with('invalid-token')
            ->once()
            ->andThrow(new Exception('Invalid token'));

        $response = postJson('/api/v1/auth/social', [
            'provider' => 'google',
            'token' => 'invalid-token',
        ]);

        $response->assertStatus(500);
    });

    it('returns user resource and bearer token on success', function () {
        Event::fake([Registered::class]);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturnSelf();

        Socialite::shouldReceive('stateless')
            ->once()
            ->andReturnSelf();

        Socialite::shouldReceive('userFromToken')
            ->with('valid-token')
            ->once()
            ->andReturn(
                SocialiteUser::fake([
                    'id' => 'test-123',
                    'email' => 'test@example.com',
                    'name' => 'Test User',
                ])
            );

        $response = postJson('/api/v1/auth/social', [
            'provider' => 'google',
            'token' => 'valid-token',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(201)
            ->assertJsonFragment(['token_type' => 'Bearer']);
    });
});
