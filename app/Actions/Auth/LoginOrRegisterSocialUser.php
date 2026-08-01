<?php

namespace App\Actions\Auth;

use App\Enums\SocialProvider;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class LoginOrRegisterSocialUser
{
    public function execute(SocialProvider $provider, SocialiteUser $socialiteUser): User
    {
        $account = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialiteUser->getId())
            ->first();

        if ($account) {
            return $account->user;
        }

        $user = User::where('email', $socialiteUser->getEmail())->first();
        $isNewUser = false;

        if (! $user) {
            $user = User::create([
                'name' => $socialiteUser->getName() ?? 'User',
                'email' => $socialiteUser->getEmail(),
                'password' => null,
                'email_verified_at' => now(),
            ]);

            $isNewUser = true;
        }

        $user->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => $socialiteUser->getId(),
        ]);

        if ($isNewUser) {
            event(new Registered($user));
        }

        return $user;
    }
}
