<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

class ProductController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Product::class);

        return QueryBuilder::for(Product::class)
            ->allowedFilters(
                AllowedFilter::trashed(),
                AllowedFilter::belongsTo('category'),
                AllowedFilter::belongsTo('supplier'),
                AllowedFilter::scope('low_stock'),
                AllowedFilter::operator('price', FilterOperator::DYNAMIC),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('name', 'ilike', "%{$value}%")
                        ->orWhere('sku', 'ilike', "%{$value}%");
                }),
            )
            ->allowedSorts(
                'price',
                'quantity',
                'created_at',
            )
            ->allowedIncludes('category', 'supplier', 'media')
            ->defaultSort('-created_at')
            ->cursorPaginate(10)
            ->appends(request()->query())
            ->toResourceCollection();
    }

    public function store(StoreProductRequest $request)
    {
        Gate::authorize('create', Product::class);

        $product = Product::create($request->validated());

        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')
                ->toMediaCollection('product_images');
        }

        return $product->toResource()
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product)
    {
        Gate::authorize('view', $product);

        return $product->load(['category', 'supplier', 'media'])->toResource();
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        Gate::authorize('update', $product);

        if ($request->hasFile('image')) {
            $product->clearMediaCollection('product_images');
            $product->addMediaFromRequest('image')
                ->toMediaCollection('product_images');
        }

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
