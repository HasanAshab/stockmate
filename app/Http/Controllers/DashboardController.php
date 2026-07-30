<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\GetDashboardMetrics;
use App\Policies\DashboardPolicy;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    /**
     * Get Dashboard Metrics
     *
     * Get key metrics for the dashboard including product counts, low stock alerts, and recent activity.
     */
    public function __invoke(GetDashboardMetrics $metrics)
    {
        Gate::authorize('viewAny', DashboardPolicy::class);

        return ['data' => $metrics->execute()];
    }
}
