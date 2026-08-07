<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{

    case Draft = 'draft';

    case Ordered = 'ordered';

    case PartiallyReceived = 'partially_received';

    case Received = 'received';

    case Cancelled = 'cancelled';

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
