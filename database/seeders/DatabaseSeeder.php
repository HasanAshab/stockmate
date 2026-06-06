<?php

namespace Database\Seeders;

use App\Enums\PurchaseOrderStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create users: 3 admins + 16 staff
        $admins = collect([
            User::factory()->admin()->create([
                'email' => 'admin1@example.com',
                'name' => 'Admin User One',
            ]),
            User::factory()->admin()->create([
                'email' => 'admin2@example.com',
                'name' => 'Admin User Two',
            ]),
            User::factory()->admin()->create([
                'email' => 'admin3@example.com',
                'name' => 'Admin User Three',
            ]),
        ]);

        $demoStaff = User::factory()->staff()->create([
            'email' => 'staff1@example.com',
            'name' => 'Staff User One',
        ]);
        $staffs = User::factory()->count(15)->staff()->create();
        $allUsers = $admins->merge($staffs->push($demoStaff));

        // Create core inventory data
        $categories = Category::factory()->count(10)->create();
        $suppliers = Supplier::factory()->count(8)->create();
        $warehouses = Warehouse::factory()->count(5)->create();

        // Create products
        $products = Product::factory()->count(50)->recycle([
            $categories,
            $suppliers,
        ])->create();

        // Create warehouse stock for products
        foreach ($products as $product) {
            $warehousesForProduct = $warehouses->random(rand(1, 3));
            
            foreach ($warehousesForProduct as $warehouse) {
                WarehouseStock::factory()->create([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                ]);
            }
        }

        // Create some low-stock items (only if not already created)
        $existingStockIds = WarehouseStock::pluck('product_id')->toArray();
        $availableProducts = $products->whereNotIn('id', $existingStockIds);
        
        if ($availableProducts->count() >= 5) {
            WarehouseStock::factory()->count(5)->low()->recycle([
                $warehouses,
                $availableProducts,
            ])->create();
        }

        // Create purchase orders
        $draftPurchaseOrders = PurchaseOrder::factory()
            ->count(5)
            ->draft()
            ->recycle([$suppliers, $warehouses, $allUsers])
            ->create();

        foreach ($draftPurchaseOrders as $po) {
            PurchaseOrderItem::factory()
                ->count(rand(2, 5))
                ->for($po)
                ->recycle($products)
                ->create();
        }

        $orderedPurchaseOrders = PurchaseOrder::factory()
            ->count(10)
            ->ordered()
            ->recycle([$suppliers, $warehouses, $allUsers])
            ->create();

        foreach ($orderedPurchaseOrders as $po) {
            PurchaseOrderItem::factory()
                ->count(rand(3, 8))
                ->for($po)
                ->recycle($products)
                ->create();
        }

        $receivedPurchaseOrders = PurchaseOrder::factory()
            ->count(15)
            ->received()
            ->recycle([$suppliers, $warehouses, $allUsers])
            ->create();

        foreach ($receivedPurchaseOrders as $po) {
            PurchaseOrderItem::factory()
                ->count(rand(3, 8))
                ->fullyReceived()
                ->for($po)
                ->recycle($products)
                ->create();
        }

        // Create sales orders
        $pendingSalesOrders = SalesOrder::factory()
            ->count(8)
            ->pending()
            ->recycle([$warehouses, $allUsers])
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

        $paidSalesOrders = SalesOrder::factory()
            ->count(20)
            ->paid()
            ->recycle([$warehouses, $allUsers])
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

        $cancelledSalesOrders = SalesOrder::factory()
            ->count(5)
            ->cancelled()
            ->recycle([$warehouses, $allUsers])
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
    }
}
