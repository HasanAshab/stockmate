<?php

namespace App\Http\Controllers;

use App\Exports\StockLogExport;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Maatwebsite\Excel\Facades\Excel;
use App\Actions\Product\CreateStockLog;
use App\Enums\StockLogExportFormat;
use App\Http\Requests\Product\ExportStockLogRequest;
use App\Http\Requests\Product\StoreStockLogRequest;
use App\Models\StockLog;

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

    public function export(ExportStockLogRequest $request, StockLogExportFormat $format)
    {
        Gate::authorize('viewAny', StockLog::class);

        $from = $request->validated('from');
        $to = $request->validated('to');
        $dateSlug  =  match (true) {
            $from && $to => "from_{$from}_to_{$to}",
            $from => "from_{$from}",
            $to => "until_{$to}",
            default => 'all',
        };

        $fileName = "stock-logs-{$dateSlug}.{$format->extension()}";
        return Excel::download(new StockLogExport($from, $to), $fileName, $format->contentType());
    }
}