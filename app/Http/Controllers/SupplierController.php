<?php

namespace App\Http\Controllers;

use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Supplier Management', 'APIs for managing suppliers')]
#[Authenticated]
class SupplierController extends Controller
{
    /**
     * List Suppliers
     *
     * Get a list of all suppliers. Results are cached for one hour.
     */
    #[ResponseFromApiResource(SupplierResource::class, Supplier::class, collection: true)]
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
    #[BodyParam('name', 'string', 'The supplier name.', required: true, example: 'Tech Supplier Inc')]
    #[BodyParam('email', 'string', 'The supplier email.', example: 'contact@techsupplier.com')]
    #[BodyParam('phone', 'string', 'The supplier phone.', example: '+1234567890')]
    #[BodyParam('address', 'string', 'The supplier address.', example: '123 Supply St')]
    #[ResponseFromApiResource(SupplierResource::class, Supplier::class, status: 201)]
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
    #[ResponseFromApiResource(SupplierResource::class, Supplier::class)]
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
    #[BodyParam('name', 'string', 'The supplier name.', example: 'Updated Supplier')]
    #[BodyParam('email', 'string', 'The supplier email.', example: 'updated@supplier.com')]
    #[BodyParam('phone', 'string', 'The supplier phone.', example: '+0987654321')]
    #[BodyParam('address', 'string', 'The supplier address.', example: '456 New St')]
    #[ResponseFromApiResource(SupplierResource::class, Supplier::class)]
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
    #[Response([], 204, 'Success')]
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
    #[ResponseFromApiResource(SupplierResource::class, Supplier::class, collection: true)]
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
    #[ResponseFromApiResource(SupplierResource::class, Supplier::class)]
    public function restore(Supplier $supplier)
    {
        Gate::authorize('restore', $supplier);
        $supplier->restore();

        return new SupplierResource($supplier);
    }
}
