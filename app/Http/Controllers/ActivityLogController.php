<?php

namespace App\Http\Controllers;

use App\Http\Filters\FiltersDateRange;
use App\Http\Resources\ActivityLogResource;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityLogController extends Controller
{
    public function index()
    {
        return QueryBuilder::for(Activity::class)
            ->allowedFilters(
                AllowedFilter::callback('subject_type', function ($query, $value) {
                    $class = 'App\\Models\\'.Str::studly($value);
                    abort_unless(class_exists($class), 422, 'Invalid subject type.');
                    $query->where('subject_type', $class);
                }),
                AllowedFilter::exact('causer_id'),
                AllowedFilter::custom('created_at', new FiltersDateRange)
            )
            ->allowedSorts('created_at')
            ->defaultSort('-created_at')
            ->allowedIncludes('causer', 'subject')
            ->with(['causer', 'subject'])
            ->paginate(15)
            ->appends(request()->query())
            ->toResourceCollection(ActivityLogResource::class);
    }
}
