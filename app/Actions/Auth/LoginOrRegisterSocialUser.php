<?php

namespace App\Actions\Auth;

use App\DTOs\SocialUserData;
use App\Enums\SocialProvider;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class LoginOrRegisterSocialUser
{
    public function execute(SocialProvider $provider, SocialUserData $data): User
    {
        $account = SocialAccount::where('provider', $provider)
            ->where('provider_id', $data->id)
            ->first();

        if ($account) {
            return $account->user;
        }

        $user = User::where('email', $data->email)->first();
        $isNewUser = false;

        if (! $user) {
            $user = User::create([
                'name' => $data->name ?? 'User',
                'email' => $data->email,
                'password' => null,
                'email_verified_at' => $data->emailVerified ? now() : null,
            ]);

            $isNewUser = true;
        }

        $user->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => $data->id,
        ]);

        if ($isNewUser) {
            event(new Registered($user));
        }

        return $user;
    }
}
