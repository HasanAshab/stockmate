<?php

namespace App\Enums;

use App\Enums\Concerns\EnumToArray;

enum PurchaseOrderStatus: int implements \JsonSerializable
{
    use EnumToArray;

    case Draft = 1;

    case Ordered = 2;

    case PartiallyReceived = 3;

    case Received = 4;

    case Cancelled = 5;

    public function isDraft(): bool
    {
        return $this === self::Draft;
    }

    public function isOrdered(): bool
    {
        return $this === self::Ordered;
    }

    public function isPartiallyReceived(): bool
    {
        return $this === self::PartiallyReceived;
    }

    public function isReceived(): bool
    {
        return $this === self::Received;
    }

    public function isCancelled(): bool
    {
        return $this === self::Cancelled;
    }
}
