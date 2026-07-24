<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;

class SendOneTimePassword
{
    public function handle(Registered $event): void
    {
        $event->user->sendOneTimePassword();
    }
}
