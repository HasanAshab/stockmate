<?php

namespace App\Http\Controllers;

use App\Actions\Product\CreateStockLog;
use App\Http\Requests\Product\StoreStockLogRequest;
use App\Models\StockLog;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StockLogController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', StockLog::class);

        return QueryBuilder::for(StockLog::class)
            ->allowedFilters(
                AllowedFilter::belongsTo('product'),
                AllowedFilter::belongsTo('user'),
                AllowedFilter::exact('type'),
                AllowedFilter::callback('from', function ($query, $value) {
                    $query->where('created_at', '>=', $value);
                }),
                AllowedFilter::callback('to', function ($query, $value) {
                    $query->where('created_at', '<=', $value);
                }),
            )
            ->allowedSorts(
                'created_at',
                'quantity',
                'unit_cost',
            )
            ->allowedIncludes('user', 'product')
            ->defaultSort('-created_at')
            ->paginate(10)
            ->appends(request()->query())
            ->toResourceCollection();
    }

    public function store(StoreStockLogRequest $request, CreateStockLog $createStockLog)
    {
        Gate::authorize('create', StockLog::class);

        $stockLog = $createStockLog->execute(
            $request->user(),
            $request->validated(),
        );

        return $stockLog->toResource()
            ->response()
            ->setStatusCode(201);
    }
}