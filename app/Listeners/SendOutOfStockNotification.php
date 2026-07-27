<?php

namespace App\Listeners;

use App\Events\ProductLowStock;
use App\Notifications\ProductOutOfStockNotification;
use Illuminate\Support\Facades\Notification;

class SendOutOfStockNotification
{
    public function handle(ProductLowStock $event): void
    {
        Notification::send(
            ProductOutOfStockNotification::recipients(),
            new ProductOutOfStockNotification($event->warehouseStock),
        );
    }
}
