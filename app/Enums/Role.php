<?php

namespace App\Enums;

enum Role: int
{
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
