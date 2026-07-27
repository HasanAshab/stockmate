<?php

namespace App\Listeners;

use App\Notifications\AccountActivationRequiredNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Notification;

class SendAccountActivationRequiredNotification
{
    public function handle(Registered $event): void
    {
        Notification::send(
            AccountActivationRequiredNotification::recipients(),
            new AccountActivationRequiredNotification($event->user)
        );
    }
}
