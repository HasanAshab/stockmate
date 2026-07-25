<?php

namespace App\Http\Controllers;

use App\Http\Filters\FiltersDateRange;
use App\Http\Requests\User\AssignPermissionsRequest;
use App\Http\Requests\User\AssignRolesRequest;
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
                AllowedFilter::scope('role'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::custom('created_at', new FiltersDateRange),
            )
            ->allowedSorts('created_at')
            ->defaultSort('-created_at')
            ->cursorPaginate(10)
            ->appends(request()->query())
            ->toResourceCollection();
    }

    public function store(StoreUserRequest $request)
    {
        return User::forceCreate([
                ...$request->validated(),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'is_active' => true,
            ])
            ->toResource()
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user)
    {
        $user->load(['roles', 'permissions']);

        return $user->toResource();
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->forceFill([
                'email_verified_at' => now(),
            ]);
        }

        if ($user->isDirty('phone')) {
            $user->forceFill([
                'phone_verified_at' => now(),
            ]);
        }

        $user->save();

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

    public function assignRoles(AssignRolesRequest $request, User $user)
    {
        Gate::authorize('updateRole', $user);

        $user->syncRoles($request->validated('roles'));

        $user->load(['roles', 'permissions']);

        return $user->toResource();
    }

    public function assignPermissions(AssignPermissionsRequest $request, User $user)
    {
        Gate::authorize('updateRole', $user);

        $user->syncPermissions($request->validated('permissions'));

        $user->load(['roles', 'permissions']);

        return $user->toResource();
    }
}
