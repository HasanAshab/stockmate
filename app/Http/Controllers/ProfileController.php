<?php

namespace App\Http\Controllers;

use App\Actions\User\UpdateProfile;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Get Profile
     *
     * Get the authenticated user's profile information.
     */
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Update Profile
     *
     * Update the authenticated user's profile information.
     */
    public function update(UpdateProfileRequest $request, UpdateProfile $updateProfile)
    {
        $user = $request->user();

        $updateProfile->execute($user, $request->validated());

        return new UserResource($user);
    }
}
