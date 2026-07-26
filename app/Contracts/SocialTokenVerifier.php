<?php

namespace App\Contracts;

use App\DTOs\SocialUserData;

interface SocialTokenVerifier
{
    public function resolve(string $token): SocialUserData;
}
