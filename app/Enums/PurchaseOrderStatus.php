<?php

namespace App\Enums;

enum PurchaseOrderStatus: int
{
    case Draft = 1;
    case Ordered = 2;
    case PartiallyReceived = 3;
    case Received = 4;
    case Cancelled = 5;
}
