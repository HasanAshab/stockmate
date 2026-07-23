<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoleResource;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Role::class);

        $roles = Role::with('permissions')->get();

        return RoleResource::collection($roles);
    }
}
