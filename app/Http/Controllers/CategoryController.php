<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Category::class);

        $categories = Cache::remember('categories:all', now()->addHour(), function () {
            return Category::all();
        });

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request)
    {
        Gate::authorize('create', Category::class);

        $category = Category::create($request->validated());

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Category $category)
    {
        Gate::authorize('view', $category);

        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        Gate::authorize('update', $category);
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    public function destroy(Category $category)
    {
        Gate::authorize('delete', $category);
        $category->delete();

        return response()->noContent();
    }

    public function trashed()
    {
        Gate::authorize('viewAny', Category::class);

        return CategoryResource::collection(Category::onlyTrashed()->get());
    }

    public function restore(Category $category)
    {
        Gate::authorize('restore', $category);
        $category->restore();

        return new CategoryResource($category);
    }
}
