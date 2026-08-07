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
use Dedoc\Scramble\Attributes\QueryParameter;
use Dedoc\Scramble\Attributes\Response;
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
    #[QueryParameter(name: 'filter[product]', description: 'Filter by product. Pass product ID. Example: `filter[product]=5`', example: 5)]
    #[QueryParameter(name: 'filter[user]', description: 'Filter by user who performed the stock movement. Pass user ID. Example: `filter[user]=1`', example: 1)]
    #[QueryParameter(name: 'filter[warehouse]', description: 'Filter by warehouse. Pass warehouse ID. Example: `filter[warehouse]=2`', example: 2)]
    #[QueryParameter(name: 'filter[type]', description: 'Filter by stock movement type. Allowed values: `in`, `out`, `adjustment`, `transfer`. Example: `filter[type]=in`', example: 'in')]
    #[QueryParameter(name: 'filter[quantity]', description: 'Filter by quantity. Prefix the value with an operator: `=`, `<`, `>`, `<=`, `>=`, `<>`. Omit the prefix to match equal. Examples: `filter[quantity]=<100`, `filter[quantity]=>50`', example: '>50')]
    #[QueryParameter(name: 'filter[unit_cost]', description: 'Filter by unit cost. Prefix the value with an operator: `=`, `<`, `>`, `<=`, `>=`, `<>`. Omit the prefix to match equal. Examples: `filter[unit_cost]=<50`, `filter[unit_cost]=>10`', example: '>10')]
    #[QueryParameter(name: 'filter[created_at][from]', description: 'Filter logs created from this date. Format: `YYYY-MM-DD` or ISO 8601. Example: `filter[created_at][from]=2024-01-01`', example: '2024-01-01')]
    #[QueryParameter(name: 'filter[created_at][to]', description: 'Filter logs created until this date. Format: `YYYY-MM-DD` or ISO 8601. Example: `filter[created_at][to]=2024-12-31`', example: '2024-12-31')]
    #[QueryParameter(name: 'sort', description: 'Sort by field. Prefix with `-` for descending. Allowed: `created_at`, `quantity`, `unit_cost`. Example: `sort=-created_at,quantity`', example: '-created_at')]
    #[QueryParameter(name: 'include', description: 'Relationships to include. Allowed: `user`, `product`, `warehouse`. Comma-separated. Example: `include=user,product,warehouse`', example: 'user,product')]
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
    #[Response(
        status: 200,
        description: 'Stock logs exported as CSV.',
        mediaType: StockLogExportFormat::CSV_CONTENT_TYPE,
        type: 'string',
    )]
    #[Response(
        status: 200,
        description: 'Stock logs exported as Excel.',
        mediaType: StockLogExportFormat::EXCEL_CONTENT_TYPE,
        type: 'string',
    )]
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
