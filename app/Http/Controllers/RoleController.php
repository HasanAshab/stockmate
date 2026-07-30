<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    /**
     * List Roles
     *
     * Get a list of all available roles with their permissions.
     */
    public function index()
    {
        Gate::authorize('viewAny', Role::class);

        $roles = Role::with('permissions')->get();

        return RoleResource::collection($roles);
    }
}
