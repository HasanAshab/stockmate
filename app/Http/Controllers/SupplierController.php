<?php

namespace App\Http\Controllers;

use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

/**
 * @group Supplier Management
 *
 * APIs for managing suppliers
 *
 * @authenticated
 */
class SupplierController extends Controller
{
    /**
     * List Suppliers
     *
     * Get a list of all suppliers. Results are cached for one hour.
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Tech Supplier Inc",
     *       "email": "contact@techsupplier.com",
     *       "phone": "+1234567890",
     *       "address": "123 Supply St",
     *       "created_at": "2026-01-15T10:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index()
    {
        Gate::authorize('viewAny', Supplier::class);

        $suppliers = Cache::remember('suppliers:all', now()->addHour(), function () {
            return Supplier::all();
        });

        return SupplierResource::collection($suppliers);
    }

    /**
     * Create Supplier
     *
     * Create a new supplier.
     *
     * @bodyParam name string required The supplier name. Example: Tech Supplier Inc
     * @bodyParam email string The supplier email. Example: contact@techsupplier.com
     * @bodyParam phone string The supplier phone. Example: +1234567890
     * @bodyParam address string The supplier address. Example: 123 Supply St
     *
     * @response 201 {
     *   "id": 1,
     *   "name": "Tech Supplier Inc",
     *   "email": "contact@techsupplier.com",
     *   "phone": "+1234567890",
     *   "address": "123 Supply St",
     *   "created_at": "2026-01-15T10:00:00.000000Z"
     * }
     */
    public function store(StoreSupplierRequest $request)
    {
        Gate::authorize('create', Supplier::class);

        $supplier = Supplier::create($request->validated());

        return (new SupplierResource($supplier))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Get Supplier
     *
     * Retrieve details of a specific supplier by ID.
     *
     * @urlParam supplier integer required The supplier ID. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Tech Supplier Inc",
     *   "email": "contact@techsupplier.com",
     *   "phone": "+1234567890",
     *   "address": "123 Supply St",
     *   "created_at": "2026-01-15T10:00:00.000000Z"
     * }
     */
    public function show(Supplier $supplier)
    {
        Gate::authorize('view', $supplier);

        return new SupplierResource($supplier);
    }

    /**
     * Update Supplier
     *
     * Update an existing supplier.
     *
     * @urlParam supplier integer required The supplier ID. Example: 1
     *
     * @bodyParam name string The supplier name. Example: Updated Supplier
     * @bodyParam email string The supplier email. Example: updated@supplier.com
     * @bodyParam phone string The supplier phone. Example: +0987654321
     * @bodyParam address string The supplier address. Example: 456 New St
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Updated Supplier",
     *   "email": "updated@supplier.com",
     *   "phone": "+0987654321",
     *   "address": "456 New St",
     *   "created_at": "2026-01-15T10:00:00.000000Z"
     * }
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        Gate::authorize('update', $supplier);
        $supplier->update($request->validated());

        return new SupplierResource($supplier);
    }

    /**
     * Delete Supplier
     *
     * Soft delete a supplier.
     *
     * @urlParam supplier integer required The supplier ID. Example: 1
     *
     * @response 204 scenario="Success"
     */
    public function destroy(Supplier $supplier)
    {
        Gate::authorize('delete', $supplier);
        $supplier->delete();

        return response()->noContent();
    }

    /**
     * List Trashed Suppliers
     *
     * Get a list of all soft-deleted suppliers.
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Old Supplier",
     *       "deleted_at": "2026-01-15T12:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function trashed()
    {
        Gate::authorize('viewAny', Supplier::class);

        return SupplierResource::collection(Supplier::onlyTrashed()->get());
    }

    /**
     * Restore Supplier
     *
     * Restore a soft-deleted supplier.
     *
     * @urlParam supplier integer required The supplier ID. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Tech Supplier Inc",
     *   "email": "contact@techsupplier.com",
     *   "created_at": "2026-01-15T10:00:00.000000Z"
     * }
     */
    public function restore(Supplier $supplier)
    {
        Gate::authorize('restore', $supplier);
        $supplier->restore();

        return new SupplierResource($supplier);
    }
}
