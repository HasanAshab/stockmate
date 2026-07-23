<?php

namespace App\Http\Controllers;

use App\Http\Resources\PermissionResource;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Permission::class);

        $permissions = Permission::all();

        return PermissionResource::collection($permissions);
    }
}
