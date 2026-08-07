<?php

namespace App\Enums;

enum StockLogType: string
{
    case In = 'in';

    case Out = 'out';

    public function isIn(): bool
    {
        return $this === self::In;
    }

    public function isOut(): bool
    {
        return $this === self::Out;
    }
}
