<?php

namespace App\Enums;

use App\Enums\Concerns\EnumToArray;

enum Role: int implements \JsonSerializable
{
    use EnumToArray;

    case Staff = 1;

    case Admin = 2;

    public function isStaff(): bool
    {
        return $this === self::Staff;
    }

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }
}
