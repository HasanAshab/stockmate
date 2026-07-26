<?php

namespace App\Services\Social;

use App\Contracts\SocialTokenVerifier;
use App\DTOs\SocialUserData;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class MicrosoftTokenVerifier implements SocialTokenVerifier
{
    public function resolve(string $token): SocialUserData
    {
        $response = Http::withToken($token)
            ->timeout(10)
            ->connectTimeout(5)
            ->get('https://graph.microsoft.com/v1.0/me');

        if ($response->failed()) {
            throw ValidationException::withMessages([
                'token' => ['Invalid Microsoft token.'],
            ]);
        }

        $data = $response->json();

        $email = $data['mail'] ?? $data['userPrincipalName'] ?? null;

        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'token' => ['Your Microsoft account must have a valid email address.'],
            ]);
        }

        return new SocialUserData(
            id: $data['id'],
            email: $email,
            name: $data['displayName'] ?? null,
            emailVerified: true,
        );
    }
}
