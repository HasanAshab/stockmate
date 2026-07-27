<?php

namespace App\Notifications\Concerns;

use App\Models\User;
use Illuminate\Support\Collection;
use LogicException;

trait HasRecipients
{
    public static function recipients(): Collection
    {
        if (! defined(static::class . '::TYPE')) {
            throw new LogicException(sprintf(
                '%s must define a TYPE constant.',
                static::class
            ));
        }

        $key = 'notification_recipients.' . constant(static::class . '::TYPE');

        $roles = config($key);

        if ($roles === null) {
            throw new LogicException("Missing config key [$key].");
        }

        return User::role($roles)->get();
    }
}