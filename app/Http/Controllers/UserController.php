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
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    /**
     * List Users
     *
     * Get a paginated list of users with filtering and sorting capabilities.
     */
    #[QueryParameter(name: 'filter[role]', description: 'Filter by role. Pass role name. Example: `filter[role]=manager`', example: 'manager')]
    #[QueryParameter(name: 'filter[is_active]', description: 'Filter by active status. Values: `1` (active) or `0` (inactive). Example: `filter[is_active]=1`', example: 1)]
    #[QueryParameter(name: 'filter[created_at][from]', description: 'Filter users created from this date. Format: `YYYY-MM-DD` or ISO 8601. Example: `filter[created_at][from]=2024-01-01`', example: '2024-01-01')]
    #[QueryParameter(name: 'filter[created_at][to]', description: 'Filter users created until this date. Format: `YYYY-MM-DD` or ISO 8601. Example: `filter[created_at][to]=2024-12-31`', example: '2024-12-31')]
    #[QueryParameter(name: 'sort', description: 'Sort by field. Prefix with `-` for descending. Allowed: `created_at`. Example: `sort=-created_at`', example: '-created_at')]
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

    /**
     * Create User
     *
     * Create a new user account with the specified details.
     */
    public function store(StoreUserRequest $request, CreateUser $createUser)
    {
        $user = $createUser->execute($request->validated());

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Get User
     *
     * Retrieve details of a specific user by ID.
     */
    public function show(User $user)
    {
        $user->load(['roles', 'permissions']);

        return new UserResource($user);
    }

    /**
     * Update User
     *
     * Update an existing user's details.
     */
    public function update(UpdateUserRequest $request, User $user, UpdateUser $updateUser)
    {
        $user = $updateUser->execute($user, $request->validated());

        return new UserResource($user);
    }

    /**
     * Toggle User Status
     *
     * Activate or deactivate a user account.
     */
    public function toggleStatus(User $user, ToggleUserStatus $toggleUserStatus)
    {
        Gate::authorize('deactivate', $user);

        $user = $toggleUserStatus->execute($user);

        return new UserResource($user);
    }

    /**
     * Assign Roles
     *
     * Assign roles to a user. This will replace all existing roles with the provided ones.
     */
    public function assignRoles(AssignRolesRequest $request, User $user)
    {
        Gate::authorize('updateRole', $user);

        $user->syncRoles($request->validated('roles'));

        $user->load(['roles', 'permissions']);

        return new UserResource($user);
    }

    /**
     * Assign Permissions
     *
     * Assign direct permissions to a user. This will replace all existing direct permissions with the provided ones.
     */
    public function assignPermissions(AssignPermissionsRequest $request, User $user)
    {
        Gate::authorize('updateRole', $user);

        $user->syncPermissions($request->validated('permissions'));

        $user->load(['roles', 'permissions']);

        return new UserResource($user);
    }
}
