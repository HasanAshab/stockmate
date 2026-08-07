<?php

namespace App\Http\Controllers;

use App\Actions\Warehouse\DeleteWarehouse;
use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Support\Facades\Gate;

class WarehouseController extends Controller
{
    /**
     * List Warehouses
     *
     * Get a paginated list of all warehouses.
     */
    public function index()
    {
        Gate::authorize('viewAny', Warehouse::class);

        return WarehouseResource::collection(Warehouse::cursorPaginate(7));
    }

    /**
     * Create Warehouse
     *
     * Create a new warehouse.
     */
    public function store(StoreWarehouseRequest $request)
    {
        Gate::authorize('create', Warehouse::class);

        $warehouse = Warehouse::create($request->validated());

        return (new WarehouseResource($warehouse))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Get Warehouse
     *
     * Retrieve details of a specific warehouse by ID.
     */
    public function show(Warehouse $warehouse)
    {
        Gate::authorize('view', $warehouse);

        return new WarehouseResource($warehouse);
    }

    /**
     * Update Warehouse
     *
     * Update an existing warehouse.
     */
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        Gate::authorize('update', $warehouse);
        $warehouse->update($request->validated());

        return new WarehouseResource($warehouse);
    }

    /**
     * Delete Warehouse
     *
     * Delete a warehouse. The warehouse must be empty (no stock) to be deleted.
     */
    #[Response(
        status: 400,
        description: 'Cannot delete warehouse with existing stock. Transfer or clear stock first.',
    )]
    public function destroy(Warehouse $warehouse, DeleteWarehouse $deleteWarehouse)
    {
        Gate::authorize('delete', $warehouse);
        $deleteWarehouse->execute($warehouse);

        return response()->noContent();
    }
}
