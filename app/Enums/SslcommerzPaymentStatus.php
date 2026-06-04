<?php

namespace App\Enums;

enum SslcommerzPaymentStatus: string
{
    case Valid = 'VALID';
    case Validated = 'VALIDATED';
    case Failed = 'FAILED';
    case Cancelled = 'CANCELLED';

    public static function isValid(string $status): bool
    {
        return \in_array($status, [
            self::Valid->value,
            self::Validated->value,
        ], true);
    }
}
