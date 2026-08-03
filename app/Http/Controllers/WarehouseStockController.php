<?php

namespace App\Http\Controllers;

use App\Http\Requests\Warehouse\UpdateWarehouseStockRequest;
use App\Http\Resources\WarehouseStockResource;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

class WarehouseStockController extends Controller
{
    /**
     * List Warehouse Stock
     *
     * Get a paginated list of stock items in a specific warehouse with filtering and sorting.
     */
    #[QueryParameter(name: 'filter[product]', description: 'Filter by product. Pass product ID. Example: `filter[product]=5`', example: 5)]
    #[QueryParameter(name: 'filter[warehouse]', description: 'Filter by warehouse. Pass warehouse ID. Example: `filter[warehouse]=2`', example: 2)]
    #[QueryParameter(name: 'filter[quantity]', description: 'Filter by quantity. Prefix the value with an operator: `=`, `<`, `>`, `<=`, `>=`, `<>`. Omit the prefix to match equal. Examples: `filter[quantity]=<100`, `filter[quantity]=>50`', example: '>50')]
    #[QueryParameter(name: 'filter[low_stock]', description: 'Filter products with low stock (quantity <= reorder_level). Pass `true` to enable. Example: `filter[low_stock]=true`', example: 'true')]
    #[QueryParameter(name: 'sort', description: 'Sort by field. Prefix with `-` for descending. Allowed: `quantity`, `created_at`. Example: `sort=quantity,-created_at`', example: 'quantity')]
    #[QueryParameter(name: 'include', description: 'Relationships to include. Allowed: `product.category`. Example: `include=product.category`', example: 'product.category')]
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
    public function update(UpdateWarehouseStockRequest $request, Warehouse $warehouse, WarehouseStock $stock)
    {
        Gate::authorize('view', $warehouse);

        $stock->update($request->validated());

        return new WarehouseStockResource($stock);
    }
}
