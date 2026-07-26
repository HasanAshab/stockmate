<?php

namespace App\DTOs;

readonly class SocialUserData
{
    public function __construct(
        public string $id,
        public string $email,
        public ?string $name,
        public bool $emailVerified,
    ) {}
}
