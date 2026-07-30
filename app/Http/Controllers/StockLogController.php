<?php

namespace App\Http\Controllers;

use App\Actions\Product\CreateStockLog;
use App\Actions\StockLog\ExportStockLog;
use App\Actions\StockLog\TransferStock;
use App\Enums\StockLogExportFormat;
use App\Http\Filters\FiltersDateRange;
use App\Http\Requests\Product\ExportStockLogRequest;
use App\Http\Requests\Product\StoreStockLogRequest;
use App\Http\Requests\Product\TransferStockRequest;
use App\Http\Resources\StockLogResource;
use App\Models\StockLog;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

class StockLogController extends Controller
{
    /**
     * List Stock Logs
     *
     * Get a paginated list of stock movement logs with filtering and sorting.
     */
    public function index()
    {
        Gate::authorize('viewAny', StockLog::class);

        $stockLogs = QueryBuilder::for(StockLog::class)
            ->allowedFilters(
                AllowedFilter::belongsTo('product'),
                AllowedFilter::belongsTo('user'),
                AllowedFilter::belongsTo('warehouse'),
                AllowedFilter::exact('type'),
                AllowedFilter::operator('quantity', FilterOperator::DYNAMIC),
                AllowedFilter::operator('unit_cost', FilterOperator::DYNAMIC),
                AllowedFilter::custom('created_at', new FiltersDateRange)
            )
            ->allowedSorts(
                'created_at',
                'quantity',
                'unit_cost',
            )
            ->allowedIncludes('user', 'product', 'warehouse')
            ->defaultSort('-created_at')
            ->cursorPaginate(10)
            ->appends(request()->query());

        return StockLogResource::collection($stockLogs);
    }

    /**
     * Create Stock Log
     *
     * Create a new stock movement log (in, out, or adjustment).
     */
    public function store(StoreStockLogRequest $request, CreateStockLog $createStockLog)
    {
        Gate::authorize('create', StockLog::class);

        $stockLog = $createStockLog->execute(
            $request->user(),
            $request->validated(),
        );

        return (new StockLogResource($stockLog))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Export Stock Logs
     *
     * Export stock logs within a date range to Excel or PDF format.
     */
    public function export(ExportStockLogRequest $request, StockLogExportFormat $format, ExportStockLog $exportStockLog)
    {
        Gate::authorize('viewAny', StockLog::class);

        return $exportStockLog->execute(
            $format,
            $request->validated('from'),
            $request->validated('to'),
        );
    }

    /**
     * Transfer Stock
     *
     * Transfer stock from one warehouse to another.
     */
    public function transfer(TransferStockRequest $request, TransferStock $transferStock)
    {
        Gate::authorize('create', Warehouse::class);

        $transferStock->execute(
            $request->user(),
            $request->validated()
        );

        return response()->json(['message' => 'Stock transferred successfully.']);
    }
}
