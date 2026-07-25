<?php

namespace App\Http\Controllers;

use App\Actions\User\UpdateProfile;
use App\Http\Requests\User\UpdateProfileRequest;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return $request->user()->toResource();
    }

    public function update(UpdateProfileRequest $request, UpdateProfile $updateProfile)
    {
        $user = $request->user();

        $updateProfile->execute($user, $request->validated());

        return $user->toResource();
    }
}
