<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return User::select(['id', 'name', 'email', 'role', 'is_active', 'created_at'])->paginate(15);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $roleEnum = match($validated['role']) {
            'admin' => Role::Admin,
            'staff' => Role::Staff,
        };

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $roleEnum,
            'is_active' => true,
        ]);

        return response()->json($user->only(['id', 'name', 'email', 'role', 'is_active', 'created_at']), 201);
    }

    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return $user->only(['id', 'name', 'email', 'role', 'is_active', 'created_at']);
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validated();

        if (isset($validated['role'])) {
            $roleEnum = match($validated['role']) {
                'admin' => Role::Admin,
                'staff' => Role::Staff,
            };

            if (auth()->id() === $user->id && $user->role !== $roleEnum) {
                abort(403, 'You cannot change your own role.');
            }
            $user->role = $roleEnum;
        }

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json($user->only(['id', 'name', 'email', 'role', 'is_active', 'created_at']), 200);
    }

    public function toggleStatus(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() === $user->id) {
            abort(403, 'You cannot deactivate your own account.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'message' => 'User status updated.',
            'is_active' => $user->is_active,
        ], 200);
    }
}
