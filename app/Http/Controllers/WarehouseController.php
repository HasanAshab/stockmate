<?php

namespace App\Http\Controllers;

use App\Actions\Warehouse\DeleteWarehouse;
use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Warehouse Management', 'APIs for managing warehouses')]
#[Authenticated]
class WarehouseController extends Controller
{
    /**
     * List Warehouses
     *
     * Get a paginated list of all warehouses.
     */
    #[ResponseFromApiResource(WarehouseResource::class, Warehouse::class, collection: true, paginate: 7)]
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
    #[BodyParam('name', 'string', 'The warehouse name.', required: true, example: 'Main Warehouse')]
    #[BodyParam('location', 'string', 'The warehouse location.', required: true, example: 'New York')]
    #[ResponseFromApiResource(WarehouseResource::class, Warehouse::class, status: 201)]
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
    #[ResponseFromApiResource(WarehouseResource::class, Warehouse::class)]
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
    #[BodyParam('name', 'string', 'The warehouse name.', example: 'Updated Warehouse')]
    #[BodyParam('location', 'string', 'The warehouse location.', example: 'Los Angeles')]
    #[ResponseFromApiResource(WarehouseResource::class, Warehouse::class)]
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
    #[Response([], 204, 'Success')]
    #[Response(['message' => 'Warehouse must be empty before deletion'], 400)]
    public function destroy(Warehouse $warehouse, DeleteWarehouse $deleteWarehouse)
    {
        Gate::authorize('delete', $warehouse);
        $deleteWarehouse->execute($warehouse);

        return response()->noContent();
    }
}
