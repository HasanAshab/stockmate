<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return $request->user()->only(['id', 'name', 'email', 'role', 'is_active', 'created_at']);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json($user->only(['id', 'name', 'email', 'role', 'is_active', 'created_at']), 200);
    }
}
