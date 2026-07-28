<?php

namespace App\Http\Controllers;

use App\Http\Filters\FiltersDateRange;
use App\Http\Filters\FiltersMorphType;
use App\Http\Resources\ActivityLogResource;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

#[Group('Activity Log', 'APIs for viewing system activity logs')]
#[Authenticated]
class ActivityLogController extends Controller
{
    /**
     * List Activity Logs
     *
     * Get a paginated list of activity logs with filtering and sorting.
     */
    #[QueryParam('filter[causer]', 'integer', 'Filter by user ID who caused the activity.', example: 1)]
    #[QueryParam('filter[subject]', 'integer', 'Filter by subject ID.', example: 1)]
    #[QueryParam('filter[subject_type]', 'string', 'Filter by subject type.', example: 'Product')]
    #[QueryParam('filter[created_at]', 'string', 'Filter by date range (format: start,end).', example: '2026-01-01,2026-01-31')]
    #[QueryParam('sort', 'string', 'Sort by field. Use - prefix for descending.', example: '-created_at')]
    #[QueryParam('include', 'string', 'Include relationships.', example: 'causer,subject')]
    #[ResponseFromApiResource(ActivityLogResource::class, Activity::class, collection: true, paginate: 15)]
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
