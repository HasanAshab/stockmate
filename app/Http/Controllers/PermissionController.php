<?php

namespace App\Http\Controllers;

use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use Illuminate\Support\Facades\Gate;

class PermissionController extends Controller
{
    /**
     * List Permissions
     *
     * Get a list of all available permissions in the system.
     */
    public function index()
    {
        Gate::authorize('viewAny', Permission::class);

        $permissions = Permission::all();

        return PermissionResource::collection($permissions);
    }
}
