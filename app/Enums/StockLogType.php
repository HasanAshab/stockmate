<?php

namespace App\Enums;

enum StockLogType: int
{
    case In = 1;
    case Out = 2;

    public static function toArray(): array
    {
        return [
            [
                'id' => self::In->value,
                'name' => self::In->name,
            ],
            [
                'id' => self::Out->value,
                'name' => self::Out->name,
            ],
        ];
    }

    public function isIn(): bool
    {
        return $this === self::In;
    }

    public function isOut(): bool
    {
        return $this === self::Out;
    }
}
