<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\StockLog;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\Product;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $dashboard = [];

        $dashboard['total_products'] = Product::count();
        $dashboard['total_low_stock'] = Product::lowStock()->count();
        $dashboard['total_categories'] = Category::count();
        $dashboard['total_suppliers'] = Supplier::count();
        $dashboard['recent_stock_logs'] = StockLog::latest()
            ->limit(5)
            ->with(['user', 'product'])
            ->get();

        return ["data" => $dashboard];
    }
}
