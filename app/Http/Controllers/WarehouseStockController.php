<?php

namespace App\Http\Controllers;

use App\Http\Requests\Warehouse\UpdateWarehouseStockRequest;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\Gate;

class WarehouseStockController extends Controller
{
    public function index(Warehouse $warehouse)
    {
        Gate::authorize('view', $warehouse);

        return $warehouse->warehouseStocks()
            ->with(['product.category'])
            ->cursorPaginate(15)
            ->toResourceCollection();
    }

    public function show(Warehouse $warehouse, WarehouseStock $stock)
    {
        Gate::authorize('view', $warehouse);

        return $stock->toResource();
    }

    public function update(UpdateWarehouseStockRequest $request, Warehouse $warehouse, WarehouseStock $stock)
    {
        Gate::authorize('view', $warehouse);

        $stock->update($request->validated());

        return $stock->toResource();
    }
}
