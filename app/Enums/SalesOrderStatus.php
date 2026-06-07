<?php

namespace App\Enums;

use App\Enums\Concerns\EnumToArray;
use Illuminate\Contracts\Support\Arrayable;

enum SalesOrderStatus: int implements \JsonSerializable, Arrayable
{
    use EnumToArray;

    case Pending = 1;

    case Paid = 2;

    case Failed = 3;

    case Cancelled = 4;

    public function isPending(): bool
    {
        return $this === self::Pending;
    }

    public function isPaid(): bool
    {
        return $this === self::Paid;
    }

    public function isFailed(): bool
    {
        return $this === self::Failed;
    }

    public function isCancelled(): bool
    {
        return $this === self::Cancelled;
    }
}
