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
        return Cache::flexible('dashboard:metrics', [300, 600], fn () => [
            'data' => [
                'total_products' => Product::count(),
                'total_low_stock' => WarehouseStock::lowStock()->count(),
                'total_categories' => Category::count(),
                'total_suppliers' => Supplier::count(),
                'recent_stock_logs' => StockLog::latest()
                    ->take(5)
                    ->with(['user', 'product'])
                    ->get()
                    ->toResourceCollection()
                    ->resolve(),
            ]
        ]);
    }
}
