<?php

namespace App\Enums;

enum SalesOrderStatus: int
{
    case Pending = 1;
    case Paid = 2;
    case Failed = 3;
    case Cancelled = 4;

    public static function toArray(): array
    {
        return [
            [
                'id' => self::Pending->value,
                'name' => self::Pending->name,
            ],
            [
                'id' => self::Paid->value,
                'name' => self::Paid->name,
            ],
            [
                'id' => self::Failed->value,
                'name' => self::Failed->name,
            ],
            [
                'id' => self::Cancelled->value,
                'name' => self::Cancelled->name,
            ],
        ];
    }

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