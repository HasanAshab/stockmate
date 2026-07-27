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

/**
 * @group Stock Log Management
 *
 * APIs for managing stock movements and inventory logs
 *
 * @authenticated
 */
class StockLogController extends Controller
{
    /**
     * List Stock Logs
     *
     * Get a paginated list of stock movement logs with filtering and sorting.
     *
     * @queryParam filter[product] Filter by product ID. Example: 1
     * @queryParam filter[user] Filter by user ID. Example: 1
     * @queryParam filter[warehouse] Filter by warehouse ID. Example: 1
     * @queryParam filter[type] Filter by stock log type (in, out, adjustment, transfer). Example: in
     * @queryParam filter[quantity][gt] Filter logs with quantity greater than value. Example: 100
     * @queryParam filter[quantity][gte] Filter logs with quantity greater than or equal to value. Example: 10
     * @queryParam filter[quantity][lt] Filter logs with quantity less than value. Example: 100
     * @queryParam filter[quantity][lte] Filter logs with quantity less than or equal to value. Example: 50
     * @queryParam filter[quantity][eq] Filter logs with exact quantity. Example: 25
     * @queryParam filter[unit_cost][gt] Filter logs with unit cost greater than value. Example: 100
     * @queryParam filter[unit_cost][gte] Filter logs with unit cost greater than or equal to value. Example: 50
     * @queryParam filter[unit_cost][lt] Filter logs with unit cost less than value. Example: 100
     * @queryParam filter[unit_cost][lte] Filter logs with unit cost less than or equal to value. Example: 50
     * @queryParam filter[unit_cost][eq] Filter logs with exact unit cost. Example: 25.50
     * @queryParam filter[created_at] Filter by date range (format: start,end). Example: 2026-01-01,2026-01-31
     * @queryParam sort Sort by field. Use - prefix for descending. Example: -created_at
     * @queryParam include Include relationships (user, product, warehouse). Example: user,product
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "type": "in",
     *       "quantity": 50,
     *       "unit_cost": 10.00,
     *       "notes": "Initial stock",
     *       "product_id": 1,
     *       "warehouse_id": 1,
     *       "user_id": 1,
     *       "created_at": "2026-01-15T10:00:00.000000Z"
     *     }
     *   ],
     *   "meta": {},
     *   "links": {}
     * }
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
     *
     * @bodyParam type string required The stock log type (in, out, adjustment). Example: in
     * @bodyParam product_id integer required The product ID. Example: 1
     * @bodyParam warehouse_id integer required The warehouse ID. Example: 1
     * @bodyParam quantity integer required The quantity. Example: 50
     * @bodyParam unit_cost number The unit cost. Example: 10.00
     * @bodyParam notes string Optional notes. Example: Initial stock
     *
     * @response 201 {
     *   "id": 1,
     *   "type": "in",
     *   "quantity": 50,
     *   "unit_cost": 10.00,
     *   "notes": "Initial stock",
     *   "product_id": 1,
     *   "warehouse_id": 1,
     *   "user_id": 1,
     *   "created_at": "2026-01-15T10:00:00.000000Z"
     * }
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
     *
     * @urlParam format string required The export format (xlsx, pdf). Example: xlsx
     *
     * @bodyParam from date required Start date. Example: 2026-01-01
     * @bodyParam to date required End date. Example: 2026-01-31
     *
     * @response 200 scenario="File download"
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
     *
     * @bodyParam product_id integer required The product ID. Example: 1
     * @bodyParam from_warehouse_id integer required The source warehouse ID. Example: 1
     * @bodyParam to_warehouse_id integer required The destination warehouse ID. Example: 2
     * @bodyParam quantity integer required The quantity to transfer. Example: 25
     * @bodyParam notes string Optional transfer notes. Example: Stock rebalancing
     *
     * @response 200 {
     *   "message": "Stock transferred successfully."
     * }
     * @response 400 {
     *   "message": "Insufficient stock in source warehouse"
     * }
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
