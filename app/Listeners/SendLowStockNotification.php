<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Notification;
use App\Enums\Role;
use App\Events\ProductStockLow;
use App\Models\User;
use App\Notifications\LowStockAlert;

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
