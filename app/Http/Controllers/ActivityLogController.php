<?php

namespace App\Http\Controllers;

use App\Http\Filters\FiltersDateRange;
use App\Http\Filters\FiltersMorphType;
use App\Http\Resources\ActivityLogResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityLogController extends Controller
{
    public function index()
    {
        return QueryBuilder::for(Activity::class)
            ->allowedFilters(
                AllowedFilter::belongsTo('causer'),
                AllowedFilter::belongsTo('subject'),
                AllowedFilter::custom('subject_type', new FiltersMorphType),
                AllowedFilter::custom('created_at', new FiltersDateRange),
                AllowedFilter::groupOR('search', [
                    AllowedFilter::partial('log_name'),
                    AllowedFilter::partial('description'),
                ]),
            )
            ->allowedSorts('created_at')
            ->defaultSort('-created_at')
            ->allowedIncludes('causer', 'subject')
            ->with(['causer', 'subject'])
            ->cursorPaginate(15)
            ->appends(request()->query())
            ->toResourceCollection(ActivityLogResource::class);
    }
}
