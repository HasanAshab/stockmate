<?php

namespace App\Http\Controllers;

use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Http\Resources\WarehouseStockResource;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Gate;

class WarehouseController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Warehouse::class);

        $warehouses = Warehouse::withCount('warehouseStocks')->paginate(15);

        return WarehouseResource::collection($warehouses);
    }

    public function store(StoreWarehouseRequest $request)
    {
        Gate::authorize('create', Warehouse::class);

        $warehouse = Warehouse::create($request->validated());

        return (new WarehouseResource($warehouse))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Warehouse $warehouse)
    {
        Gate::authorize('view', $warehouse);

        return new WarehouseResource($warehouse);
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        Gate::authorize('update', $warehouse);

        $warehouse->update($request->validated());

        return new WarehouseResource($warehouse);
    }

    public function destroy(Warehouse $warehouse)
    {
        Gate::authorize('delete', $warehouse);

        $hasStock = $warehouse->warehouseStocks()->where('quantity', '>', 0)->exists();

        if ($hasStock) {
            return response()->json([
                'message' => 'Cannot delete warehouse with existing stock. Transfer or clear stock first.',
            ], 422);
        }

        $warehouse->delete();

        return response()->json(['message' => 'Warehouse deleted.']);
    }

    public function stock(Warehouse $warehouse)
    {
        Gate::authorize('view', $warehouse);

        $stocks = $warehouse->warehouseStocks()
            ->with(['product.category'])
            ->paginate(20);

        return WarehouseStockResource::collection($stocks);
    }
}
