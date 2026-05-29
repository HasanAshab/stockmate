<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Category::class);
        return Category::all()->toResourceCollection();
    }

    public function store(StoreCategoryRequest $request)
    {
        Gate::authorize('create', Category::class);
        return Category::create($request->validated())
            ->toResource()
            ->response()
            ->setStatusCode(201);
    }

    public function show(Category $category)
    {
        Gate::authorize('view', $category);
        return $category->toResource();
    }
 
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        Gate::authorize('update', $category);
        $category->update($request->validated());
        return $category->toResource();
    }

    public function destroy(Category $category)
    {
        Gate::authorize('delete', $category);
        $category->delete();
        return response()->noContent();
    }

    public function restore(Category $category)
    {
        Gate::authorize('restore', $category);
        $category->restore();
        return $category->toResource();
    }    
}
