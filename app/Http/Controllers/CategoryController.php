<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Category Management', 'APIs for managing product categories')]
#[Authenticated]
class CategoryController extends Controller
{
    /**
     * List Categories
     *
     * Get a list of all categories. Results are cached for one hour.
     */
    #[ResponseFromApiResource(CategoryResource::class, Category::class, collection: true)]
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
     */
    #[BodyParam('name', 'string', 'The category name.', required: true, example: 'Electronics')]
    #[BodyParam('description', 'string', 'The category description.', example: 'Electronic products')]
    #[ResponseFromApiResource(CategoryResource::class, Category::class, status: 201)]
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
     */
    #[ResponseFromApiResource(CategoryResource::class, Category::class)]
    public function show(Category $category)
    {
        Gate::authorize('view', $category);

        return new CategoryResource($category);
    }

    /**
     * Update Category
     *
     * Update an existing category.
     */
    #[BodyParam('name', 'string', 'The category name.', example: 'Updated Electronics')]
    #[BodyParam('description', 'string', 'The category description.', example: 'Updated description')]
    #[ResponseFromApiResource(CategoryResource::class, Category::class)]
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
     */
    #[Response([], 204, 'Success')]
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
     */
    #[ResponseFromApiResource(CategoryResource::class, Category::class, collection: true)]
    public function trashed()
    {
        Gate::authorize('viewAny', Category::class);

        return CategoryResource::collection(Category::onlyTrashed()->get());
    }

    /**
     * Restore Category
     *
     * Restore a soft-deleted category.
     */
    #[ResponseFromApiResource(CategoryResource::class, Category::class)]
    public function restore(Category $category)
    {
        Gate::authorize('restore', $category);
        $category->restore();

        return new CategoryResource($category);
    }
}
