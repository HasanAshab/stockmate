<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Gate;

class DashboardPolicy
{
    public function viewAny(User $user): bool
    {
        return collect([
            Product::class,
            Warehouse::class,
            Category::class,
            Supplier::class,
            StockLog::class,
        ])->every(
            fn ($model) => Gate::forUser($user)->allows('viewAny', $model)
        );
    }
}
