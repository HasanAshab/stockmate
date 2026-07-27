<?php

namespace App\Http\Controllers;

use App\Http\Requests\Warehouse\UpdateWarehouseStockRequest;
use App\Http\Resources\WarehouseStockResource;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Warehouse Stock Management
 *
 * APIs for managing stock levels in warehouses
 *
 * @authenticated
 */
class WarehouseStockController extends Controller
{
    /**
     * List Warehouse Stock
     *
     * Get a paginated list of stock items in a specific warehouse with filtering and sorting.
     *
     * @urlParam warehouse integer required The warehouse ID. Example: 1
     *
     * @queryParam filter[product] Filter by product ID. Example: 1
     * @queryParam filter[warehouse] Filter by warehouse ID. Example: 1
     * @queryParam filter[quantity][gt] Filter stock with quantity greater than value. Example: 100
     * @queryParam filter[quantity][gte] Filter stock with quantity greater than or equal to value. Example: 50
     * @queryParam filter[quantity][lt] Filter stock with quantity less than value. Example: 10
     * @queryParam filter[quantity][lte] Filter stock with quantity less than or equal to value. Example: 20
     * @queryParam filter[quantity][eq] Filter stock with exact quantity. Example: 75
     * @queryParam filter[low_stock] Filter low stock items. Example: true
     * @queryParam sort Sort by field. Use - prefix for descending. Example: -quantity
     * @queryParam include Include relationships (product.category). Example: product.category
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "warehouse_id": 1,
     *       "product_id": 1,
     *       "quantity": 50,
     *       "product": {
     *         "id": 1,
     *         "name": "Laptop",
     *         "sku": "LAP-001"
     *       }
     *     }
     *   ],
     *   "meta": {},
     *   "links": {}
     * }
     */
    public function index(Warehouse $warehouse)
    {
        Gate::authorize('view', $warehouse);

        $stocks = QueryBuilder::for($warehouse->warehouseStocks())
            ->allowedFilters(
                AllowedFilter::belongsTo('product'),
                AllowedFilter::belongsTo('warehouse'),
                AllowedFilter::operator('quantity', FilterOperator::DYNAMIC),
                AllowedFilter::scope('low_stock'),
            )
            ->allowedSorts(
                'quantity',
                'created_at',
            )
            ->allowedIncludes('product.category')
            ->defaultSort('-created_at')
            ->cursorPaginate(15)
            ->appends(request()->query());

        return WarehouseStockResource::collection($stocks);
    }

    /**
     * Get Warehouse Stock Item
     *
     * Retrieve details of a specific stock item in a warehouse.
     *
     * @urlParam warehouse integer required The warehouse ID. Example: 1
     * @urlParam stock integer required The stock ID. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "warehouse_id": 1,
     *   "product_id": 1,
     *   "quantity": 50,
     *   "created_at": "2026-01-15T10:00:00.000000Z"
     * }
     */
    public function show(Warehouse $warehouse, WarehouseStock $stock)
    {
        Gate::authorize('view', $warehouse);

        return new WarehouseStockResource($stock);
    }

    /**
     * Update Warehouse Stock
     *
     * Update the quantity of a stock item in a warehouse.
     *
     * @urlParam warehouse integer required The warehouse ID. Example: 1
     * @urlParam stock integer required The stock ID. Example: 1
     *
     * @bodyParam quantity integer required The new quantity. Example: 75
     *
     * @response 200 {
     *   "id": 1,
     *   "warehouse_id": 1,
     *   "product_id": 1,
     *   "quantity": 75,
     *   "created_at": "2026-01-15T10:00:00.000000Z"
     * }
     */
    public function update(UpdateWarehouseStockRequest $request, Warehouse $warehouse, WarehouseStock $stock)
    {
        Gate::authorize('view', $warehouse);

        $stock->update($request->validated());

        return new WarehouseStockResource($stock);
    }
}
