<?php

namespace App\Notifications;

use Spatie\OneTimePasswords\Models\OneTimePassword;
use Spatie\OneTimePasswords\Notifications\OneTimePasswordNotification;

abstract class OtpChannelNotification extends OneTimePasswordNotification
{
    public function __construct(
        OneTimePassword $otp,
        protected ?string $identifierType = null,
    ) {
        parent::__construct($otp);
    }

    public function via($notifiable): array
    {
        return match ($this->identifierType) {
            'email' => ['mail'],
            'phone' => ['twilio'],
            default => $notifiable->email ? ['mail'] : ['twilio'],
        };
    }
}
