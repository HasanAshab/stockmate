<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class SalesOrderSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = Warehouse::all();
        $users = User::all();
        $products = Product::all();

        // Create pending sales orders
        $pendingSalesOrders = SalesOrder::factory()
            ->count(8)
            ->pending()
            ->recycle([$warehouses, $users])
            ->create();

        foreach ($pendingSalesOrders as $so) {
            $items = SalesOrderItem::factory()
                ->count(rand(1, 4))
                ->for($so)
                ->recycle($products)
                ->create();

            $so->update([
                'total_amount' => $items->sum(fn ($item) => $item->quantity * $item->unit_price),
            ]);
        }

        // Create processing sales orders
        $failedSalesOrders = SalesOrder::factory()
            ->count(7)
            ->failed()
            ->recycle([$warehouses, $users])
            ->create();

        foreach ($failedSalesOrders as $so) {
            $items = SalesOrderItem::factory()
                ->count(rand(1, 3))
                ->for($so)
                ->recycle($products)
                ->create();

            $so->update([
                'total_amount' => $items->sum(fn ($item) => $item->quantity * $item->unit_price),
            ]);
        }

        // Create paid sales orders
        $paidSalesOrders = SalesOrder::factory()
            ->count(25)
            ->paid()
            ->recycle([$warehouses, $users])
            ->create();

        foreach ($paidSalesOrders as $so) {
            $items = SalesOrderItem::factory()
                ->count(rand(2, 6))
                ->for($so)
                ->recycle($products)
                ->create();

            $so->update([
                'total_amount' => $items->sum(fn ($item) => $item->quantity * $item->unit_price),
            ]);
        }

        // Create cancelled sales orders
        $cancelledSalesOrders = SalesOrder::factory()
            ->count(5)
            ->cancelled()
            ->recycle([$warehouses, $users])
            ->create();

        foreach ($cancelledSalesOrders as $so) {
            $items = SalesOrderItem::factory()
                ->count(rand(1, 3))
                ->for($so)
                ->recycle($products)
                ->create();

            $so->update([
                'total_amount' => $items->sum(fn ($item) => $item->quantity * $item->unit_price),
            ]);
        }

        $this->command->info('Created 45 sales orders (8 pending, 25 paid, 7 failed, 5 cancelled)');
    }
}
