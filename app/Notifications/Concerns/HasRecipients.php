<?php

namespace App\Notifications\Concerns;

use App\Models\User;
use Illuminate\Support\Collection;
use LogicException;

trait HasRecipients
{
    public static function recipients(): Collection
    {
        $key = 'notification-recipients.' . static::class;

        $roles = config($key);

        if ($roles === null) {
            throw new LogicException("Missing config key [$key].");
        }

        return User::role($roles)->get();
    }
}
