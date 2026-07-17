<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use App\Models\WarehouseStock;
use App\Notifications\LowStockAlert;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Notification;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $warehouseStocks = WarehouseStock::lowStock()->limit(3)->get();

        $admins = User::whereRole(Role::Admin)->get();

        foreach ($warehouseStocks as $warehouseStock) {
            Notification::sendNow($admins, new LowStockAlert($warehouseStock));
        }

        $this->command->info('Created notifications');
    }
}
