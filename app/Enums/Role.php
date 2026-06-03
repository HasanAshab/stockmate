<?php

namespace App\Enums;

enum Role: int
{
    case Staff = 1;
    case Admin = 2;

    public static function toArray(): array
    {
        return [
            [
                'id' => self::Staff->value,
                'name' => self::Staff->name,
            ],
            [
                'id' => self::Admin->value,
                'name' => self::Admin->name,
            ],
        ];
    }

    public function isStaff(): bool
    {
        return $this === self::Staff;
    }

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }
}
