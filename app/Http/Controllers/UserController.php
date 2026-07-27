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

/**
 * @group User Management
 *
 * APIs for managing users, roles, and permissions
 *
 * @authenticated
 */
class UserController extends Controller
{
    /**
     * List Users
     *
     * Get a paginated list of users with filtering and sorting capabilities.
     *
     * @queryParam filter[role] Filter by role name. Example: admin
     * @queryParam filter[is_active] Filter by active status. Example: 1
     * @queryParam filter[created_at] Filter by creation date range (format: start,end). Example: 2026-01-01,2026-01-31
     * @queryParam sort Sort by field. Use - prefix for descending. Example: -created_at
     * @queryParam include Include relationships (roles, permissions). Example: roles,permissions
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "user@example.com",
     *       "phone": null,
     *       "is_active": true,
     *       "is_verified": true,
     *       "created_at": "2026-01-15T10:00:00.000000Z",
     *       "roles": [],
     *       "direct_permissions": [],
     *       "permissions": []
     *     }
     *   ],
     *   "meta": {},
     *   "links": {}
     * }
     */
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
     *
     * @bodyParam name string required The user's full name. Example: Jane Smith
     * @bodyParam email string The user's email address. Example: jane@example.com
     * @bodyParam phone string The user's phone number. Example: +1234567890
     * @bodyParam password string required The user's password. Example: Password123!
     * @bodyParam password_confirmation string required Password confirmation. Example: Password123!
     * @bodyParam is_active boolean Whether the user account is active. Example: true
     *
     * @response 201 {
     *   "id": 2,
     *   "name": "Jane Smith",
     *   "email": "jane@example.com",
     *   "phone": null,
     *   "is_active": true,
     *   "is_verified": true,
     *   "created_at": "2026-01-15T11:00:00.000000Z",
     *   "roles": [],
     *   "direct_permissions": [],
     *   "permissions": []
     * }
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
     *
     * @urlParam user integer required The user ID. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "John Doe",
     *   "email": "user@example.com",
     *   "phone": null,
     *   "is_active": true,
     *   "is_verified": true,
     *   "created_at": "2026-01-15T10:00:00.000000Z",
     *   "roles": [
     *     {
     *       "id": 1,
     *       "name": "admin",
     *       "guard_name": "web",
     *       "permissions": []
     *     }
     *   ],
     *   "direct_permissions": [],
     *   "permissions": []
     * }
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
     *
     * @urlParam user integer required The user ID. Example: 1
     *
     * @bodyParam name string The user's full name. Example: John Updated
     * @bodyParam email string The user's email address. Example: updated@example.com
     * @bodyParam phone string The user's phone number. Example: +0987654321
     * @bodyParam password string The new password. Example: NewPassword123!
     * @bodyParam password_confirmation string Password confirmation. Example: NewPassword123!
     * @bodyParam is_active boolean Whether the user account is active. Example: true
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "John Updated",
     *   "email": "updated@example.com",
     *   "phone": null,
     *   "is_active": true,
     *   "is_verified": true,
     *   "created_at": "2026-01-15T10:00:00.000000Z",
     *   "roles": [],
     *   "direct_permissions": [],
     *   "permissions": []
     * }
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
     *
     * @urlParam user integer required The user ID. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "John Doe",
     *   "email": "user@example.com",
     *   "phone": null,
     *   "is_active": false,
     *   "is_verified": true,
     *   "created_at": "2026-01-15T10:00:00.000000Z",
     *   "roles": [],
     *   "direct_permissions": [],
     *   "permissions": []
     * }
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
     *
     * @urlParam user integer required The user ID. Example: 1
     *
     * @bodyParam roles string[] required Array of role names. Example: ["admin", "manager"]
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "John Doe",
     *   "email": "user@example.com",
     *   "phone": null,
     *   "is_active": true,
     *   "is_verified": true,
     *   "created_at": "2026-01-15T10:00:00.000000Z",
     *   "roles": [
     *     {
     *       "id": 1,
     *       "name": "admin",
     *       "guard_name": "web",
     *       "permissions": []
     *     }
     *   ],
     *   "direct_permissions": [],
     *   "permissions": []
     * }
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
     *
     * @urlParam user integer required The user ID. Example: 1
     *
     * @bodyParam permissions string[] required Array of permission names. Example: ["UsersCreate", "UsersUpdate"]
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "John Doe",
     *   "email": "user@example.com",
     *   "phone": null,
     *   "is_active": true,
     *   "is_verified": true,
     *   "created_at": "2026-01-15T10:00:00.000000Z",
     *   "roles": [],
     *   "direct_permissions": [
     *     {
     *       "id": 1,
     *       "name": "UsersCreate",
     *       "guard_name": "web"
     *     }
     *   ],
     *   "permissions": [
     *     {
     *       "id": 1,
     *       "name": "UsersCreate",
     *       "guard_name": "web"
     *     }
     *   ]
     * }
     */
    public function assignPermissions(AssignPermissionsRequest $request, User $user)
    {
        Gate::authorize('updateRole', $user);

        $user->syncPermissions($request->validated('permissions'));

        $user->load(['roles', 'permissions']);

        return new UserResource($user);
    }
}
