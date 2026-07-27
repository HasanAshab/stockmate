<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\GetDashboardMetrics;
use App\Policies\DashboardPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * @group Dashboard
 *
 * APIs for dashboard metrics and analytics
 *
 * @authenticated
 */
class DashboardController extends Controller
{
    /**
     * Get Dashboard Metrics
     *
     * Get key metrics for the dashboard including product counts, low stock alerts, and recent activity.
     *
     * @response 200 {
     *   "data": {
     *     "total_products": 150,
     *     "total_low_stock": 5,
     *     "total_categories": 10,
     *     "total_suppliers": 8,
     *     "recent_stock_logs": [
     *       {
     *         "id": 1,
     *         "type": "in",
     *         "quantity": 50,
     *         "product": {
     *           "name": "Laptop"
     *         }
     *       }
     *     ]
     *   }
     * }
     */
    public function __invoke(GetDashboardMetrics $metrics)
    {
        Gate::authorize('viewAny', DashboardPolicy::class);

        return ['data' => $metrics->execute()];
    }
}
