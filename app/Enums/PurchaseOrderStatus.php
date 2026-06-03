<?php

namespace App\Enums;

enum PurchaseOrderStatus: int
{
    case Draft = 1;
    case Ordered = 2;
    case PartiallyReceived = 3;
    case Received = 4;
    case Cancelled = 5;

    public static function toArray(): array
    {
        return [
            [
                'id' => self::Draft->value,
                'name' => self::Draft->name,
            ],
            [
                'id' => self::Ordered->value,
                'name' => self::Ordered->name,
            ],
            [
                'id' => self::PartiallyReceived->value,
                'name' => self::PartiallyReceived->name,
            ],
            [
                'id' => self::Received->value,
                'name' => self::Received->name,
            ],
            [
                'id' => self::Cancelled->value,
                'name' => self::Cancelled->name,
            ],
        ];
    }

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
