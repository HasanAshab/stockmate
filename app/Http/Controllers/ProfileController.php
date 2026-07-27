<?php

namespace App\Http\Controllers;

use App\Actions\User\UpdateProfile;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

/**
 * @group Profile Management
 *
 * APIs for managing the authenticated user's profile
 *
 * @authenticated
 */
class ProfileController extends Controller
{
    /**
     * Get Profile
     *
     * Get the authenticated user's profile information.
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "John Doe",
     *   "email": "user@example.com",
     *   "phone": null,
     *   "is_active": true,
     *   "is_verified": true,
     *   "created_at": "2026-01-15T10:00:00.000000Z",
     *   "roles": [],
     *   "direct_permissions": [],
     *   "permissions": []
     * }
     */
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Update Profile
     *
     * Update the authenticated user's profile information.
     *
     * @bodyParam name string The user's full name. Example: John Updated
     * @bodyParam email string The user's email address. Example: updated@example.com
     * @bodyParam phone string The user's phone number. Example: +0987654321
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "John Updated",
     *   "email": "updated@example.com",
     *   "phone": "+0987654321",
     *   "is_active": true,
     *   "is_verified": true,
     *   "created_at": "2026-01-15T10:00:00.000000Z",
     *   "roles": [],
     *   "direct_permissions": [],
     *   "permissions": []
     * }
     */
    public function update(UpdateProfileRequest $request, UpdateProfile $updateProfile)
    {
        $user = $request->user();

        $updateProfile->execute($user, $request->validated());

        return new UserResource($user);
    }
}
