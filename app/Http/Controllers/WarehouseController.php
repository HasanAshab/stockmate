<?php

namespace App\Http\Controllers;

use App\Actions\Warehouse\DeleteWarehouse;
use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Gate;

/**
 * @group Warehouse Management
 *
 * APIs for managing warehouses
 *
 * @authenticated
 */
class WarehouseController extends Controller
{
    /**
     * List Warehouses
     *
     * Get a paginated list of all warehouses.
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Main Warehouse",
     *       "location": "New York",
     *       "created_at": "2026-01-15T10:00:00.000000Z"
     *     }
     *   ],
     *   "meta": {},
     *   "links": {}
     * }
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
     *
     * @bodyParam name string required The warehouse name. Example: Main Warehouse
     * @bodyParam location string required The warehouse location. Example: New York
     *
     * @response 201 {
     *   "id": 1,
     *   "name": "Main Warehouse",
     *   "location": "New York",
     *   "created_at": "2026-01-15T10:00:00.000000Z"
     * }
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
     *
     * @urlParam warehouse integer required The warehouse ID. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Main Warehouse",
     *   "location": "New York",
     *   "created_at": "2026-01-15T10:00:00.000000Z"
     * }
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
     *
     * @urlParam warehouse integer required The warehouse ID. Example: 1
     *
     * @bodyParam name string The warehouse name. Example: Updated Warehouse
     * @bodyParam location string The warehouse location. Example: Los Angeles
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Updated Warehouse",
     *   "location": "Los Angeles",
     *   "created_at": "2026-01-15T10:00:00.000000Z"
     * }
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
     *
     * @urlParam warehouse integer required The warehouse ID. Example: 1
     *
     * @response 204 scenario="Success"
     * @response 400 {
     *   "message": "Warehouse must be empty before deletion"
     * }
     */
    public function destroy(Warehouse $warehouse, DeleteWarehouse $deleteWarehouse)
    {
        Gate::authorize('delete', $warehouse);
        $deleteWarehouse->execute($warehouse);

        return response()->noContent();
    }
}
