<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoleResource;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

/**
 * @group User Management
 *
 * @authenticated
 */
class RoleController extends Controller
{
    /**
     * List Roles
     *
     * Get a list of all available roles with their permissions.
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "admin",
     *       "guard_name": "web",
     *       "permissions": [
     *         {
     *           "id": 1,
     *           "name": "UsersCreate",
     *           "guard_name": "web"
     *         }
     *       ]
     *     }
     *   ]
     * }
     */
    public function index()
    {
        Gate::authorize('viewAny', Role::class);

        $roles = Role::with('permissions')->get();

        return RoleResource::collection($roles);
    }
}
