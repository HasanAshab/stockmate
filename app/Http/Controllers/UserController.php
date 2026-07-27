<?php

namespace App\Http\Controllers;

use App\Actions\User\CreateUser;
use App\Actions\User\ToggleUserStatus;
use App\Actions\User\UpdateUser;
use App\Http\Filters\FiltersDateRange;
use App\Http\Requests\User\AssignPermissionsRequest;
use App\Http\Requests\User\AssignRolesRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    public function index()
    {
        $users = QueryBuilder::for(User::class)
            ->with(['roles', 'permissions'])
            ->allowedFilters(
                AllowedFilter::scope('role'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::custom('created_at', new FiltersDateRange),
            )
            ->allowedSorts('created_at')
            ->defaultSort('-created_at')
            ->cursorPaginate(10)
            ->appends(request()->query());

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request, CreateUser $createUser)
    {
        $user = $createUser->execute($request->validated());

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user)
    {
        $user->load(['roles', 'permissions']);

        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUser $updateUser)
    {
        $user = $updateUser->execute($user, $request->validated());

        return new UserResource($user);
    }

    public function toggleStatus(User $user, ToggleUserStatus $toggleUserStatus)
    {
        Gate::authorize('deactivate', $user);

        $user = $toggleUserStatus->execute($user);

        return new UserResource($user);
    }

    public function assignRoles(AssignRolesRequest $request, User $user)
    {
        Gate::authorize('updateRole', $user);

        $user->syncRoles($request->validated('roles'));

        $user->load(['roles', 'permissions']);

        return new UserResource($user);
    }

    public function assignPermissions(AssignPermissionsRequest $request, User $user)
    {
        Gate::authorize('updateRole', $user);

        $user->syncPermissions($request->validated('permissions'));

        $user->load(['roles', 'permissions']);

        return new UserResource($user);
    }
}
