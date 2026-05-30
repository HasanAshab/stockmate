<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return User::paginate(15)->toResourceCollection();
    }

    public function store(StoreUserRequest $request)
    {
        return User::create($request->validated())
            ->toResource()
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user)
    {
        return $user->toResource();
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if (isset($validated["role"])) {
            Gate::authorize('updateRole', $user);
        }

        $user->update($validated);
        return $user->toResource();
    }

    public function toggleStatus(User $user)
    {
        Gate::authorize('deactivate', $user);

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        return $user->toResource();
    }
}
