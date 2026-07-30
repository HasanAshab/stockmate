<?php

namespace App\Http\Controllers;

use App\Http\Filters\FiltersDateRange;
use App\Http\Filters\FiltersMorphType;
use App\Http\Resources\ActivityLogResource;
use App\Models\Activity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityLogController extends Controller
{
    /**
     * List Activity Logs
     *
     * Get a paginated list of activity logs with filtering and sorting.
     */
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
            ->with(['causer', 'subject'])
            ->cursorPaginate(15)
            ->appends(request()->query());

        return ActivityLogResource::collection($activities);
    }
}
