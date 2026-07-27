<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Product Management
 *
 * APIs for managing products
 *
 * @authenticated
 */
class ProductController extends Controller
{
    /**
     * List Products
     *
     * Get a paginated list of products with filtering, sorting, and relationship loading.
     *
     * @queryParam filter[trashed] Include trashed products (with, only, without). Example: with
     * @queryParam filter[category] Filter by category ID. Example: 1
     * @queryParam filter[supplier] Filter by supplier ID. Example: 1
     * @queryParam filter[price][gt] Filter products with price greater than value. Example: 100
     * @queryParam filter[price][gte] Filter products with price greater than or equal to value. Example: 50
     * @queryParam filter[price][lt] Filter products with price less than value. Example: 100
     * @queryParam filter[price][lte] Filter products with price less than or equal to value. Example: 100
     * @queryParam filter[price][eq] Filter products with exact price. Example: 99.99
     * @queryParam sort Sort by field. Use - prefix for descending. Example: -created_at
     * @queryParam include Include relationships (category, supplier, media). Example: category,supplier
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Product Name",
     *       "description": "Product description",
     *       "sku": "SKU-001",
     *       "price": 99.99,
     *       "category": {
     *         "id": 1,
     *         "name": "Category Name"
     *       },
     *       "supplier": {
     *         "id": 1,
     *         "name": "Supplier Name"
     *       }
     *     }
     *   ],
     *   "meta": {},
     *   "links": {}
     * }
     */
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
     *
     * @bodyParam name string required The product name. Example: Laptop
     * @bodyParam description string The product description. Example: High-performance laptop
     * @bodyParam sku string required The product SKU. Example: LAP-001
     * @bodyParam price number required The product price. Example: 999.99
     * @bodyParam category_id integer required The category ID. Example: 1
     * @bodyParam supplier_id integer The supplier ID. Example: 1
     * @bodyParam image file The product image.
     *
     * @response 201 {
     *   "id": 1,
     *   "name": "Laptop",
     *   "description": "High-performance laptop",
     *   "sku": "LAP-001",
     *   "price": 999.99,
     *   "category_id": 1,
     *   "supplier_id": 1,
     *   "created_at": "2026-01-15T10:00:00.000000Z"
     * }
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
     *
     * @urlParam product integer required The product ID. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Laptop",
     *   "description": "High-performance laptop",
     *   "sku": "LAP-001",
     *   "price": 999.99,
     *   "category": {
     *     "id": 1,
     *     "name": "Electronics"
     *   },
     *   "supplier": {
     *     "id": 1,
     *     "name": "Tech Supplier"
     *   }
     * }
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
     *
     * @urlParam product integer required The product ID. Example: 1
     *
     * @bodyParam name string The product name. Example: Updated Laptop
     * @bodyParam description string The product description. Example: Updated description
     * @bodyParam sku string The product SKU. Example: LAP-002
     * @bodyParam price number The product price. Example: 1099.99
     * @bodyParam category_id integer The category ID. Example: 1
     * @bodyParam supplier_id integer The supplier ID. Example: 1
     * @bodyParam image file The product image.
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Updated Laptop",
     *   "description": "Updated description",
     *   "sku": "LAP-002",
     *   "price": 1099.99,
     *   "category_id": 1,
     *   "supplier_id": 1
     * }
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
     *
     * @urlParam product integer required The product ID. Example: 1
     *
     * @response 204 scenario="Success"
     */
    public function destroy(Product $product)
    {
        Gate::authorize('delete', $product);
        $product->delete();

        return response()->noContent();
    }
}
