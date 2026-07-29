<?php

use function Pest\Laravel\postJson;

describe('Social Login', function () {
    it('authenticates existing user with google token and returns 200', function () {
        $this->markTestSkipped('Requires Google OAuth token mocking setup');
    });

    it('authenticates existing user with microsoft token and returns 200', function () {
        $this->markTestSkipped('Requires Microsoft OAuth token mocking setup');
    });

    it('registers new user with valid google token and returns 200', function () {
        $this->markTestSkipped('Requires Google OAuth token mocking setup');
    });

    it('registers new user with valid microsoft token and returns 200', function () {
        $this->markTestSkipped('Requires Microsoft OAuth token mocking setup');
    });

    it('returns validation errors for invalid input', function () {
        $response = postJson('/api/v1/auth/social', [
            'provider' => '',
            'token' => '',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('rejects invalid provider value', function () {
        $response = postJson('/api/v1/auth/social', [
            'provider' => 'invalid-provider',
            'token' => 'some-token',
        ]);

        $response->assertValidRequest()
            ->assertValidResponse(422);
    });

    it('returns 401 for invalid social token', function () {
        $this->markTestSkipped('Requires social provider token mocking setup');
    });

    it('returns user resource and bearer token on success', function () {
        $this->markTestSkipped('Requires social provider token mocking setup');
    });
});
