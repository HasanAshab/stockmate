<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Product::class);

        return Product::all()->toResourceCollection();
    }

    public function store(StoreProductRequest $request)
    {
        Gate::authorize('create', Product::class);

        return Product::create($request->validated())
            ->toResource()
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product)
    {
        Gate::authorize('view', $product);

        return $product->load(['category', 'supplier'])->toResource();
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        Gate::authorize('update', $product);
        $product->update($request->validated());

        return $product->toResource();
    }

    public function destroy(Product $product)
    {
        Gate::authorize('delete', $product);
        $product->delete();

        return response()->noContent();
    }
}
