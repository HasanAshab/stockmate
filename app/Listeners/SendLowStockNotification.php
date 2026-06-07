<?php

namespace App\Listeners;

use App\Enums\Role;
use App\Events\WarehouseStockLow;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Support\Facades\Notification;

class SendLowStockNotification
{
    public $afterCommit = true;

    public function handle(WarehouseStockLow $event): void
    {
        $admins = User::whereRole(Role::Admin)->get();
        Notification::send($admins, new LowStockAlert($event->warehouseStock));
    }
}
