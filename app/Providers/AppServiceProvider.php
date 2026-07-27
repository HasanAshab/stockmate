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
use App\Services\Social\SocialAuthManager;
use Google\Client as GoogleClient;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role as RoleModel;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GoogleClient::class, function () {
            $client = new GoogleClient;
            $client->setClientId(config('services.google.client_id'));

            return $client;
        });

        $this->app->singleton(SocialAuthManager::class, function ($app) {
            return new SocialAuthManager($app);
        });
    }

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

        Gate::before(function ($user, $ability) {
            return $user->hasRole(Role::SuperAdmin) ? true : null;
        });
    }
}
