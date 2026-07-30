<?php

namespace App\Http\Controllers;

use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class SupplierController extends Controller
{
    /**
     * List Suppliers
     *
     * Get a list of all suppliers. Results are cached for one hour.
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
     */
    public function restore(Supplier $supplier)
    {
        Gate::authorize('restore', $supplier);
        $supplier->restore();

        return new SupplierResource($supplier);
    }
}
