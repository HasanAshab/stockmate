<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        $users = User::all();
        $products = Product::all();

        // Create draft purchase orders
        $draftPurchaseOrders = PurchaseOrder::factory()
            ->count(5)
            ->draft()
            ->recycle([$suppliers, $warehouses, $users])
            ->create();

        foreach ($draftPurchaseOrders as $po) {
            PurchaseOrderItem::factory()
                ->count(rand(2, 5))
                ->for($po)
                ->recycle($products)
                ->create();
        }

        // Create ordered purchase orders
        $orderedPurchaseOrders = PurchaseOrder::factory()
            ->count(10)
            ->ordered()
            ->recycle([$suppliers, $warehouses, $users])
            ->create();

        foreach ($orderedPurchaseOrders as $po) {
            PurchaseOrderItem::factory()
                ->count(rand(3, 8))
                ->for($po)
                ->recycle($products)
                ->create();
        }

        // Create received purchase orders
        $receivedPurchaseOrders = PurchaseOrder::factory()
            ->count(15)
            ->received()
            ->recycle([$suppliers, $warehouses, $users])
            ->create();

        foreach ($receivedPurchaseOrders as $po) {
            PurchaseOrderItem::factory()
                ->count(rand(3, 8))
                ->fullyReceived()
                ->for($po)
                ->recycle($products)
                ->create();
        }

        $this->command->info('Created 30 purchase orders (5 draft, 10 ordered, 15 received)');
    }
}
