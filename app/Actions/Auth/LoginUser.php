<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class LoginUser
{
    public function execute(string $identifier, string $password): User
    {
        $user = User::findByIdentifier($identifier);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new AuthenticationException(
                'These credentials do not match our records.'
            );
        }

        return $user;
    }
}
