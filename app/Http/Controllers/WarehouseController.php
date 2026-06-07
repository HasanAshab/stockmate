<?php

namespace App\Http\Controllers;

use App\Actions\Warehouse\DeleteWarehouse;
use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseStockRequest;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\Gate;

class WarehouseController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Warehouse::class);

        return Warehouse::withCount('warehouseStocks')
            ->paginate(15)
            ->toResourceCollection();
    }

    public function store(StoreWarehouseRequest $request)
    {
        Gate::authorize('create', Warehouse::class);

        return Warehouse::create($request->validated())
            ->toResource()
            ->response()
            ->setStatusCode(201);
    }

    public function show(Warehouse $warehouse)
    {
        Gate::authorize('view', $warehouse);

        return $warehouse->toResource();
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        Gate::authorize('update', $warehouse);
        $warehouse->update($request->validated());

        return $warehouse->toResource();
    }

    public function destroy(Warehouse $warehouse, DeleteWarehouse $deleteWarehouse)
    {
        Gate::authorize('delete', $warehouse);
        $deleteWarehouse->execute($warehouse);

        return response()->noContent();
    }

    public function stock(Warehouse $warehouse)
    {
        Gate::authorize('view', $warehouse);

        return $warehouse->warehouseStocks()
            ->with(['product.category'])
            ->paginate(20)
            ->toResourceCollection();
    }

    public function updateStock(UpdateWarehouseStockRequest $request, Warehouse $warehouse, WarehouseStock $warehouseStock)
    {
        Gate::authorize('view', $warehouse);

        abort_if(
            $warehouseStock->warehouse_id !== $warehouse->id,
            404,
            'Warehouse stock not found in this warehouse'
        );

        $warehouseStock->update($request->validated());

        return $warehouseStock->toResource();
    }
}
