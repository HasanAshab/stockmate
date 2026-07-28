<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

#[Group('Product Management', 'APIs for managing products')]
#[Authenticated]
class ProductController extends Controller
{
    /**
     * List Products
     *
     * Get a paginated list of products with filtering, sorting, and relationship loading.
     */
    #[QueryParam('filter[trashed]', 'string', 'Include trashed products (with, only, without).', example: 'with')]
    #[QueryParam('filter[category]', 'integer', 'Filter by category ID.', example: 1)]
    #[QueryParam('filter[supplier]', 'integer', 'Filter by supplier ID.', example: 1)]
    #[QueryParam('filter[price][gt]', 'number', 'Filter products with price greater than value.', example: 100)]
    #[QueryParam('filter[price][gte]', 'number', 'Filter products with price greater than or equal to value.', example: 50)]
    #[QueryParam('filter[price][lt]', 'number', 'Filter products with price less than value.', example: 100)]
    #[QueryParam('filter[price][lte]', 'number', 'Filter products with price less than or equal to value.', example: 100)]
    #[QueryParam('filter[price][eq]', 'number', 'Filter products with exact price.', example: 99.99)]
    #[QueryParam('sort', 'string', 'Sort by field. Use - prefix for descending.', example: '-created_at')]
    #[QueryParam('include', 'string', 'Include relationships (category, supplier, media).', example: 'category,supplier')]
    #[ResponseFromApiResource(ProductResource::class, Product::class, collection: true, paginate: 10)]
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
    #[BodyParam('name', 'string', 'The product name.', required: true, example: 'Laptop')]
    #[BodyParam('description', 'string', 'The product description.', example: 'High-performance laptop')]
    #[BodyParam('sku', 'string', 'The product SKU.', required: true, example: 'LAP-001')]
    #[BodyParam('price', 'number', 'The product price.', required: true, example: 999.99)]
    #[BodyParam('category_id', 'integer', 'The category ID.', required: true, example: 1)]
    #[BodyParam('supplier_id', 'integer', 'The supplier ID.', example: 1)]
    #[BodyParam('image', 'file', 'The product image.')]
    #[ResponseFromApiResource(ProductResource::class, Product::class, status: 201)]
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
    #[UrlParam('product', 'integer', 'The product ID.', required: true, example: 1)]
    #[ResponseFromApiResource(ProductResource::class, Product::class, with: ['category', 'supplier', 'media'])]
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
    #[UrlParam('product', 'integer', 'The product ID.', required: true, example: 1)]
    #[BodyParam('name', 'string', 'The product name.', example: 'Updated Laptop')]
    #[BodyParam('description', 'string', 'The product description.', example: 'Updated description')]
    #[BodyParam('sku', 'string', 'The product SKU.', example: 'LAP-002')]
    #[BodyParam('price', 'number', 'The product price.', example: 1099.99)]
    #[BodyParam('category_id', 'integer', 'The category ID.', example: 1)]
    #[BodyParam('supplier_id', 'integer', 'The supplier ID.', example: 1)]
    #[BodyParam('image', 'file', 'The product image.')]
    #[ResponseFromApiResource(ProductResource::class, Product::class)]
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
    #[UrlParam('product', 'integer', 'The product ID.', required: true, example: 1)]
    #[Response([], 204, 'Success')]
    public function destroy(Product $product)
    {
        Gate::authorize('delete', $product);
        $product->delete();

        return response()->noContent();
    }
}
