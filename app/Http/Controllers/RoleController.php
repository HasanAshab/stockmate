<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoleResource;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Spatie\Permission\Models\Role;

#[Group('User Management')]
#[Authenticated]
class RoleController extends Controller
{
    /**
     * List Roles
     *
     * Get a list of all available roles with their permissions.
     */
    #[ResponseFromApiResource(RoleResource::class, Role::class, collection: true, with: ['permissions'])]
    public function index()
    {
        Gate::authorize('viewAny', Role::class);

        $roles = Role::with('permissions')->get();

        return RoleResource::collection($roles);
    }
}
