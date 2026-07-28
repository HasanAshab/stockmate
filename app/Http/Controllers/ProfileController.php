<?php

namespace App\Http\Controllers;

use App\Actions\User\UpdateProfile;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Profile Management', 'APIs for managing the authenticated user\'s profile')]
#[Authenticated]
class ProfileController extends Controller
{
    /**
     * Get Profile
     *
     * Get the authenticated user's profile information.
     */
    #[ResponseFromApiResource(UserResource::class, User::class)]
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Update Profile
     *
     * Update the authenticated user's profile information.
     */
    #[BodyParam('name', 'string', 'The user\'s full name.', example: 'John Updated')]
    #[BodyParam('email', 'string', 'The user\'s email address.', example: 'updated@example.com')]
    #[BodyParam('phone', 'string', 'The user\'s phone number.', example: '+0987654321')]
    #[ResponseFromApiResource(UserResource::class, User::class)]
    public function update(UpdateProfileRequest $request, UpdateProfile $updateProfile)
    {
        $user = $request->user();

        $updateProfile->execute($user, $request->validated());

        return new UserResource($user);
    }
}
