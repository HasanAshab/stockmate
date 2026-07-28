<?php

namespace App\Http\Controllers;

use App\Http\Requests\Warehouse\UpdateWarehouseStockRequest;
use App\Http\Resources\WarehouseStockResource;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

#[Group('Warehouse Stock Management', 'APIs for managing stock levels in warehouses')]
#[Authenticated]
class WarehouseStockController extends Controller
{
    /**
     * List Warehouse Stock
     *
     * Get a paginated list of stock items in a specific warehouse with filtering and sorting.
     */
    #[UrlParam('warehouse', 'integer', 'The warehouse ID.', required: true, example: 1)]
    #[QueryParam('filter[product]', 'integer', 'Filter by product ID.', example: 1)]
    #[QueryParam('filter[warehouse]', 'integer', 'Filter by warehouse ID.', example: 1)]
    #[QueryParam('filter[quantity][gt]', 'number', 'Filter stock with quantity greater than value.', example: 100)]
    #[QueryParam('filter[quantity][gte]', 'number', 'Filter stock with quantity greater than or equal to value.', example: 50)]
    #[QueryParam('filter[quantity][lt]', 'number', 'Filter stock with quantity less than value.', example: 10)]
    #[QueryParam('filter[quantity][lte]', 'number', 'Filter stock with quantity less than or equal to value.', example: 20)]
    #[QueryParam('filter[quantity][eq]', 'number', 'Filter stock with exact quantity.', example: 75)]
    #[QueryParam('filter[low_stock]', 'boolean', 'Filter low stock items.', example: true)]
    #[QueryParam('sort', 'string', 'Sort by field. Use - prefix for descending.', example: '-quantity')]
    #[QueryParam('include', 'string', 'Include relationships.', example: 'product.category')]
    #[ResponseFromApiResource(WarehouseStockResource::class, WarehouseStock::class, collection: true, paginate: 15)]
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
     */
    #[UrlParam('warehouse', 'integer', 'The warehouse ID.', required: true, example: 1)]
    #[UrlParam('stock', 'integer', 'The stock ID.', required: true, example: 1)]
    #[ResponseFromApiResource(WarehouseStockResource::class, WarehouseStock::class)]
    public function show(Warehouse $warehouse, WarehouseStock $stock)
    {
        Gate::authorize('view', $warehouse);

        return new WarehouseStockResource($stock);
    }

    /**
     * Update Warehouse Stock
     *
     * Update the quantity of a stock item in a warehouse.
     */
    #[UrlParam('warehouse', 'integer', 'The warehouse ID.', required: true, example: 1)]
    #[UrlParam('stock', 'integer', 'The stock ID.', required: true, example: 1)]
    #[BodyParam('quantity', 'integer', 'The new quantity.', required: true, example: 75)]
    #[ResponseFromApiResource(WarehouseStockResource::class, WarehouseStock::class)]
    public function update(UpdateWarehouseStockRequest $request, Warehouse $warehouse, WarehouseStock $stock)
    {
        Gate::authorize('view', $warehouse);

        $stock->update($request->validated());

        return new WarehouseStockResource($stock);
    }
}
