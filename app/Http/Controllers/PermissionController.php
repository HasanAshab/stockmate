<?php

namespace App\Http\Controllers;

use App\Http\Resources\PermissionResource;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

/**
 * @group User Management
 *
 * @authenticated
 */
class PermissionController extends Controller
{
    /**
     * List Permissions
     *
     * Get a list of all available permissions in the system.
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "UsersCreate",
     *       "guard_name": "web"
     *     },
     *     {
     *       "id": 2,
     *       "name": "UsersUpdate",
     *       "guard_name": "web"
     *     }
     *   ]
     * }
     */
    public function index()
    {
        Gate::authorize('viewAny', Permission::class);

        $permissions = Permission::all();

        return PermissionResource::collection($permissions);
    }
}
