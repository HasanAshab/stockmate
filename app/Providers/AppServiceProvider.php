<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockLog;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('viewApiDocs', function () {
            // WARN: This is a dummy project otherwise
            // never expose API DOCS on production
            // return !app()->isProduction();

            return true;
        });

        DB::prohibitDestructiveCommands(
            app()->isProduction()
        );

        Relation::enforceMorphMap([
            'user' => User::class,
            'category' => Category::class,
            'product' => Product::class,
            'sales-order' => SalesOrder::class,
            'sales-order-item' => SalesOrderItem::class,
            'purchase-order' => PurchaseOrder::class,
            'purchase-order-item' => PurchaseOrderItem::class,
            'stock-log' => StockLog::class,
            'supplier' => Supplier::class,
            'warehouse' => Warehouse::class,
            'warehouse-stock' => WarehouseStock::class,
        ]);
    }
}
