<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\GetDashboardMetrics;
use App\Policies\DashboardPolicy;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Dashboard', 'APIs for dashboard metrics and analytics')]
#[Authenticated]
class DashboardController extends Controller
{
    /**
     * Get Dashboard Metrics
     *
     * Get key metrics for the dashboard including product counts, low stock alerts, and recent activity.
     */
    #[Response(['data' => ['total_products' => 150, 'total_low_stock' => 5, 'total_categories' => 10, 'total_suppliers' => 8, 'recent_stock_logs' => []]], 200)]
    public function __invoke(GetDashboardMetrics $metrics)
    {
        Gate::authorize('viewAny', DashboardPolicy::class);

        return ['data' => $metrics->execute()];
    }
}
