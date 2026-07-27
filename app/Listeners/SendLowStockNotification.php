<?php

namespace App\Listeners;

use App\Events\WarehouseStockLow;
use App\Notifications\LowStockAlert;
use Illuminate\Support\Facades\Notification;

class SendLowStockNotification
{
    public function handle(WarehouseStockLow $event): void
    {
        Notification::send(
            LowStockAlert::recipients(),
            new LowStockAlert($event->warehouseStock),
        );
    }
}
