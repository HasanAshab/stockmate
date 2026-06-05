<?php

namespace App\Http\Controllers;

use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class SupplierController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Supplier::class);

        $suppliers = Cache::remember('suppliers:all', now()->addHour(), function () {
            return Supplier::all();
        });

        return $suppliers->toResourceCollection();
    }

    public function store(StoreSupplierRequest $request)
    {
        Gate::authorize('create', Supplier::class);

        $supplier = Supplier::create($request->validated());

        $this->clearSupplierCache();

        return $supplier->toResource()
            ->response()
            ->setStatusCode(201);
    }

    public function show(Supplier $supplier)
    {
        Gate::authorize('view', $supplier);

        return $supplier->toResource();
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        Gate::authorize('update', $supplier);
        $supplier->update($request->validated());

        $this->clearSupplierCache();

        return $supplier->toResource();
    }

    public function destroy(Supplier $supplier)
    {
        Gate::authorize('delete', $supplier);
        $supplier->delete();

        $this->clearSupplierCache();

        return response()->noContent();
    }

    public function trashed()
    {
        Gate::authorize('viewAny', Supplier::class);

        $trashed = Cache::remember('suppliers:trashed', now()->addMinutes(30), function () {
            return Supplier::onlyTrashed()->get();
        });

        return $trashed->toResourceCollection();
    }

    public function restore(Supplier $supplier)
    {
        Gate::authorize('restore', $supplier);
        $supplier->restore();

        $this->clearSupplierCache();

        return $supplier->toResource();
    }

    protected function clearSupplierCache(): void
    {
        Cache::forget('suppliers:all');
        Cache::forget('suppliers:trashed');
        Cache::forget('dashboard:metrics');
    }
}
