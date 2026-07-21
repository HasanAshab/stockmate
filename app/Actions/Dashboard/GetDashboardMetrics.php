<?php

namespace App\Actions\Dashboard;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\Supplier;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\Cache;

class GetDashboardMetrics
{
    public function execute(): array
    {
        return Cache::flexible('dashboard:metrics', [300, 600], fn () => [
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
        ]);
    }
}