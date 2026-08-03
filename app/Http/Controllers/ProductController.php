<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

class ProductController extends Controller
{
    /**
     * List Products
     *
     * Get a paginated list of products with filtering, sorting, and relationship loading.
     */
    #[QueryParameter(name: 'filter[trashed]', description: 'Include trashed products. Values: `with` (include), `only` (only trashed), `without` (exclude). Example: `filter[trashed]=with`', example: 'with')]
    #[QueryParameter(name: 'filter[category]', description: 'Filter by category. Pass category ID. Example: `filter[category]=2`', example: 2)]
    #[QueryParameter(name: 'filter[supplier]', description: 'Filter by supplier. Pass supplier ID. Example: `filter[supplier]=3`', example: 3)]
    #[QueryParameter(name: 'filter[price]', description: 'Filter by price. Prefix the value with an operator: `=`, `<`, `>`, `<=`, `>=`, `<>`. Omit the prefix to match equal. Examples: `filter[price]=<100`, `filter[price]=>50`, `filter[price]=100`', example: '>50')]
    #[QueryParameter(name: 'sort', description: 'Sort by field. Prefix with `-` for descending. Allowed: `price`, `created_at`. Example: `sort=-created_at,price`', example: '-created_at')]
    #[QueryParameter(name: 'include', description: 'Relationships to include. Allowed: `category`, `supplier`, `media`. Comma-separated. Example: `include=category,supplier`', example: 'category,supplier')]
    public function index()
    {
        Gate::authorize('viewAny', Product::class);

        $products = QueryBuilder::for(Product::class)
            ->allowedFilters(
                AllowedFilter::trashed(),
                AllowedFilter::belongsTo('category'),
                AllowedFilter::belongsTo('supplier'),
                AllowedFilter::operator('price', FilterOperator::DYNAMIC),
            )
            ->allowedSorts(
                'price',
                'created_at',
            )
            ->allowedIncludes('category', 'supplier', 'media')
            ->defaultSort('-created_at')
            ->cursorPaginate(10)
            ->appends(request()->query());

        return ProductResource::collection($products);
    }

    /**
     * Create Product
     *
     * Create a new product with optional image upload.
     */
    public function store(StoreProductRequest $request)
    {
        Gate::authorize('create', Product::class);

        $product = Product::create($request->validated());

        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')
                ->toMediaCollection('product_images');
        }

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Get Product
     *
     * Retrieve details of a specific product by ID.
     */
    public function show(Product $product)
    {
        Gate::authorize('view', $product);

        return new ProductResource($product->load(['category', 'supplier', 'media']));
    }

    /**
     * Update Product
     *
     * Update an existing product. If a new image is uploaded, the old image will be replaced.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        Gate::authorize('update', $product);

        if ($request->hasFile('image')) {
            $product->clearMediaCollection('product_images');
            $product->addMediaFromRequest('image')
                ->toMediaCollection('product_images');
        }

        $product->update($request->validated());

        return new ProductResource($product);
    }

    /**
     * Delete Product
     *
     * Soft delete a product.
     */
    public function destroy(Product $product)
    {
        Gate::authorize('delete', $product);
        $product->delete();

        return response()->noContent();
    }
}
