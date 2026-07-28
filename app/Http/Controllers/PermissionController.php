<?php

namespace App\Http\Controllers;

use App\Http\Resources\PermissionResource;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use App\Models\Permission;

#[Group('User Management')]
#[Authenticated]
class PermissionController extends Controller
{
    /**
     * List Permissions
     *
     * Get a list of all available permissions in the system.
     */
    #[ResponseFromApiResource(PermissionResource::class, Permission::class, collection: true)]
    public function index()
    {
        Gate::authorize('viewAny', Permission::class);

        $permissions = Permission::all();

        return PermissionResource::collection($permissions);
    }
}
