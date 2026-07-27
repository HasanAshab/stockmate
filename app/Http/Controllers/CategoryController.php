<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

/**
 * @group Category Management
 *
 * APIs for managing product categories
 *
 * @authenticated
 */
class CategoryController extends Controller
{
    /**
     * List Categories
     *
     * Get a list of all categories. Results are cached for one hour.
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Electronics",
     *       "description": "Electronic products",
     *       "created_at": "2026-01-15T10:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index()
    {
        Gate::authorize('viewAny', Category::class);

        $categories = Cache::remember('categories:all', now()->addHour(), function () {
            return Category::all();
        });

        return CategoryResource::collection($categories);
    }

    /**
     * Create Category
     *
     * Create a new category.
     *
     * @bodyParam name string required The category name. Example: Electronics
     * @bodyParam description string The category description. Example: Electronic products
     *
     * @response 201 {
     *   "id": 1,
     *   "name": "Electronics",
     *   "description": "Electronic products",
     *   "created_at": "2026-01-15T10:00:00.000000Z"
     * }
     */
    public function store(StoreCategoryRequest $request)
    {
        Gate::authorize('create', Category::class);

        $category = Category::create($request->validated());

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Get Category
     *
     * Retrieve details of a specific category by ID.
     *
     * @urlParam category integer required The category ID. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Electronics",
     *   "description": "Electronic products",
     *   "created_at": "2026-01-15T10:00:00.000000Z"
     * }
     */
    public function show(Category $category)
    {
        Gate::authorize('view', $category);

        return new CategoryResource($category);
    }

    /**
     * Update Category
     *
     * Update an existing category.
     *
     * @urlParam category integer required The category ID. Example: 1
     *
     * @bodyParam name string The category name. Example: Updated Electronics
     * @bodyParam description string The category description. Example: Updated description
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Updated Electronics",
     *   "description": "Updated description",
     *   "created_at": "2026-01-15T10:00:00.000000Z"
     * }
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        Gate::authorize('update', $category);
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    /**
     * Delete Category
     *
     * Soft delete a category.
     *
     * @urlParam category integer required The category ID. Example: 1
     *
     * @response 204 scenario="Success"
     */
    public function destroy(Category $category)
    {
        Gate::authorize('delete', $category);
        $category->delete();

        return response()->noContent();
    }

    /**
     * List Trashed Categories
     *
     * Get a list of all soft-deleted categories.
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Old Electronics",
     *       "description": "Old category",
     *       "deleted_at": "2026-01-15T12:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function trashed()
    {
        Gate::authorize('viewAny', Category::class);

        return CategoryResource::collection(Category::onlyTrashed()->get());
    }

    /**
     * Restore Category
     *
     * Restore a soft-deleted category.
     *
     * @urlParam category integer required The category ID. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "Electronics",
     *   "description": "Electronic products",
     *   "created_at": "2026-01-15T10:00:00.000000Z"
     * }
     */
    public function restore(Category $category)
    {
        Gate::authorize('restore', $category);
        $category->restore();

        return new CategoryResource($category);
    }
}
