<?php

namespace App\Http\Controllers;

use App\Actions\Warehouse\DeleteWarehouse;
use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Gate;

class WarehouseController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Warehouse::class);

        return WarehouseResource::collection(Warehouse::cursorPaginate(7));
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

    public function destroy(Warehouse $warehouse, DeleteWarehouse $deleteWarehouse)
    {
        Gate::authorize('delete', $warehouse);
        $deleteWarehouse->execute($warehouse);

        return response()->noContent();
    }
}
