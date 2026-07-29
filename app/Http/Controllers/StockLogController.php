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
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

#[Group('Stock Log Management', 'APIs for managing stock movements and inventory logs')]
#[Authenticated]
class StockLogController extends Controller
{
    /**
     * List Stock Logs
     *
     * Get a paginated list of stock movement logs with filtering and sorting.
     */
    #[QueryParam('filter[product]', 'integer', 'Filter by product ID.', example: 1)]
    #[QueryParam('filter[user]', 'integer', 'Filter by user ID.', example: 1)]
    #[QueryParam('filter[warehouse]', 'integer', 'Filter by warehouse ID.', example: 1)]
    #[QueryParam('filter[type]', 'string', 'Filter by stock log type.', example: 'in')]
    #[QueryParam('filter[quantity][gt]', 'number', 'Filter logs with quantity greater than value.', example: 100)]
    #[QueryParam('filter[quantity][gte]', 'number', 'Filter logs with quantity greater than or equal to value.', example: 10)]
    #[QueryParam('filter[quantity][lt]', 'number', 'Filter logs with quantity less than value.', example: 100)]
    #[QueryParam('filter[quantity][lte]', 'number', 'Filter logs with quantity less than or equal to value.', example: 50)]
    #[QueryParam('filter[quantity][eq]', 'number', 'Filter logs with exact quantity.', example: 25)]
    #[QueryParam('filter[unit_cost][gt]', 'number', 'Filter logs with unit cost greater than value.', example: 100)]
    #[QueryParam('filter[unit_cost][gte]', 'number', 'Filter logs with unit cost greater than or equal to value.', example: 50)]
    #[QueryParam('filter[unit_cost][lt]', 'number', 'Filter logs with unit cost less than value.', example: 100)]
    #[QueryParam('filter[unit_cost][lte]', 'number', 'Filter logs with unit cost less than or equal to value.', example: 50)]
    #[QueryParam('filter[unit_cost][eq]', 'number', 'Filter logs with exact unit cost.', example: 25.50)]
    #[QueryParam('filter[created_at]', 'string', 'Filter by date range (format: start,end).', example: '2026-01-01,2026-01-31')]
    #[QueryParam('sort', 'string', 'Sort by field. Use - prefix for descending.', example: '-created_at')]
    #[QueryParam('include', 'string', 'Include relationships.', example: 'user,product')]
    #[ResponseFromApiResource(StockLogResource::class, StockLog::class, collection: true, paginate: 10)]
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
    #[BodyParam('type', 'string', 'The stock log type (in, out, adjustment).', required: true, example: 'in')]
    #[BodyParam('product_id', 'integer', 'The product ID.', required: true, example: 1)]
    #[BodyParam('warehouse_id', 'integer', 'The warehouse ID.', required: true, example: 1)]
    #[BodyParam('quantity', 'integer', 'The quantity.', required: true, example: 50)]
    #[BodyParam('unit_cost', 'number', 'The unit cost.', example: 10.00)]
    #[BodyParam('notes', 'string', 'Optional notes.', example: 'Initial stock')]
    #[ResponseFromApiResource(StockLogResource::class, StockLog::class, status: 201)]
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
    #[BodyParam('from', 'string', 'Start date.', required: true, example: '2026-01-01')]
    #[BodyParam('to', 'string', 'End date.', required: true, example: '2026-01-31')]
    #[Response([], 200, 'File download')]
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
    #[BodyParam('product_id', 'integer', 'The product ID.', required: true, example: 1)]
    #[BodyParam('from_warehouse_id', 'integer', 'The source warehouse ID.', required: true, example: 1)]
    #[BodyParam('to_warehouse_id', 'integer', 'The destination warehouse ID.', required: true, example: 2)]
    #[BodyParam('quantity', 'integer', 'The quantity to transfer.', required: true, example: 25)]
    #[BodyParam('notes', 'string', 'Optional transfer notes.', example: 'Stock rebalancing')]
    #[Response(['message' => 'Stock transferred successfully.'], 200)]
    #[Response(['message' => 'Insufficient stock in source warehouse'], 400)]
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
