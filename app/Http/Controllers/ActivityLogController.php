<?php

namespace App\Http\Controllers;

use App\Http\Filters\FiltersDateRange;
use App\Http\Filters\FiltersMorphType;
use App\Http\Resources\ActivityLogResource;
use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Activity Log
 *
 * APIs for viewing system activity logs
 *
 * @authenticated
 */
class ActivityLogController extends Controller
{
    /**
     * List Activity Logs
     *
     * Get a paginated list of activity logs with filtering and sorting.
     *
     * @queryParam filter[causer] Filter by user ID who caused the activity. Example: 1
     * @queryParam filter[subject] Filter by subject ID. Example: 1
     * @queryParam filter[subject_type] Filter by subject type (e.g., Product, User). Example: Product
     * @queryParam filter[created_at] Filter by date range (format: start,end). Example: 2026-01-01,2026-01-31
     * @queryParam sort Sort by field. Use - prefix for descending. Example: -created_at
     * @queryParam include Include relationships (causer, subject). Example: causer,subject
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "log_name": "default",
     *       "description": "created",
     *       "subject_type": "Product",
     *       "subject_id": 1,
     *       "done_by": "John Doe",
     *       "changes": {
     *         "name": "Laptop",
     *         "price": 999.99
     *       },
     *       "old_values": null,
     *       "created_at": "2026-01-15T10:00:00.000000Z"
     *     }
     *   ],
     *   "meta": {},
     *   "links": {}
     * }
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
