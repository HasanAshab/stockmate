<?php

namespace App\Http\Controllers;

use App\Http\Filters\FiltersDateRange;
use App\Http\Filters\FiltersMorphType;
use App\Http\Resources\ActivityLogResource;
use Dedoc\Scramble\Attributes\QueryParameter;
use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityLogController extends Controller
{
    /**
     * List Activity Logs
     *
     * Get a paginated list of activity logs with filtering and sorting.
     */
    #[QueryParameter(name: 'filter[causer]', description: 'Filter by user who caused the activity. Pass user ID. Example: `filter[causer]=1`', example: 1)]
    #[QueryParameter(name: 'filter[subject]', description: 'Filter by subject entity. Pass the subject ID. Example: `filter[subject]=5`', example: 5)]
    #[QueryParameter(name: 'filter[subject_type]', description: 'Filter by subject type morph alias. Example: `filter[subject_type]=product`', example: 'product')]
    #[QueryParameter(name: 'filter[created_at][from]', description: 'Filter activities created from this date. Format: `YYYY-MM-DD` or ISO 8601. Example: `filter[created_at][from]=2024-01-01`', example: '2024-01-01')]
    #[QueryParameter(name: 'filter[created_at][to]', description: 'Filter activities created until this date. Format: `YYYY-MM-DD` or ISO 8601. Example: `filter[created_at][to]=2024-12-31`', example: '2024-12-31')]
    #[QueryParameter(name: 'sort', description: 'Sort by field. Prefix with `-` for descending. Allowed: `created_at`. Example: `sort=-created_at`', example: '-created_at')]
    #[QueryParameter(name: 'include', description: 'Relationships to include. Allowed: `causer`, `subject`. Comma-separated. Example: `include=causer,subject`', example: 'causer,subject')]
    public function index()
    {
        $activities = QueryBuilder::for(Activity::class)
            ->allowedFilters(
                AllowedFilter::belongsTo('causer'),
                AllowedFilter::belongsTo('subject'),
                AllowedFilter::custom('subject_type', new FiltersMorphType),
                AllowedFilter::custom('created_at', new FiltersDateRange),
            )
            ->allowedSorts('created_at')
            ->defaultSort('-created_at')
            ->allowedIncludes('causer', 'subject')
            ->with('causer')
            ->cursorPaginate(15)
            ->appends(request()->query());

        return ActivityLogResource::collection($activities);
    }
}
