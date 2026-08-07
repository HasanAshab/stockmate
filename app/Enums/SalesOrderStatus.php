<?php

namespace App\Enums;


enum SalesOrderStatus: string
{
    case Pending = 'pending';

    case Paid = 'paid';

    case Failed = 'failed';

    case Cancelled = 'cancelled';

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
