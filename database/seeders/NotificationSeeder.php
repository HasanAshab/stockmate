<?php

namespace Database\Seeders;

use App\Enums\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Models\WarehouseStock;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $warehouseStocks = WarehouseStock::lowStock()->limit(3)->get();

        $admins = User::whereRole(Role::Admin)->get();

        foreach ($warehouseStocks as $warehouseStock) {
            Notification::sendNow($admins, new LowStockAlert($warehouseStock));
        }

        $this->command->info("Created notifications");
    }
}
