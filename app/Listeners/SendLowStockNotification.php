<?php

namespace App\Listeners;

use App\Enums\Role;
use App\Events\WarehouseStockLow;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Support\Facades\Notification;

class SendLowStockNotification
{
    public function handle(WarehouseStockLow $event): void
    {
        $admins = User::role(Role::Administrator)->get();
        Notification::send($admins, new LowStockAlert($event->warehouseStock));
    }
}
