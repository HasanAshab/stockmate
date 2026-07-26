<?php

namespace App\Services\Social;

use Illuminate\Support\Manager;

class SocialAuthManager extends Manager
{
    public function getDefaultDriver(): never
    {
        throw new \InvalidArgumentException('No default social auth driver specified.');
    }

    public function createGoogleDriver(): GoogleTokenVerifier
    {
        return $this->container->make(GoogleTokenVerifier::class);
    }

    public function createMicrosoftDriver(): MicrosoftTokenVerifier
    {
        return $this->container->make(MicrosoftTokenVerifier::class);
    }
}
