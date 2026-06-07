<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\Supplier;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return Cache::remember('dashboard:metrics', now()->addMinutes(5), function () {
            $dashboard = [];

            $dashboard['total_products'] = Product::count();
            $dashboard['total_low_stock'] = WarehouseStock::lowStock()->count();
            $dashboard['total_categories'] = Category::count();
            $dashboard['total_suppliers'] = Supplier::count();
            $dashboard['recent_stock_logs'] = StockLog::latest()
                ->limit(5)
                ->with(['user', 'product'])
                ->get()
                ->toArray();

            return ['data' => $dashboard];
        });
    }
}
