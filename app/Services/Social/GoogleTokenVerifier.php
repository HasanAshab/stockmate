<?php

namespace App\Services\Social;

use App\Contracts\SocialTokenVerifier;
use App\DTOs\SocialUserData;
use Google\Client;
use Illuminate\Validation\ValidationException;

class GoogleTokenVerifier implements SocialTokenVerifier
{
    public function __construct(
        protected Client $client
    ) {}

    public function resolve(string $token): SocialUserData
    {
        $payload = $this->client->verifyIdToken($token);

        if (! $payload) {
            throw ValidationException::withMessages([
                'token' => ['Invalid Google token.'],
            ]);
        }

        $email = $payload['email'] ?? null;

        if (! $email) {
            throw ValidationException::withMessages([
                'token' => ['Your Google account must have an email address.'],
            ]);
        }

        return new SocialUserData(
            id: $payload['sub'],
            email: $email,
            name: $payload['name'] ?? null,
            emailVerified: $payload['email_verified'] ?? false,
        );
    }
}
