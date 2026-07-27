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

class WarehouseStockController extends Controller
{
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

    public function show(Warehouse $warehouse, WarehouseStock $stock)
    {
        Gate::authorize('view', $warehouse);

        return new WarehouseStockResource($stock);
    }

    public function update(UpdateWarehouseStockRequest $request, Warehouse $warehouse, WarehouseStock $stock)
    {
        Gate::authorize('view', $warehouse);

        $stock->update($request->validated());

        return new WarehouseStockResource($stock);
    }
}
