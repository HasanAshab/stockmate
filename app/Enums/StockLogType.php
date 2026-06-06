<?php

namespace App\Enums;

use App\Enums\Concerns\EnumToArray;

enum StockLogType: int implements \JsonSerializable
{
    use EnumToArray;

    case In = 1;

    case Out = 2;

    public function isIn(): bool
    {
        return $this === self::In;
    }

    public function isOut(): bool
    {
        return $this === self::Out;
    }
}
