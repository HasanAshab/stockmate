<?php

namespace App\Providers;

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
            'user'                => \App\Models\User::class,
            'category'            => \App\Models\Category::class,
            'product'             => \App\Models\Product::class,
            'sales-order'         => \App\Models\SalesOrder::class,
            'sales-order-item'    => \App\Models\SalesOrderItem::class,
            'purchase-order'      => \App\Models\PurchaseOrder::class,
            'purchase-order-item' => \App\Models\PurchaseOrderItem::class,
            'stock-log'           => \App\Models\StockLog::class,
            'supplier'            => \App\Models\Supplier::class,
            'warehouse'           => \App\Models\Warehouse::class,
            'warehouse-stock'     => \App\Models\WarehouseStock::class,
        ]);
    }
}
