<?php

namespace App\Providers;

use App\Enums\Role;
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
use App\Policies\DashboardPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role as RoleModel;
use Spatie\Permission\Models\Permission as PermissionModel;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
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

        DB::prohibitDestructiveCommands(
            app()->isProduction()
        );

        Gate::policy(DashboardPolicy::class, DashboardPolicy::class);
        Gate::policy(RoleModel::class, RolePolicy::class);
        Gate::policy(PermissionModel::class, PermissionPolicy::class);

        Gate::define('viewApiDocs', function () {
            // WARN: This is a dummy project otherwise
            // never expose API DOCS on production
            // return !app()->isProduction();

            return true;
        });

        Gate::before(function ($user, $ability) {
            return $user->hasRole(Role::SuperAdmin) ? true : null;
        });
    }
}
