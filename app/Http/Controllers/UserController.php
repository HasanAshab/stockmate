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
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

#[Group('User Management', 'APIs for managing users, roles, and permissions')]
#[Authenticated]
class UserController extends Controller
{
    /**
     * List Users
     *
     * Get a paginated list of users with filtering and sorting capabilities.
     */
    #[QueryParam('filter[role]', 'string', 'Filter by role name.', example: 'admin')]
    #[QueryParam('filter[is_active]', 'integer', 'Filter by active status.', example: 1)]
    #[QueryParam('filter[created_at]', 'string', 'Filter by creation date range (format: start,end).', example: '2026-01-01,2026-01-31')]
    #[QueryParam('sort', 'string', 'Sort by field. Use - prefix for descending.', example: '-created_at')]
    #[QueryParam('include', 'string', 'Include relationships (roles, permissions).', example: 'roles,permissions')]
    #[ResponseFromApiResource(UserResource::class, User::class, collection: true, paginate: 10)]
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
    #[BodyParam('name', 'string', 'The user\'s full name.', required: true, example: 'Jane Smith')]
    #[BodyParam('email', 'string', 'The user\'s email address.', example: 'jane@example.com')]
    #[BodyParam('phone', 'string', 'The user\'s phone number.', example: '+1234567890')]
    #[BodyParam('password', 'string', 'The user\'s password.', required: true, example: 'Password123!')]
    #[BodyParam('is_active', 'boolean', 'Whether the user account is active.', example: true)]
    #[ResponseFromApiResource(UserResource::class, User::class, status: 201)]
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
    #[ResponseFromApiResource(UserResource::class, User::class, with: ['roles', 'permissions'])]
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
    #[BodyParam('name', 'string', 'The user\'s full name.', example: 'John Updated')]
    #[BodyParam('email', 'string', 'The user\'s email address.', example: 'updated@example.com')]
    #[BodyParam('phone', 'string', 'The user\'s phone number.', example: '+0987654321')]
    #[BodyParam('password', 'string', 'The new password.', example: 'NewPassword123!')]
    #[BodyParam('is_active', 'boolean', 'Whether the user account is active.', example: true)]
    #[ResponseFromApiResource(UserResource::class, User::class)]
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
    #[ResponseFromApiResource(UserResource::class, User::class)]
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
    #[BodyParam('roles', 'string[]', 'Array of role names.', required: true, example: ['admin', 'manager'])]
    #[ResponseFromApiResource(UserResource::class, User::class, with: ['roles', 'permissions'])]
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
    #[BodyParam('permissions', 'string[]', 'Array of permission names.', required: true, example: ['UsersCreate', 'UsersUpdate'])]
    #[ResponseFromApiResource(UserResource::class, User::class, with: ['roles', 'permissions'])]
    public function assignPermissions(AssignPermissionsRequest $request, User $user)
    {
        Gate::authorize('updateRole', $user);

        $user->syncPermissions($request->validated('permissions'));

        $user->load(['roles', 'permissions']);

        return new UserResource($user);
    }
}
