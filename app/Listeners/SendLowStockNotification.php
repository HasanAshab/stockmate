<?php

namespace App\Listeners;

use App\Enums\Role;
use App\Events\ProductStockLow;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Support\Facades\Notification;

class SendLowStockNotification
{
    public $afterCommit = true;

    public function handle(ProductStockLow $event): void
    {
        $admins = User::query()
            ->where('role', Role::Admin)
            ->get();

        Notification::send($admins, new LowStockAlert($event->product));
    }
}
