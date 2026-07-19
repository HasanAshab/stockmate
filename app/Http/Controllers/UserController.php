<?php

namespace App\Http\Controllers;

use App\Http\Filters\FiltersDateRange;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    public function index()
    {
        return QueryBuilder::for(User::class)
            ->allowedFilters(
                AllowedFilter::exact('role'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::custom('created_at', new FiltersDateRange),
                AllowedFilter::groupOr('search', [
                    AllowedFilter::partial('name'),
                    AllowedFilter::partial('email'),
                ])
            )
            ->allowedSorts('created_at')
            ->defaultSort('-created_at')
            ->cursorPaginate(10)
            ->appends(request()->query())
            ->toResourceCollection();
    }

    public function store(StoreUserRequest $request)
    {
        return User::create($request->validated())
            ->toResource()
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user)
    {
        return $user->toResource();
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if (isset($validated['role'])) {
            Gate::authorize('updateRole', $user);
        }

        $user->update($validated);

        return $user->toResource();
    }

    public function toggleStatus(User $user)
    {
        Gate::authorize('deactivate', $user);

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        return $user->toResource();
    }
}
